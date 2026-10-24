<x-layouts.site title="Site map" description="Every page on the Hadeed Motors website.">
    <x-page-header title="Site map" code="MAP" label="All pages" />
    <section class="wrap mt-12 grid gap-10 md:mt-16 md:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            'Buy' => ['New vehicles' => route('lineup.index'), 'Pre-owned' => route('inventory.index'), 'Book a test drive' => route('test-drive'), 'Value my trade-in' => route('trade-in')],
            'Finance' => ['Finance with FDC' => route('finance'), 'Get pre-qualified' => route('finance.apply')],
            'Own' => ['Service and booking' => route('service'), 'Request a part' => route('parts')],
            'Company' => ['About us' => route('about'), 'Group companies' => route('group'), 'Contact and hours' => route('contact'), 'Privacy notice' => route('privacy'), 'Photo credits' => route('credits')],
        ] as $group => $links)
            <div>
                <h2 class="text-lg font-bold font-sans">{{ $group }}</h2>
                <ul class="mt-3 space-y-1">@foreach ($links as $t => $u)<li><a class="text-link inline-flex min-h-11 items-center" href="{{ $u }}">{{ $t }}</a></li>@endforeach</ul>
            </div>
        @endforeach
        @foreach (hadeed('brands') as $bk => $b)
            <div>
                <h2 class="text-lg font-bold font-sans">New {{ $b['name'] }}</h2>
                <ul class="mt-3 space-y-1">@foreach (\App\Support\Lineup::all($bk) as $k => $m)<li><a class="text-link inline-flex min-h-11 min-w-11 items-center" href="{{ route('lineup.show', [$bk, $k]) }}">{{ $m['name'] }}</a></li>@endforeach</ul>
            </div>
        @endforeach
        @if ($stock->isNotEmpty())
            <div class="md:col-span-2">
                <h2 class="text-lg font-bold font-sans">Vehicles in stock</h2>
                <ul class="mt-3 space-y-1">@foreach ($stock as $v)<li><a class="text-link inline-flex min-h-11 items-center" href="{{ route('inventory.show', $v) }}">{{ $v->title }}</a></li>@endforeach</ul>
            </div>
        @endif
    </section>
</x-layouts.site>
