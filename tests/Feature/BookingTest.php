<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Filament\Resources\BookingRequests\Pages\ListBookingRequests;
use App\Filament\Resources\BookingRequests\Pages\ViewBookingRequest;
use App\Mail\AlternativeTimeOffered;
use App\Mail\AppointmentReminder;
use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingDeclined;
use App\Mail\BookingReceived;
use App\Mail\NewBookingAlert;
use App\Models\BookingRequest;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\User;
use App\Settings\ClinicSettings;
use Database\Seeders\InitialContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Honeypot\EncryptedTime;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InitialContentSeeder::class);
        Mail::fake();
    }

    /** A real form submission includes the (empty) honeypot field and an encrypted "form opened at" time. */
    private function validForm(array $overrides = []): array
    {
        return [
            'my_name_abc' => '',
            'valid_from' => (string) EncryptedTime::create(now()->subSeconds(10)),
            'service_id' => Service::firstWhere('slug', 'check-up')->id,
            'name' => 'Siti Aminah',
            'phone' => '012-345 6789',
            'email' => 'Siti@Example.com',
            'patient_type' => 'new',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_time' => 'morning',
            'note' => 'Bringing my two children.',
            'consent' => '1',
            ...$overrides,
        ];
    }

    // ---------- Public form ----------

    public function test_booking_form_loads_and_preselects_a_service(): void
    {
        $fillings = Service::firstWhere('slug', 'fillings');

        $this->get('/book?service=fillings')
            ->assertOk()
            ->assertSee('Send appointment request')
            ->assertSee('value="'.$fillings->id.'" selected', escape: false)
            ->assertSee('privacy policy');
    }

    public function test_a_patient_can_send_a_request_and_both_sides_are_emailed(): void
    {
        $response = $this->post('/book', $this->validForm());

        $booking = BookingRequest::sole();
        $response->assertRedirect('/book/thank-you');

        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame('siti@example.com', $booking->email);
        $this->assertNotNull($booking->consented_at);
        $this->assertSame(config('booking.privacy_notice_version'), $booking->privacy_notice_version);
        $this->assertMatchesRegularExpression('/^SNY-[A-HJ-NP-Z2-9]{6}$/', $booking->reference);

        Mail::assertQueued(BookingReceived::class, fn ($mail) => $mail->hasTo('siti@example.com'));
        Mail::assertQueued(NewBookingAlert::class, fn ($mail) => $mail->hasTo(app(ClinicSettings::class)->booking_notification_email));

        $this->followRedirects($response)->assertSee($booking->reference)->assertSee('not yet a confirmed appointment');
    }

    public function test_thank_you_page_needs_a_fresh_submission(): void
    {
        $this->get('/book/thank-you')->assertRedirect('/book');
    }

    public function test_form_is_validated(): void
    {
        $this->post('/book', $this->validForm([
            'name' => '',
            'phone' => 'call me',
            'email' => 'not-an-email',
            'preferred_date' => now()->toDateString(),
            'consent' => null,
        ]))->assertSessionHasErrors(['name', 'phone', 'email', 'preferred_date', 'consent']);

        $this->assertDatabaseCount('booking_requests', 0);
        Mail::assertNothingQueued();
    }

    public function test_dates_too_far_ahead_are_rejected(): void
    {
        $this->post('/book', $this->validForm(['preferred_date' => now()->addDays(200)->toDateString()]))
            ->assertSessionHasErrors('preferred_date');
    }

    public function test_hidden_services_cannot_be_booked(): void
    {
        $service = Service::firstWhere('slug', 'fillings');
        $service->update(['is_bookable' => false]);

        $this->post('/book', $this->validForm(['service_id' => $service->id]))->assertSessionHasErrors('service_id');
    }

    public function test_bots_that_fill_the_hidden_field_are_blocked(): void
    {
        $this->post('/book', $this->validForm(['my_name_abc' => 'I am a bot']));

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_bots_that_skip_the_hidden_fields_are_blocked(): void
    {
        $data = $this->validForm();
        unset($data['my_name_abc'], $data['valid_from']);

        $this->post('/book', $data);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_bots_that_submit_instantly_are_blocked(): void
    {
        $this->post('/book', $this->validForm(['valid_from' => (string) EncryptedTime::create(now()->addSeconds(3))]));

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_one_visitor_cannot_flood_the_form(): void
    {
        for ($i = 0; $i < config('booking.max_per_hour'); $i++) {
            $this->post('/book', $this->validForm(['email' => "p{$i}@example.com"]))->assertRedirect('/book/thank-you');
        }

        $this->post('/book', $this->validForm())->assertStatus(429);
    }

    public function test_requests_can_be_paused_by_the_owner(): void
    {
        $settings = app(ClinicSettings::class);
        $settings->bookings_enabled = false;
        $settings->save();

        $this->get('/book')->assertOk()->assertSee('Online requests are paused')->assertDontSee('Send appointment request');
        $this->post('/book', $this->validForm())->assertForbidden();
    }

    // ---------- Staff workflow ----------

    public function test_front_desk_can_confirm_a_request_and_the_patient_gets_a_calendar_file(): void
    {
        $staff = User::factory()->role(Role::FrontDesk)->withTwoFactor()->create();
        $booking = BookingRequest::factory()->create();
        $dentist = Dentist::first();

        $this->actingAs($staff);
        Livewire::test(ViewBookingRequest::class, ['record' => $booking->getRouteKey()])
            ->callAction('confirm', [
                'appointment_at' => now()->addDays(3)->setTime(10, 30)->format('Y-m-d H:i:s'),
                'dentist_id' => $dentist->id,
                'message' => 'Please park at the back.',
            ])
            ->assertHasNoActionErrors();

        $booking->refresh();
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertSame('10:30', $booking->appointment_at->format('H:i'));
        $this->assertSame($staff->id, $booking->handled_by);

        Mail::assertQueued(BookingConfirmed::class, function (BookingConfirmed $mail) use ($booking) {
            $ics = $mail->attachments()[0];

            return $mail->hasTo($booking->email) && $ics->as === 'appointment.ics';
        });

        // Audit trail records who did it.
        $this->assertSame($staff->id, $booking->activitiesAsSubject()->latest('id')->first()->causer_id);
    }

    public function test_offering_another_time_and_declining_email_the_patient(): void
    {
        $staff = User::factory()->role(Role::FrontDesk)->withTwoFactor()->create();
        $first = BookingRequest::factory()->create();
        $second = BookingRequest::factory()->create();
        $this->actingAs($staff);

        Livewire::test(ViewBookingRequest::class, ['record' => $first->getRouteKey()])
            ->callAction('offerAlternative', ['alternative_at' => now()->addDays(5)->setTime(15, 0)->format('Y-m-d H:i:s')])
            ->assertHasNoActionErrors();
        Livewire::test(ViewBookingRequest::class, ['record' => $second->getRouteKey()])
            ->callAction('decline', ['message' => 'We are fully booked that week.']);

        $this->assertSame(BookingStatus::AlternativeOffered, $first->fresh()->status);
        $this->assertSame(BookingStatus::Declined, $second->fresh()->status);
        Mail::assertQueued(AlternativeTimeOffered::class);
        Mail::assertQueued(BookingDeclined::class);
    }

    public function test_cancelling_can_skip_the_email(): void
    {
        $staff = User::factory()->role(Role::FrontDesk)->withTwoFactor()->create();
        $booking = BookingRequest::factory()->confirmed()->create();
        $this->actingAs($staff);

        Livewire::test(ViewBookingRequest::class, ['record' => $booking->getRouteKey()])
            ->callAction('cancel', ['notify' => false]);

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        Mail::assertNotQueued(BookingCancelled::class);
    }

    public function test_reminders_are_sent_once_for_tomorrows_appointments_only(): void
    {
        $tomorrow = BookingRequest::factory()->confirmed(now()->addDay()->setTime(11, 0))->create();
        BookingRequest::factory()->confirmed(now()->addDays(3)->setTime(11, 0))->create();
        BookingRequest::factory()->create(['status' => BookingStatus::Pending]);

        Artisan::call('bookings:send-reminders');
        Artisan::call('bookings:send-reminders');

        Mail::assertQueuedCount(1);
        Mail::assertQueued(AppointmentReminder::class, fn ($mail) => $mail->hasTo($tomorrow->email));
        $this->assertNotNull($tomorrow->fresh()->reminder_sent_at);
    }

    public function test_old_requests_are_deleted_automatically_for_privacy(): void
    {
        $old = BookingRequest::factory()->create(['preferred_date' => now()->subMonths(7)->toDateString()]);
        $recent = BookingRequest::factory()->create(['preferred_date' => now()->subMonths(2)->toDateString()]);

        Artisan::call('model:prune', ['--model' => [BookingRequest::class]]);

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    // ---------- Who can see what ----------

    public function test_owner_and_front_desk_see_all_requests(): void
    {
        $bookings = BookingRequest::factory()->count(3)->create();

        foreach ([Role::Owner, Role::FrontDesk] as $role) {
            $this->actingAs(User::factory()->role($role)->withTwoFactor()->create());
            Livewire::test(ListBookingRequests::class)->assertCanSeeTableRecords($bookings);
        }
    }

    public function test_content_editors_cannot_see_patient_bookings(): void
    {
        $editor = User::factory()->role(Role::ContentEditor)->withTwoFactor()->create();
        $booking = BookingRequest::factory()->create();

        $this->actingAs($editor)->get('/admin/booking-requests')->assertForbidden();
        $this->actingAs($editor)->get('/admin/booking-requests/'.$booking->id)->assertNotFound();
    }

    public function test_dentists_only_see_their_own_bookings_and_cannot_change_them(): void
    {
        $dentistUser = User::factory()->role(Role::Dentist)->withTwoFactor()->create();
        $dentist = Dentist::first();
        $dentist->update(['user_id' => $dentistUser->id]);

        $mine = BookingRequest::factory()->confirmed()->create(['dentist_id' => $dentist->id]);
        $theirs = BookingRequest::factory()->confirmed()->create(['dentist_id' => Dentist::skip(1)->first()->id]);
        $unassigned = BookingRequest::factory()->create();

        $this->actingAs($dentistUser);
        Livewire::test(ListBookingRequests::class, ['activeTab' => 'all'])
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs, $unassigned]);

        $this->get('/admin/booking-requests/'.$theirs->id)->assertNotFound();

        Livewire::test(ViewBookingRequest::class, ['record' => $mine->getRouteKey()])
            ->assertActionHidden('confirm')
            ->assertActionHidden('cancel');
    }

    public function test_only_the_owner_can_permanently_delete_a_request(): void
    {
        $booking = BookingRequest::factory()->create();

        $this->assertTrue(User::factory()->owner()->create()->can('delete', $booking));
        $this->assertFalse(User::factory()->role(Role::FrontDesk)->create()->can('delete', $booking));
    }

    public function test_new_request_alert_email_does_not_contain_patient_contact_details(): void
    {
        $booking = BookingRequest::factory()->create(['phone' => '019-888 7777', 'email' => 'secret@example.com']);

        $html = (new NewBookingAlert($booking))->render();

        $this->assertStringNotContainsString('019-888 7777', $html);
        $this->assertStringNotContainsString('secret@example.com', $html);
        $this->assertStringContainsString($booking->reference, $html);
    }

    public function test_all_booking_emails_render(): void
    {
        $booking = BookingRequest::factory()->confirmed()->create([
            'service_id' => Service::first()->id,
            'dentist_id' => Dentist::first()->id,
            'alternative_at' => now()->addDays(4),
        ]);

        foreach ([BookingReceived::class, NewBookingAlert::class, BookingConfirmed::class, AlternativeTimeOffered::class,
            BookingDeclined::class, BookingCancelled::class, AppointmentReminder::class] as $mailable) {
            $this->assertStringContainsString($booking->reference, (new $mailable($booking))->render(), $mailable);
        }
    }
}
