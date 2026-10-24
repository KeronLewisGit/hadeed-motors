@php
    $firstOpen = collect($serviceDays)->first(fn ($d) => $d['left'] > 0);
    $selectedDate = old('appointment_date', $firstOpen ? $firstOpen['date']->toDateString() : '');
@endphp
<x-layouts.site title="Service and parts" description="Book a service at Hadeed Motors, Old Parham Road, St John's. See which days have space, choose a drop-off time, and order genuine parts by VIN.">
    <x-page-header title="Service and parts" lead="Nissan and Suzuki specialists on Old Parham Road. See which days still have space in the workshop, choose a drop-off time, and we'll call to confirm." code="SV" label="Workshop" photo="service-bay" position="50% 35%">
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="#book" class="btn btn-accent">Book a service<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="{{ route('parts') }}" class="btn btn-on-dark">Request a part</a>
        </div>
    </x-page-header>

    <section class="wrap mt-14 grid gap-6 md:mt-20 md:grid-cols-3" aria-label="What we do">
        @foreach ([
            ['wrench', 'Scheduled maintenance', 'Services at the mileages in your handbook, using the right oils and filters for your engine.'],
            ['gauge', 'Repairs and diagnostics', 'Warning lights, brakes, air conditioning, suspension. Tell us what it\'s doing and we\'ll find out why.'],
            ['cog', 'Genuine parts', 'Send your VIN and a photo of the part. The parts counter checks stock and calls you with a price.'],
        ] as [$icon, $t, $d])
            <div class="panel flex gap-4 p-5 md:block md:p-6">
                <span class="grid h-12 w-12 flex-none place-items-center rounded-full bg-accent-soft text-accent-text"><x-icon :name="$icon" class="h-6 w-6" /></span>
                <div><h2 class="text-lg font-bold font-sans md:mt-4">{{ $t }}</h2>
                <p class="mt-1 text-ink-2">{{ $d }}</p></div>
            </div>
        @endforeach
    </section>

    <section id="book" class="wrap mt-16 grid gap-12 md:mt-24 lg:grid-cols-[1.6fr_1fr]" aria-labelledby="book-title">
        <form method="POST" action="{{ route('service.store') }}" class="space-y-12" x-data="leadForm" @submit="submit">
            @csrf
            <div>
                <x-plate-tag code="SV" label="Book online" />
                <h2 id="book-title" class="display-2 mt-5">Book a service</h2>
            </div>
            <x-form-errors />

            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">1</span>Your vehicle</legend>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="registration" label="Licence plate" :required="true" maxlength="12" autocapitalize="characters" hint="As on your plate, e.g. A12345" />
                    <x-field name="mileage" label="Odometer (km)" type="number" :required="true" min="0" max="1500000" inputmode="numeric" hint="Roughly is fine." />
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-select name="vehicle_make" label="Make" :options="array_combine(hadeed('makes'), hadeed('makes'))" :required="true" />
                    <x-field name="vehicle_model" label="Model" :required="true" maxlength="60" placeholder="e.g. Swift" />
                    <x-field name="vehicle_year" label="Year" type="number" :optional="true" min="1970" :max="now()->year + 1" inputmode="numeric" />
                </div>
            </fieldset>

            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">2</span>What it needs</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach (hadeed('service_types') as $v => $t)
                        <label class="choice"><input type="radio" name="service_type" value="{{ $v }}" required @checked(old('service_type') === $v)>{{ $t }}</label>
                    @endforeach
                </div>
                <x-error for="service_type" />
                <x-field name="message" label="Tell us more" type="textarea" :optional="true" rows="3" maxlength="1500" hint="Noises, warning lights, when it started. Required if you chose Something else." />
            </fieldset>

            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">3</span>Drop-off day</legend>
                @if ($serviceDays === [])
                    <p class="panel p-5">Online booking is closed at the moment. Please call {{ format_phone(hadeed('phone')) }}.</p>
                @else
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4" role="radiogroup" aria-label="Drop-off day">
                        @foreach (array_slice($serviceDays, 0, 16) as $d)
                            @php($value = $d['date']->toDateString())
                            <label class="choice !items-start !gap-2.5 !py-3">
                                <input type="radio" name="appointment_date" value="{{ $value }}" required class="mt-0.5" @checked($selectedDate === $value) @disabled($d['left'] < 1)>
                                <span class="leading-tight">
                                    <span class="block font-semibold">{{ $d['date']->format('D j M') }}</span>
                                    <span class="mt-0.5 block text-sm {{ $d['left'] < 1 ? 'text-ink-3' : ($d['left'] <= 2 ? 'font-semibold text-accent-text' : 'text-ink-3') }}">{{ $d['left'] < 1 ? 'Fully booked' : $d['left'].' '.\Illuminate\Support\Str::plural('place', $d['left']).' left' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-error for="appointment_date" />
                @endif
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-select name="appointment_time" label="Drop-off time" :options="collect(config('hadeed.booking.service_dropoff_slots'))->mapWithKeys(fn ($t) => [$t => date('g:i a', strtotime($t))])->all()" :required="true" />
                    <div class="sm:pt-8"><label class="choice"><input type="checkbox" name="waiting" value="1" @checked(old('waiting'))>I'd like to wait while it's done</label></div>
                </div>
            </fieldset>

            <x-contact-fields :step="4" />

            <div class="flex flex-col gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                <x-submit label="Request this booking" />
                <p class="text-sm text-ink-3">We hold your place and call to confirm the time.</p>
            </div>
        </form>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">Workshop hours</h2>
                <x-spec class="mt-3" :rows="collect(hadeed('hours'))->mapWithKeys(fn ($h) => [$h['days'] => $h['open'] ? date('g', strtotime($h['open'])).'am – '.date('g', strtotime($h['close'])).'pm' : 'Closed'])->all()" />
            </div>
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">Need a part instead?</h2>
                <p class="mt-1 text-ink-2">Send the VIN and a photo and the parts counter will call you back.</p>
                <a href="{{ route('parts') }}" class="btn btn-outline btn-sm mt-4">Request a part</a>
            </div>
            <x-photo key="parts-store" alt="Shelves of boxed spare parts in a parts store" sizes="(min-width: 1024px) 30vw, 100vw" class="aspect-[3/2] rounded-[14px]" />
        </aside>
    </section>
</x-layouts.site>
