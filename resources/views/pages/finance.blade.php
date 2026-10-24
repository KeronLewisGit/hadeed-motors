@php($fdc = hadeed('finance'))
<x-layouts.site title="Car finance with FDC" description="Finance your new or pre-owned car with Finance & Development Co., upstairs in the Hadeed Motors showroom since 1984. Pre-qualify online, then bring your documents.">
    <x-page-header title="Finance, upstairs from the showroom." lead="Finance & Development Co. (FDC) has lent to the people of Antigua and Barbuda since 1984. Choose your car downstairs, arrange the loan upstairs, drive home the same week." code="FDC" label="Finance" photo="model-fronx-interior" position="50% 60%">
        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('finance.apply') }}" class="btn btn-accent">Get pre-qualified<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="{{ tel_href($fdc['phone']) }}" class="btn btn-on-dark"><x-icon name="phone" class="h-4 w-4" />FDC {{ format_phone($fdc['phone']) }}</a>
        </div>
    </x-page-header>

    <section class="wrap mt-14 grid gap-12 md:mt-20 lg:grid-cols-[1fr_1fr] lg:gap-16" aria-labelledby="how-title">
        <div>
            <x-plate-tag code="01" label="How it works" />
            <h2 id="how-title" class="display-2 mt-5">Four steps to your keys</h2>
            <ol class="mt-8 space-y-7">
                @foreach ([
                    ['Pre-qualify online', 'Tell us roughly what you earn, how much deposit you have and which car you like. It takes about five minutes and is not a credit application.'],
                    ['FDC calls you', 'A loans officer reviews it, usually within one working day, and tells you what to bring. FDC says decisions can come in as little as 24 hours once they have your documents.'],
                    ['Bring your documents', 'Upstairs in the Hadeed Motors showroom, '.$fdc['hours'].'.'],
                    ['Sign and drive', 'FDC pays the dealership, we prepare the car, and you collect the keys downstairs.'],
                ] as $i => [$t, $d])
                    <li class="flex gap-5">
                        <x-odo :value="'0'.($i + 1)" class="flex-none text-xl" />
                        <div><h3 class="text-lg font-bold">{{ $t }}</h3><p class="mt-1 measure text-ink-2">{{ $d }}</p></div>
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Loan estimator. Hidden until the dealership publishes an indicative rate in Settings. --}}
        <div id="estimate">
            @if ($rate)
                <form method="GET" action="{{ route('finance') }}#estimate" class="panel p-6 md:p-8"
                    x-data="loanEstimator({ rate: {{ $rate }}, price: '{{ $input['price'] }}', deposit: '{{ $input['deposit'] }}', term: {{ $input['term'] }} })" @submit.prevent>
                    <h2 class="text-xl font-bold font-sans">Estimate a monthly payment</h2>
                    <p class="mt-1 text-ink-2">At FDC's indicative rate of <strong class="num">{{ rtrim(rtrim(number_format($rate, 2), '0'), '.') }}% a year</strong>.</p>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-field name="price" label="Car price (EC$)" inputmode="numeric" x-model="price" :value="$input['price']" placeholder="e.g. 65000" />
                        <x-field name="deposit" label="Deposit (EC$)" inputmode="numeric" x-model="deposit" :value="$input['deposit']" placeholder="e.g. 15000" />
                    </div>
                    <div class="mt-5">
                        <x-select name="term" label="Term" :options="collect($fdc['terms'])->mapWithKeys(fn ($t) => [$t => $t.' months ('.($t / 12).' years)'])->all()" :value="$input['term']" :placeholder="null" x-model.number="term" />
                    </div>
                    <noscript><button class="btn btn-primary mt-5" formaction="{{ route('finance') }}#estimate">Calculate</button></noscript>
                    <div class="mt-6 rounded-[12px] bg-graphite-900 p-5 text-white" aria-live="polite">
                        <p class="text-sm font-semibold text-white/70">Estimated monthly payment</p>
                        <p class="font-display num mt-1 text-3xl font-bold" x-text="ready ? money(monthly, true) : '—'">{{ $estimate ? money($estimate['monthly'], true) : '—' }}</p>
                        <p class="mt-1 text-sm text-white/70" x-show="ready" x-text="'Borrowing ' + money(principal) + ' over ' + term + ' months'">@if($estimate)Borrowing {{ money($estimate['principal']) }} over {{ $estimate['months'] }} months @endif</p>
                    </div>
                    <p class="mt-4 text-sm text-ink-3">An estimate only, not a loan offer. It leaves out fees and insurance. FDC sets the actual rate, fees and term when it assesses your application.</p>
                </form>
            @else
                <div class="panel p-6 md:p-8">
                    <h2 class="text-xl font-bold font-sans">What will it cost a month?</h2>
                    <p class="mt-2 measure text-ink-2">Rates depend on the car, your deposit and the term, so FDC quotes each customer individually. Pre-qualify and they will call you with real figures, or ring the loans team directly.</p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('finance.apply') }}" class="btn btn-accent">Get pre-qualified</a>
                        <a href="{{ tel_href($fdc['phone']) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone($fdc['phone']) }}</a>
                    </div>
                </div>
            @endif

            <div class="mt-6 rounded-[14px] border border-line bg-surface-2 p-6">
                <h2 class="flex items-center gap-2 text-lg font-bold font-sans"><x-icon name="shield" class="h-5 w-5 text-accent-text" />Your details are safe</h2>
                <ul class="mt-3 space-y-2 text-ink-2">
                    <li>We never ask for bank account numbers, card numbers or PINs online.</li>
                    <li>Uploaded ID and pay slips are stored privately and only FDC's finance staff can open them.</li>
                    <li>{{ $fdc['regulator'] }}.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="mt-20 bg-surface-2 py-16 md:mt-28 md:py-20" aria-labelledby="docs-title">
        <div class="wrap grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <x-plate-tag code="02" label="What to bring" />
                <h2 id="docs-title" class="display-2 mt-5">Documents FDC will ask for</h2>
                <p class="mt-4 measure text-ink-2">From FDC's published loan requirements. Having them ready is the fastest way to a decision.</p>
            </div>
            <ul class="space-y-4">
                @foreach ($fdc['documents'] as $doc)
                    <li class="panel flex gap-4 p-5"><x-icon name="file" class="mt-0.5 h-5 w-5 flex-none text-accent-text" /><span>{{ $doc }}</span></li>
                @endforeach
                <li class="text-sm text-ink-3">Non-nationals may also need a police record and immigration documents. Ask the loans team.</li>
            </ul>
        </div>
    </section>

    <section class="wrap mt-20" aria-labelledby="fdc-title">
        <div class="grid gap-8 md:grid-cols-2">
            <div>
                <h2 id="fdc-title" class="display-3">{{ $fdc['name'] }}</h2>
                <x-spec class="mt-6" :rows="['Where' => $fdc['location'], 'Hours' => $fdc['hours'], 'Phone' => format_phone($fdc['phone']), 'Email' => $fdc['email'], 'Since' => $fdc['since']]" />
                <a href="{{ $fdc['url'] }}" class="btn btn-outline mt-6" target="_blank" rel="noopener">Visit fdcantigua.com<x-icon name="external" class="h-4 w-4" /></a>
            </div>
            <div class="panel p-6 md:p-8">
                <h2 class="text-lg font-bold font-sans">Questions people ask</h2>
                <dl class="mt-4 divide-y divide-line">
                    @foreach ([
                        ['Can I finance a car from somewhere else?', 'FDC finances vehicles sold by Hadeed Motors and its affiliated companies.'],
                        ['I have bad credit. Can I still apply?', 'Yes. FDC\'s loans team will talk through your situation and advise you.'],
                        ['How do I make payments?', 'Cash or cheque at the office, debit or credit card, standing order, salary deduction, or online transfer from ECAB, CIBC Caribbean or ACB Caribbean.'],
                    ] as [$q, $a])
                        <div class="py-4"><dt class="font-semibold">{{ $q }}</dt><dd class="mt-1 text-ink-2">{{ $a }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>
</x-layouts.site>
