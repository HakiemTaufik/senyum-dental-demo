<x-layouts.site title="Request an appointment" description="Send an appointment request to {{ $clinic->name }}. We will reply to confirm a time.">
    <div class="border-b border-sand-200 bg-brand-50">
        <div class="container-site py-12 sm:py-16">
            <h1 class="text-4xl font-bold sm:text-5xl">Request an appointment</h1>
            <p class="section-intro">Tell us when suits you and we will contact you, usually within one working day, to confirm a time.</p>
        </div>
    </div>

    <div class="container-site grid gap-10 py-12 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if (! $enabled)
                <div class="card">
                    <h2 class="text-2xl font-bold">Online requests are paused</h2>
                    <p class="mt-3 text-ink-muted">Please call or WhatsApp us to arrange an appointment.</p>
                </div>
            @else
                @if ($errors->any())
                    <div id="error-summary" class="mb-8 rounded-2xl border-2 border-accent-700 bg-white p-5" role="alert" tabindex="-1">
                        <h2 class="text-lg font-bold text-accent-700">Please check the form</h2>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->keys() as $field)
                                <li><a href="#field-{{ $field }}" class="text-accent-700 underline">{{ $errors->first($field) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">document.getElementById('error-summary')?.focus();</script>
                @endif

                <form method="POST" action="{{ route('book.store') }}" class="card space-y-7 sm:p-8" novalidate>
                    @csrf
                    <x-honeypot />

                    <p class="text-sm text-ink-muted">All fields are required unless marked optional.</p>

                    {{-- Service --}}
                    <div>
                        <label for="field-service_id" class="block font-semibold">What would you like to come in for? <span class="font-normal text-ink-muted">(optional)</span></label>
                        <select id="field-service_id" name="service_id" class="mt-2 block w-full rounded-xl border border-sand-200 bg-white px-4 py-3 text-base focus:border-brand-600"
                                @error('service_id') aria-invalid="true" aria-describedby="error-service_id" @enderror>
                            <option value="">Not sure – general check-up or advice</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((int) old('service_id', $selectedServiceId) === $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id')<p id="error-service_id" class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                    </div>

                    {{-- Date & time --}}
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="field-preferred_date" class="block font-semibold">Preferred date</label>
                            <input type="date" id="field-preferred_date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ $minDate }}" max="{{ $maxDate }}" required
                                   class="mt-2 block w-full rounded-xl border border-sand-200 px-4 py-3 text-base focus:border-brand-600"
                                   aria-describedby="hint-preferred_date @error('preferred_date') error-preferred_date @enderror" @error('preferred_date') aria-invalid="true" @enderror>
                            <p id="hint-preferred_date" class="mt-2 text-sm text-ink-muted">For an appointment today, please call us.</p>
                            @error('preferred_date')<p id="error-preferred_date" class="mt-1 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                        </div>
                        <fieldset>
                            <legend id="field-preferred_time" class="font-semibold">Preferred time</legend>
                            <div class="mt-2 space-y-2">
                                @foreach ($preferredTimes as $time)
                                    <label class="flex items-center gap-3 rounded-xl border border-sand-200 px-4 py-2.5 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                        <input type="radio" name="preferred_time" value="{{ $time->value }}" class="size-5 accent-brand-600" @checked(old('preferred_time', 'any') === $time->value) required>
                                        <span>{{ $time->getLabel() }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('preferred_time')<p class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>

                    {{-- Patient type --}}
                    <fieldset>
                        <legend id="field-patient_type" class="font-semibold">Have you visited us before?</legend>
                        <div class="mt-2 flex flex-wrap gap-3">
                            @foreach ($patientTypes as $type)
                                <label class="flex items-center gap-3 rounded-xl border border-sand-200 px-4 py-2.5 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                    <input type="radio" name="patient_type" value="{{ $type->value }}" class="size-5 accent-brand-600" @checked(old('patient_type') === $type->value) required>
                                    <span>{{ $type->getLabel() }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('patient_type')<p class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                    </fieldset>

                    {{-- Contact details --}}
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="field-name" class="block font-semibold">Full name</label>
                            <input type="text" id="field-name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="100"
                                   class="mt-2 block w-full rounded-xl border border-sand-200 px-4 py-3 text-base focus:border-brand-600"
                                   @error('name') aria-invalid="true" aria-describedby="error-name" @enderror>
                            @error('name')<p id="error-name" class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="field-phone" class="block font-semibold">Mobile number</label>
                            <input type="tel" id="field-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" required maxlength="30" placeholder="012-345 6789"
                                   class="mt-2 block w-full rounded-xl border border-sand-200 px-4 py-3 text-base focus:border-brand-600"
                                   @error('phone') aria-invalid="true" aria-describedby="error-phone" @enderror>
                            @error('phone')<p id="error-phone" class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="field-email" class="block font-semibold">Email</label>
                            <input type="email" id="field-email" name="email" value="{{ old('email') }}" autocomplete="email" spellcheck="false" required maxlength="255"
                                   class="mt-2 block w-full rounded-xl border border-sand-200 px-4 py-3 text-base focus:border-brand-600"
                                   aria-describedby="hint-email @error('email') error-email @enderror" @error('email') aria-invalid="true" @enderror>
                            <p id="hint-email" class="mt-2 text-sm text-ink-muted">We'll email you a copy of your request.</p>
                            @error('email')<p id="error-email" class="mt-1 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Note --}}
                    <div>
                        <label for="field-note" class="block font-semibold">Anything we should know to arrange your visit? <span class="font-normal text-ink-muted">(optional)</span></label>
                        <textarea id="field-note" name="note" rows="3" maxlength="500"
                                  class="mt-2 block w-full rounded-xl border border-sand-200 px-4 py-3 text-base focus:border-brand-600"
                                  aria-describedby="hint-note @error('note') error-note @enderror">{{ old('note') }}</textarea>
                        <p id="hint-note" class="mt-2 text-sm text-ink-muted">For example: "I need wheelchair access" or "bringing two children". <strong>Please don't include medical details</strong> – your dentist will ask about these privately at your visit.</p>
                        @error('note')<p id="error-note" class="mt-1 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                    </div>

                    {{-- PDPA consent --}}
                    <div class="rounded-xl bg-sand-100 p-4">
                        <label for="field-consent" class="flex items-start gap-3">
                            <input type="checkbox" id="field-consent" name="consent" value="1" class="mt-1 size-5 shrink-0 accent-brand-600" @checked(old('consent')) required
                                   @error('consent') aria-invalid="true" aria-describedby="error-consent" @enderror>
                            <span>I agree that {{ $clinic->name }} may use the details above (including any health information I choose to share) to contact me about this appointment request, as described in the <a href="{{ url('/privacy-policy') }}" class="font-semibold text-brand-700 underline" target="_blank">privacy policy<span class="sr-only"> (opens in a new tab)</span></a>.</span>
                        </label>
                        @error('consent')<p id="error-consent" class="mt-2 text-sm font-medium text-accent-700">{{ $message }}</p>@enderror
                    </div>

                    {{-- Shows "Sending…" and blocks a second tap while the request is on its way (resources/js/app.js). --}}
                    <button type="submit" class="btn-primary w-full disabled:cursor-wait disabled:opacity-70 sm:w-auto" data-submit-once data-sending-text="Sending…">Send appointment request</button>
                </form>
            @endif
        </div>

        <aside class="space-y-6" aria-label="Other ways to book">
            <div class="card">
                <h2 class="text-lg font-semibold">Prefer to talk?</h2>
                <ul class="mt-3 space-y-3">
                    @if ($clinic->phoneHref())
                        <li class="flex items-center gap-2"><x-site-icon name="phone" class="text-brand-600" /><a href="{{ $clinic->phoneHref() }}" class="font-semibold text-brand-700 underline-offset-4 hover:underline">{{ $clinic->phone }}</a></li>
                    @endif
                    @if ($clinic->whatsappUrl())
                        <li><a href="{{ $clinic->whatsappUrl('Hi, I would like to make an appointment.') }}" class="btn-whatsapp w-full" rel="noopener" target="_blank"><x-site-icon name="whatsapp" class="size-5" /> WhatsApp us<span class="sr-only"> (opens in a new tab)</span></a></li>
                    @endif
                </ul>
            </div>
            <div class="card">
                <h2 class="text-lg font-semibold">Opening hours</h2>
                <dl class="mt-3 space-y-2">
                    @foreach ($clinic->opening_hours as $row)
                        <div class="flex justify-between gap-4"><dt class="text-ink-muted">{{ $row['days'] }}</dt><dd class="text-right font-medium">{{ $row['hours'] }}</dd></div>
                    @endforeach
                </dl>
            </div>
            <div class="rounded-2xl border border-accent/40 bg-white p-5 text-sm">
                <p><strong>Dental emergency?</strong> Please call us instead of using this form. Outside opening hours, for severe swelling, heavy bleeding or difficulty breathing, go to the nearest hospital emergency department.</p>
            </div>
        </aside>
    </div>
</x-layouts.site>
