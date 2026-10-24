@php
    $brandName = hadeed('brands.'.$brandKey.'.name');
    $full = $brandName.' '.$m['name'];
@endphp
<x-layouts.site :title="'New '.$full" :description="$m['intro']" :og-image="$m['photo']" :test-drive-url="route('test-drive', ['model' => $key])"
    :json-ld="[[
        '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $full, 'brand' => ['@type' => 'Brand', 'name' => $brandName],
        'image' => asset('img/'.$m['photo'].'-1920.webp'), 'description' => $m['intro'], 'category' => $m['body'],
    ]]">
    <x-page-header :title="$full" :lead="$m['tagline']" :code="strtoupper(substr($brandName, 0, 3))" :label="'New '.$m['body']" :photo="$m['photo']">
        <nav aria-label="Breadcrumb" class="mt-6 text-sm text-white/75">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('lineup.index') }}">New vehicles</a></li>
                <li aria-hidden="true">/</li>
                <li><a class="inline-flex min-h-11 items-center hover:text-white" href="{{ route('lineup.brand', $brandKey) }}">{{ $brandName }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-white">{{ $m['name'] }}</li>
            </ol>
        </nav>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('test-drive', ['model' => $key]) }}" class="btn btn-accent">Book a test drive<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
            <a href="{{ route('finance.apply', ['interest' => 'm:'.$key]) }}" class="btn btn-on-dark">Get pre-qualified</a>
        </div>
    </x-page-header>

    <section class="wrap mt-14 grid gap-12 md:mt-20 lg:grid-cols-[1.25fr_1fr] lg:gap-16">
        <div>
            <x-plate-tag code="01" label="Overview" />
            <p class="lead mt-6 text-ink-2">{{ $m['intro'] }}</p>
            @if ($m['gallery'])
                <div class="mt-10 grid gap-4 sm:grid-cols-2">
                    <x-photo :key="$m['photo']" :alt="$full.', exterior'" sizes="(min-width: 1024px) 28vw, (min-width: 640px) 45vw, 100vw" class="aspect-[3/2] rounded-[14px]" />
                    @foreach ($m['gallery'] as $g)
                        <x-photo :key="$g" :alt="$full.(str_contains($g, 'interior') ? ', interior' : ', on the trail')" sizes="(min-width: 1024px) 28vw, (min-width: 640px) 45vw, 100vw" class="aspect-[3/2] rounded-[14px]" />
                    @endforeach
                </div>
                @if (str_contains(implode(' ', $m['gallery']), 'frontier-interior'))<p class="mt-2 text-sm text-ink-3">Interior shown is the Navara PRO-4X, the Frontier's sister model sold in other markets.</p>@endif
            @endif
        </div>
        <aside class="panel h-fit p-6 md:p-8" aria-labelledby="spec-title">
            <h2 id="spec-title" class="text-lg font-bold">At a glance</h2>
            <x-spec class="mt-4" :rows="['Brand' => $brandName, 'Body' => $m['body']] + $m['specs'] + ['Price' => 'Call for today\'s price']" />
            <div class="mt-6 grid gap-3">
                <a href="{{ tel_href(hadeed('phone')) }}" class="btn btn-primary"><x-icon name="phone" class="h-4 w-4" />Call sales: {{ format_phone(hadeed('phone')) }}</a>
                <a href="{{ wa_href(null, 'Hello, I am interested in the '.$full.'.') }}" class="btn btn-outline" target="_blank" rel="noopener"><x-icon name="whatsapp" class="h-4 w-4" />Ask on WhatsApp</a>
            </div>
        </aside>
    </section>

    @if ($m['trims'])
        <section class="wrap mt-16 md:mt-24" aria-labelledby="trims-title">
            <x-plate-tag code="02" label="Trims" />
            <h2 id="trims-title" class="display-2 mt-5">Choose your {{ $m['name'] }}</h2>
            <div @class(['mt-8 grid gap-5', 'md:grid-cols-2' => count($m['trims']) === 2, 'md:grid-cols-3' => count($m['trims']) >= 3, 'max-w-2xl' => count($m['trims']) === 1])>
                @foreach ($m['trims'] as $trim => $features)
                    <article class="panel flex flex-col p-6 md:p-7">
                        <p class="font-display text-2xl font-bold tracking-tight">{{ $trim }}</p>
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($features as $f)
                                <li class="flex gap-2.5"><x-icon name="check" class="mt-1 h-4 w-4 flex-none text-accent-text" />{{ $f }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('test-drive', ['model' => $key]) }}" class="btn btn-outline mt-auto self-start !mt-7">Drive the {{ $trim }}</a>
                    </article>
                @endforeach
            </div>
            <p class="mt-4 text-sm text-ink-3">Features from Hadeed Motors' own model pages. Specifications can change by shipment; confirm with the sales team.</p>
        </section>
    @endif

    @if ($stock->isNotEmpty())
        <section class="wrap mt-16 md:mt-24" aria-labelledby="stock-title">
            <x-plate-tag code="03" label="In stock" />
            <h2 id="stock-title" class="display-2 mt-5">{{ $m['name'] }} on the lot now</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($stock as $vehicle)<x-vehicle-card :vehicle="$vehicle" />@endforeach
            </div>
        </section>
    @endif

    @if ($others->isNotEmpty())
        <section class="wrap mt-16 md:mt-24" aria-labelledby="others-title">
            <h2 id="others-title" class="display-3">More from {{ $brandName }}</h2>
            <div class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($others as $k => $o)<x-model-card :key="$k" :model="$o" />@endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
