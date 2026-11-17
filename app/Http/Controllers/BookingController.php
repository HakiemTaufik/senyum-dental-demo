<?php

namespace App\Http\Controllers;

use App\Enums\PatientType;
use App\Enums\PreferredTime;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Service;
use App\Services\BookingWorkflow;
use App\Settings\ClinicSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, ClinicSettings $clinic): View
    {
        $services = Service::published()->where('is_bookable', true)->ordered()->get(['id', 'name', 'slug']);

        return view('pages.book', [
            'enabled' => $clinic->bookings_enabled,
            'services' => $services,
            'selectedServiceId' => $services->firstWhere('slug', $request->query('service'))?->id,
            'patientTypes' => PatientType::cases(),
            'preferredTimes' => PreferredTime::cases(),
            'minDate' => now()->addDay()->toDateString(),
            'maxDate' => now()->addDays($clinic->booking_max_days_ahead ?: 90)->toDateString(),
        ]);
    }

    public function store(StoreBookingRequest $request, BookingWorkflow $workflow): RedirectResponse
    {
        $booking = $workflow->submit($request->bookingData());

        return redirect()
            ->route('book.thanks')
            ->with('booking_reference', $booking->reference)
            ->with('booking_email', $booking->email);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('booking_reference')) {
            return redirect()->route('book');
        }

        return view('pages.book-thanks', [
            'reference' => $request->session()->get('booking_reference'),
            'email' => $request->session()->get('booking_email'),
        ]);
    }
}
