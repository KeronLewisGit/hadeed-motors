@php
    $selected = old('interest', $vehicle ? 'v:'.$vehicle->id : ($model ? 'm:'.$model : ''));
    $firstDay = $days[0] ?? null;
@endphp
<x-layouts.site title="Book a test drive" description="Book a test drive at Hadeed Motors, St John's: any new Nissan or Suzuki, or a car from our pre-owned stock. Choose a day and time." :mobile-bar="false">
    <x-page-header title="Book a test drive" lead="Choose the car, a weekday and a time. A sales consultant calls to confirm and has it washed and waiting." code="TD" label="Test drive" photo="model-jimny-trail" />

    <div class="wrap mt-10 grid gap-12 md:mt-14 lg:grid-cols-[1.6fr_1fr]">
        <form method="POST" action="{{ route('test-drive.store') }}" class="space-y-12" x-data="leadForm" @submit="submit">
            @csrf
            <x-form-errors />

            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">1</span>The car</legend>
                <x-select name="interest" label="Which vehicle?" :options="$interests" :value="$selected" :required="true" placeholder="Choose a vehicle…" />
                @if ($vehicle)
                    <div class="panel flex items-center gap-4 p-3">
                        @if ($vehicle->photoUrl())<img src="{{ $vehicle->photoUrl() }}" alt="" class="h-16 w-24 flex-none rounded-lg object-cover">@endif
                        <div><p class="font-bold">{{ $vehicle->title }}</p><p class="text-sm text-ink-3">{{ $vehicle->priceLabel() }}@if($vehicle->mileage_km !== null) · {{ number_format($vehicle->mileage_km) }} km @endif</p></div>
                    </div>
                @endif
            </fieldset>

            <fieldset class="space-y-5" x-data="slotPicker({ grid: @js($slots), date: @js(old('appointment_date', $firstDay?->toDateString() ?? '')), time: @js(old('appointment_time', '')) })">
                <legend class="fieldset-title mb-5"><span class="step">2</span>Day and time</legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="f-appointment_date" class="label">Day</label>
                        <select id="f-appointment_date" name="appointment_date" class="input" required x-model="date" @if($errors->has('appointment_date')) aria-invalid="true" @endif>
                            @foreach ($days as $d)
                                <option value="{{ $d->toDateString() }}" @selected(old('appointment_date', $firstDay?->toDateString()) === $d->toDateString()) @disabled(empty($slots[$d->toDateString()]))>{{ $d->format('l j F') }}{{ empty($slots[$d->toDateString()]) ? ' (fully booked)' : '' }}</option>
                            @endforeach
                        </select>
                        <span class="hint">Weekdays only. We're closed weekends and public holidays.</span>
                        <x-error for="appointment_date" />
                    </div>
                    <div>
                        <label for="f-appointment_time" class="label">Time</label>
                        <select id="f-appointment_time" name="appointment_time" class="input" required x-model="time" @if($errors->has('appointment_time')) aria-invalid="true" @endif>
                            <option value="">Choose a time…</option>
                            {{-- Without JavaScript every time is listed and the server checks it. --}}
                            @foreach (config('hadeed.booking.test_drive_slots') as $t)
                                <option value="{{ $t }}" @selected(old('appointment_time') === $t) x-bind:disabled="!times.includes('{{ $t }}')" x-bind:hidden="!times.includes('{{ $t }}')">{{ date('g:i a', strtotime($t)) }}</option>
                            @endforeach
                        </select>
                        <span class="hint">Drives take about 30 minutes.</span>
                        <x-error for="appointment_time" />
                    </div>
                </div>
            </fieldset>

            <x-contact-fields :step="3" />

            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">4</span>Anything else?</legend>
                <x-field name="message" label="Notes for the sales team" type="textarea" :optional="true" maxlength="1500" rows="3" placeholder="e.g. I'd like to compare it with the Vitara, or I'll bring my trade-in." />
                <div>
                    <label class="choice"><input type="checkbox" name="licence" value="1" required @checked(old('licence'))>I hold a valid driving licence and will bring it with me.</label>
                    <x-error for="licence" />
                </div>
            </fieldset>

            <div class="flex flex-col gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                <x-submit label="Request this test drive" />
                <p class="text-sm text-ink-3">You'll get a reference number straight away and by email.</p>
            </div>
        </form>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">Good to know</h2>
                <ul class="mt-3 space-y-3 text-ink-2">
                    <li class="flex gap-3"><x-icon name="key" class="mt-0.5 h-5 w-5 flex-none text-accent-text" />A sales consultant rides along. Bring your driving licence.</li>
                    <li class="flex gap-3"><x-icon name="clock" class="mt-0.5 h-5 w-5 flex-none text-accent-text" />{{ \App\Support\Hours::summary() }}.</li>
                    <li class="flex gap-3"><x-icon name="swap" class="mt-0.5 h-5 w-5 flex-none text-accent-text" /><span>Trading in? <a href="{{ route('trade-in') }}" class="text-link">Send photos first</a> and we'll have a figure ready.</span></li>
                </ul>
            </div>
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">Rather talk?</h2>
                <a href="{{ tel_href(hadeed('phone')) }}" class="font-display mt-2 inline-flex min-h-11 items-center gap-2 text-xl font-bold"><x-icon name="phone" class="h-5 w-5" />{{ format_phone(hadeed('phone')) }}</a>
            </div>
        </aside>
    </div>
</x-layouts.site>
