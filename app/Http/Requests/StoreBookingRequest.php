<?php

namespace App\Http\Requests;

use App\Enums\PatientType;
use App\Enums\PreferredTime;
use App\Settings\ClinicSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(ClinicSettings::class)->bookings_enabled;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name'))),
            'phone' => trim((string) $this->input('phone')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'note' => filled($this->input('note')) ? trim((string) $this->input('note')) : null,
            'service_id' => $this->input('service_id') ?: null,
        ]);
    }

    public function rules(): array
    {
        $maxDays = app(ClinicSettings::class)->booking_max_days_ahead ?: 90;

        return [
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_published', true)->where('is_bookable', true)],
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s\-]{7,18}$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'patient_type' => ['required', Rule::enum(PatientType::class)],
            'preferred_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:tomorrow', 'before_or_equal:'.now()->addDays($maxDays)->toDateString()],
            'preferred_time' => ['required', Rule::enum(PreferredTime::class)],
            'note' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number, for example 012-345 6789.',
            'preferred_date.after_or_equal' => 'Please choose a date from tomorrow onwards. For today, please call us.',
            'preferred_date.before_or_equal' => 'Please choose a date on or before :date. For later dates, please call us.',
            'consent.accepted' => 'Please tick the box to agree, so we can use your details to arrange your appointment.',
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'service',
            'patient_type' => 'new or existing patient',
            'preferred_date' => 'preferred date',
            'preferred_time' => 'preferred time',
        ];
    }

    /** Only the fields we store (the consent tick itself is recorded as a timestamp). */
    public function bookingData(): array
    {
        return $this->safe()->except('consent');
    }
}
