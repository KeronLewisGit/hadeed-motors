<x-layouts.site>
    @php
        $models = \App\Support\Lineup::all();
        $showcase = ['frontier', 'jimny', 'grand-vitara', 'fronx', 'xl7', 'scross'];
    @endphp

    {{-- Hero: the real showroom on Old Parham Road --}}
    <section class="relative isolate overflow-hidden bg-graphite-900 text-white on-dark">
        <div class="hero-photo absolute inset-0 -z-10">
            <x-photo key="showroom" alt="" :eager="true" sizes="100vw" class="h-full w-full" position="22% 50%" />
            <div class="hero-scrim absolute inset-0"></div>
        </div>
        <div class="wrap pb-12 pt-40 sm:pt-48 md:pb-24 md:pt-28 lg:pt-36">
            <x-plate-tag code="1972" label="Old Parham Road · St John's" :dark="true" />
            <h1 class="display-1 mt-6 max-w-[15ch]">Antigua's car dealer since&nbsp;1972.</h1>
            <p class="lead mt-5 text-white/85">New Nissan and Suzuki, quality pre-owned cars, finance arranged upstairs with FDC, and a workshop that knows your car. Family-run, and still on Old Parham Road.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <a href="{{ route('test-drive') }}" class="btn btn-accent">Book a test drive<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                <a href="{{ route('lineup.index') }}" class="btn btn-on-dark">See the new line-up</a>
                <a href="{{ route('inventory.index') }}" class="btn btn-on-dark">Browse pre-owned</a>
            </div>
        </div>
    </section>

    {{-- Instrument cluster: four facts read like gauges --}}
    <section class="bg-graphite-950 text-white" aria-label="Hadeed Motors at a glance">
        <div class="ticks text-white" aria-hidden="true"></div>
        <dl class="wrap grid grid-cols-2 gap-x-6 gap-y-8 py-10 md:grid-cols-4 md:py-12">
            @foreach ([
                ['1972', 0, 'Year we opened, with Datsun (now Nissan)'],
                [str_pad((string) count(hadeed('brands')), 2, '0', STR_PAD_LEFT), 0, 'Franchises: Nissan and Suzuki'],
                [str_pad((string) count($models), 2, '0', STR_PAD_LEFT), 0, 'Models in the new line-up'],
                [str_pad((string) $usedCount, 2, '0', STR_PAD_LEFT), 2, 'Pre-owned cars on the lot today'],
            ] as [$value, $hl, $label])
                <div class="flex flex-col-reverse justify-end gap-3" data-reveal style="--reveal-delay: {{ $loop->index * 80 }}ms">
                    <dt class="max-w-[22ch] text-[0.9375rem] text-white/75">{{ $label }}</dt>
                    <dd><x-odo :value="$value" :highlight="$hl" class="text-[1.75rem] sm:text-4xl" :roll="true" /></dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- I'd like to… (the old site's best idea, now with real forms behind it) --}}
    <section class="wrap mt-16 md:mt-24" aria-labelledby="todo-title">
        <x-plate-tag code="01" label="Start here" />
        <h2 id="todo-title" class="display-2 mt-5">I'd like to…</h2>
        <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['steering', 'Book a test drive', 'Pick a car, a day and a time. We confirm by phone.', route('test-drive')],
                ['wrench', 'Book a service', 'See which days have space in the workshop and claim one.', route('service').'#book'],
                ['cog', 'Find a part', 'Send your VIN and a photo; the parts counter calls back.', route('parts')],
                ['swap', 'Value my trade-in', 'Photos and a few details are enough for a first figure.', route('trade-in')],
                ['percent', 'Get pre-qualified', 'Finance & Development Co. reviews it, usually within a working day.', route('finance.apply')],
                ['phone', 'Talk to someone', format_phone(hadeed('phone')).', Monday to Friday.', tel_href(hadeed('phone'))],
            ] as [$icon, $title, $text, $href])
                <li data-reveal style="--reveal-delay: {{ $loop->index * 50 }}ms">
                    <a href="{{ $href }}" class="panel group flex h-full items-start gap-4 p-5 transition hover:border-ink hover:shadow-md">
                        <span class="grid h-12 w-12 flex-none place-items-center rounded-full bg-accent-soft text-accent-text"><x-icon :name="$icon" class="h-6 w-6" /></span>
                        <span>
                            <span class="flex items-center gap-1.5 text-lg font-bold">{{ $title }}<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></span>
                            <span class="mt-1 block text-ink-2">{{ $text }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- New line-up --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="lineup-title">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <x-plate-tag code="02" label="New vehicles" />
                <h2 id="lineup-title" class="display-2 mt-5 max-w-[20ch]">The current Nissan and Suzuki line-up</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('lineup.brand', 'nissan') }}" class="tag">Nissan <span class="count">{{ count(\App\Support\Lineup::all('nissan')) }}</span></a>
                <a href="{{ route('lineup.brand', 'suzuki') }}" class="tag">Suzuki <span class="count">{{ count(\App\Support\Lineup::all('suzuki')) }}</span></a>
                <a href="{{ route('lineup.index') }}" class="tag" aria-current="true">All {{ count($models) }}</a>
            </div>
        </div>
        <div class="mt-10 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($showcase as $key)
                <x-model-card :key="$key" :model="$models[$key]" data-reveal />
            @endforeach
        </div>
        <p class="mt-10"><a href="{{ route('lineup.index') }}" class="btn btn-outline">All {{ count($models) }} new models<x-icon name="arrow-right" class="nudge h-4 w-4" /></a></p>
    </section>

    {{-- Pre-owned --}}
    @if ($featured->isNotEmpty())
        <section class="mt-20 bg-surface-2 py-16 md:mt-28 md:py-24" aria-labelledby="used-title">
            <div class="wrap">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <x-plate-tag code="03" label="Pre-owned" />
                        <h2 id="used-title" class="display-2 mt-5">On the lot this week</h2>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="btn btn-primary">All {{ $usedCount }} pre-owned<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $vehicle)
                        <x-vehicle-card :vehicle="$vehicle" data-reveal />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Finance --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="finance-title">
        <div class="grid overflow-hidden rounded-[18px] bg-graphite-900 text-white on-dark lg:grid-cols-[1.1fr_1fr]">
            <div class="p-7 sm:p-10 lg:p-14">
                <x-plate-tag code="04" label="Finance" :dark="true" />
                <h2 id="finance-title" class="display-2 mt-5">Finance is upstairs.</h2>
                <p class="lead mt-4 text-white/85">Finance &amp; Development Co. has lent to Antiguans since 1984 from the floor above our showroom. Choose the car and arrange the loan in one visit.</p>
                <ol class="mt-8 space-y-5">
                    @foreach ([
                        ['Pre-qualify online', 'Five minutes. Income band, deposit and the car you like. No bank numbers, ever.'],
                        ['FDC calls you', 'Usually within one working day, with what they need to see.'],
                        ['Drive home', 'Bring your documents, sign upstairs, collect the keys downstairs.'],
                    ] as $i => [$t, $d])
                        <li class="flex gap-4">
                            <x-odo :value="'0'.($i + 1)" class="flex-none text-lg" />
                            <div><p class="font-bold">{{ $t }}</p><p class="text-white/75">{{ $d }}</p></div>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('finance.apply') }}" class="btn btn-accent">Get pre-qualified<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    <a href="{{ route('finance') }}" class="btn btn-on-dark">{{ $financeRate ? 'Estimate a payment' : 'How finance works' }}</a>
                </div>
            </div>
            <x-photo key="model-frontier-interior" alt="Dashboard and touchscreen of a Nissan double-cab" sizes="(min-width: 1024px) 45vw, 100vw" class="min-h-64 lg:min-h-full" />
        </div>
    </section>

    {{-- Service --}}
    <section class="wrap mt-20 grid items-center gap-10 md:mt-28 lg:grid-cols-2 lg:gap-16" aria-labelledby="service-title">
        <x-photo key="service-bay" alt="A technician using wheel-balancing equipment in a service bay" sizes="(min-width: 1024px) 45vw, 100vw" class="aspect-[4/3] rounded-[18px]" position="50% 40%" />
        <div>
            <x-plate-tag code="05" label="Service & parts" />
            <h2 id="service-title" class="display-2 mt-5">Keep it running like the day you bought it.</h2>
            <p class="mt-5 measure text-ink-2">Book a drop-off online and see which days still have space. Our technicians work on Nissan and Suzuki every day, and the parts counter can order what isn't on the shelf.</p>
            <x-spec class="mt-7 max-w-md" :rows="['Workshop' => 'Mon–Thu 8–5, Fri 8–4', 'Drop-off' => 'From 8:00am', 'Parts' => 'Send a VIN, get a call back', 'Phone' => format_phone(hadeed('phone'))]" />
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('service') }}#book" class="btn btn-primary">Book a service<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                <a href="{{ route('parts') }}" class="btn btn-outline">Request a part</a>
            </div>
        </div>
    </section>

    {{-- Heritage --}}
    <section class="mt-20 bg-surface-2 py-16 md:mt-28 md:py-24" aria-labelledby="history-title">
        <div class="wrap grid gap-12 lg:grid-cols-[1fr_1.3fr]">
            <div>
                <x-plate-tag code="06" label="Since 1972" />
                <h2 id="history-title" class="display-2 mt-5">Putting a car within reach of the average family.</h2>
                <p class="mt-5 measure text-ink-2">That was Senator Aziz Hadeed's philosophy when the doors opened in 1972, and it is still the job. Several of Antigua's car-rental companies credit Hadeed Motors' support with helping them grow into major players.</p>
                <a href="{{ route('about') }}" class="btn btn-outline mt-8">Our story<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            </div>
            <ol class="relative space-y-8 border-l-2 border-line pl-8">
                @foreach (hadeed('history') as $h)
                    <li class="relative" data-reveal>
                        <span class="absolute -left-[2.45rem] top-1 h-4 w-4 rounded-full border-[3px] border-bg bg-accent" aria-hidden="true"></span>
                        @if (ctype_digit($h['year']))<x-odo :value="$h['year']" class="text-lg" />@else<p class="font-display text-lg font-medium">{{ $h['year'] }}</p>@endif
                        <p class="mt-2 measure text-ink-2">{{ $h['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Visit --}}
    <section class="wrap mt-20 md:mt-28" aria-labelledby="visit-title">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <x-plate-tag code="07" label="Visit" />
                <h2 id="visit-title" class="display-2 mt-5">Hadeed Motors Complex, Old Parham Road.</h2>
                <x-spec class="mt-8" :rows="collect(hadeed('hours'))->mapWithKeys(fn ($h) => [$h['days'] => $h['open'] ? date('g:i a', strtotime($h['open'])).' – '.date('g:i a', strtotime($h['close'])) : 'Closed'])->all()" />
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ hadeed('map_url') }}" class="btn btn-primary" target="_blank" rel="noopener"><x-icon name="map-pin" class="h-4 w-4" />Get directions</a>
                    <a href="{{ route('contact') }}" class="btn btn-outline">Contact us</a>
                </div>
            </div>
            <x-photo key="stjohns-harbour" alt="St John's harbour front with colourful buildings and a catamaran" sizes="(min-width: 1024px) 45vw, 100vw" class="aspect-[3/2] rounded-[18px]" />
        </div>
    </section>
</x-layouts.site>
