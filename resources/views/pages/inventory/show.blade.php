@php
    /** @var \App\Models\Vehicle $vehicle */
    $photos = $vehicle->photos;
    $title = $vehicle->title;
    $available = $vehicle->status === \App\Enums\VehicleStatus::Available;
@endphp
<x-layouts.site :title="$title" :description="$title.', '.($vehicle->mileage_km !== null ? number_format($vehicle->mileage_km).' km, ' : '').$vehicle->transmission->label().'. '.$vehicle->priceLabel().' at Hadeed Motors, St John\'s, Antigua.'"
    :og-image="$photos->first() ? url($photos->first()->url()) : 'showroom'" :test-drive-url="route('test-drive', ['vehicle' => $vehicle->slug])"
    :json-ld="[array_filter([
        '@context' => 'https://schema.org', '@type' => 'Car', 'name' => $title, 'brand' => ['@type' => 'Brand', 'name' => $vehicle->make],
        'model' => $vehicle->model, 'vehicleModelDate' => (string) $vehicle->year, 'bodyType' => $vehicle->body_type,
        'vehicleTransmission' => $vehicle->transmission->label(), 'color' => $vehicle->colour,
        'mileageFromOdometer' => $vehicle->mileage_km !== null ? ['@type' => 'QuantitativeValue', 'value' => $vehicle->mileage_km, 'unitCode' => 'KMT'] : null,
        'itemCondition' => $vehicle->condition === \App\Enums\Condition::New ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
        'image' => $photos->map(fn ($p) => url($p->url()))->all(),
        'offers' => $vehicle->price ? ['@type' => 'Offer', 'price' => $vehicle->price, 'priceCurrency' => 'XCD', 'availability' => $available ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut'] : null,
    ])]">
    <x-page-header :title="$title" :code="$vehicle->condition === \App\Enums\Condition::New ? 'NEW' : 'USED'" :label="$vehicle->stock_no ? 'Stock '.$vehicle->stock_no : $vehicle->body_type">
        <nav aria-label="Breadcrumb" class="mt-5 text-sm text-white/75">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a class="inline-flex min-h-11 min-w-11 items-center hover:text-white" href="{{ route('inventory.index') }}">Pre-owned</a></li>
                <li aria-hidden="true">/</li>
                <li><a class="inline-flex min-h-11 min-w-11 items-center hover:text-white" href="{{ route('inventory.index', ['make' => $vehicle->make]) }}">{{ $vehicle->make }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-white">{{ $vehicle->model }}</li>
            </ol>
        </nav>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <x-status :status="$vehicle->status" />
            @if ($vehicle->is_demo)<span class="demo-badge !bg-white/15">Sample listing</span>@endif
        </div>
    </x-page-header>

    <div class="wrap mt-10 grid gap-10 md:mt-14 lg:grid-cols-[1.5fr_1fr] lg:gap-14">
        <section aria-label="Photos">
            @if ($photos->isNotEmpty())
                <div x-data="gallery({{ $photos->count() }})" @keydown="key($event)">
                    <div class="photo relative aspect-[3/2] overflow-hidden rounded-[16px]" tabindex="0" aria-roledescription="carousel" aria-label="Photo gallery, use the arrow keys">
                        @foreach ($photos as $i => $p)
                            <img src="{{ $p->url() }}" alt="{{ $title }}, photo {{ $i + 1 }} of {{ $photos->count() }}" width="{{ $p->width ?? 1920 }}" height="{{ $p->height ?? 1280 }}"
                                class="absolute inset-0 h-full w-full object-cover" @if($i > 0) loading="lazy" x-cloak x-show="i === {{ $i }}" @else x-show="i === 0" fetchpriority="high" style="z-index: 1" @endif>
                        @endforeach
                        @if ($photos->count() > 1)
                            <div class="absolute inset-x-3 bottom-3 z-10 flex items-center justify-between">
                                <button type="button" class="grid h-11 w-11 place-items-center rounded-full bg-graphite-900/80 text-white" @click="go(i - 1)" aria-label="Previous photo"><x-icon name="chevron-left" /></button>
                                <span class="rounded-full bg-graphite-900/80 px-3 py-1 text-sm font-semibold text-white num" x-text="(i + 1) + ' / ' + count">1 / {{ $photos->count() }}</span>
                                <button type="button" class="grid h-11 w-11 place-items-center rounded-full bg-graphite-900/80 text-white" @click="go(i + 1)" aria-label="Next photo"><x-icon name="chevron-right" /></button>
                            </div>
                        @endif
                    </div>
                    @if ($photos->count() > 1)
                        <ul class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6">
                            @foreach ($photos as $i => $p)
                                <li><button type="button" class="photo block aspect-[3/2] w-full overflow-hidden rounded-lg ring-offset-2 ring-offset-bg" :class="i === {{ $i }} && 'ring-2 ring-accent'" @click="go({{ $i }})" aria-label="Show photo {{ $i + 1 }}"><img src="{{ $p->thumbUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover"></button></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @else
                <div class="photo grid aspect-[3/2] place-items-center rounded-[16px] text-white/60"><p class="flex flex-col items-center gap-2"><x-icon name="car" class="h-12 w-12" />Photos coming soon</p></div>
            @endif

            @if ($vehicle->description)
                <h2 class="display-3 mt-10">About this {{ $vehicle->model }}</h2>
                <div class="prose-copy mt-4 measure text-ink-2">{!! nl2br(e($vehicle->description)) !!}</div>
            @endif
            @if ($vehicle->features)
                <h2 class="display-3 mt-10">Features</h2>
                <ul class="mt-4 grid gap-x-6 gap-y-2.5 sm:grid-cols-2">
                    @foreach ($vehicle->features as $f)<li class="flex gap-2.5"><x-icon name="check" class="mt-1 h-4 w-4 flex-none text-accent-text" />{{ $f }}</li>@endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-6" aria-label="Price and details">
            <div class="panel p-6 md:p-7">
                <p class="text-sm font-semibold text-ink-3">{{ $vehicle->price ? 'Price' : 'Price on request' }}</p>
                <p class="font-display num mt-1 text-3xl font-bold tracking-tight">{{ $vehicle->priceLabel() }}</p>
                @if ($vehicle->price)<p class="mt-1 text-sm text-ink-3">{{ usd_hint($vehicle->price) }} at the fixed EC$2.70 rate</p>@endif
                @if ($vehicle->mileage_km !== null)
                    <p class="mt-5 text-sm font-semibold text-ink-3">Odometer</p>
                    <x-odo :value="str_pad((string) $vehicle->mileage_km, 6, '0', STR_PAD_LEFT)" :highlight="1" class="mt-1.5 text-xl" />
                    <span class="ml-1 text-sm font-semibold text-ink-3">km</span>
                @endif
                <x-spec class="mt-5" :rows="[
                    'Year' => $vehicle->year, 'Make' => $vehicle->make, 'Model' => trim($vehicle->model.' '.$vehicle->trim), 'Body' => $vehicle->body_type,
                    'Transmission' => $vehicle->transmission->label(), 'Fuel' => $vehicle->fuel, 'Engine' => $vehicle->engine, 'Drive' => $vehicle->drive,
                    'Colour' => $vehicle->colour, 'Seats' => $vehicle->seats,
                ]" />
                <div class="mt-6 grid gap-3">
                    @if ($available)
                        <a href="{{ route('test-drive', ['vehicle' => $vehicle->slug]) }}" class="btn btn-accent">Book a test drive<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
                    @endif
                    <a href="{{ tel_href(hadeed('phone')) }}" class="btn btn-primary"><x-icon name="phone" class="h-4 w-4" />Call {{ format_phone(hadeed('phone')) }}</a>
                    <a href="{{ wa_href(null, 'Hello, is the '.$title.' ('.route('inventory.show', $vehicle).') still available?') }}" class="btn btn-outline" target="_blank" rel="noopener"><x-icon name="whatsapp" class="h-4 w-4" />Ask on WhatsApp</a>
                </div>
            </div>

            <div class="panel p-6 md:p-7">
                <h2 class="text-lg font-bold font-sans">Finance this car</h2>
                @if ($estimate)
                    <p class="mt-2 text-ink-2">With a {{ money($estimate['deposit']) }} deposit over {{ $estimate['months'] }} months at an indicative {{ plain_number($estimate['rate']) }}%:</p>
                    <p class="font-display num mt-3 text-2xl font-bold">{{ money($estimate['monthly'], true) }}<span class="text-base font-sans font-semibold text-ink-3"> a month</span></p>
                    <p class="mt-2 text-sm text-ink-3">An estimate only, not an offer. FDC sets the rate, fees and term when it reviews your application.</p>
                @else
                    <p class="mt-2 text-ink-2">Finance &amp; Development Co. is upstairs in our showroom. Pre-qualify online and they will call you.</p>
                @endif
                <div class="mt-5 flex flex-col gap-3 sm:flex-row lg:flex-col xl:flex-row">
                    <a href="{{ route('finance.apply', ['interest' => 'v:'.$vehicle->id]) }}" class="btn btn-outline btn-sm">Get pre-qualified</a>
                    <a href="{{ route('trade-in') }}" class="btn btn-outline btn-sm">Value my trade-in</a>
                </div>
            </div>
        </aside>
    </div>

    @if ($similar->isNotEmpty())
        <section class="wrap mt-20" aria-labelledby="similar-title">
            <h2 id="similar-title" class="display-3">You might also like</h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($similar as $s)<x-vehicle-card :vehicle="$s" />@endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
