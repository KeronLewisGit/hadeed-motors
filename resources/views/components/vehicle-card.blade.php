@props(['vehicle', 'headingLevel' => 'h3'])
@php
    /** @var \App\Models\Vehicle $vehicle */
    $url = route('inventory.show', $vehicle);
    $photo = $vehicle->photoUrl();
@endphp
<article {{ $attributes->merge(['class' => 'card-link panel flex flex-col overflow-hidden']) }}>
    <div class="card-img photo relative aspect-[3/2]">
        @if ($photo)
            <img src="{{ $photo }}" alt="" loading="lazy" decoding="async" width="800" height="533">
        @else
            <div class="grid h-full place-items-center text-white/50"><x-icon name="car" class="h-12 w-12" /></div>
        @endif
        <div class="absolute left-3 top-3 flex flex-wrap gap-2">
            @if ($vehicle->status !== \App\Enums\VehicleStatus::Available)
                <x-status :status="$vehicle->status" />
            @endif
            @if ($vehicle->is_demo)<span class="demo-badge">Sample listing</span>@endif
        </div>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-sm font-semibold text-ink-3">{{ $vehicle->year }} · {{ $vehicle->body_type }}</p>
        <{{ $headingLevel }} class="mt-1 text-xl font-bold leading-snug">
            <a href="{{ $url }}" class="stretch">{{ $vehicle->make }} {{ $vehicle->model }}@if($vehicle->trim) <span class="font-semibold text-ink-2">{{ $vehicle->trim }}</span>@endif</a>
        </{{ $headingLevel }}>
        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-[0.9375rem] text-ink-2">
            @if ($vehicle->mileage_km !== null)<li class="num inline-flex items-center gap-1.5"><x-icon name="gauge" class="h-4 w-4 text-ink-3" />{{ number_format($vehicle->mileage_km) }} km</li>@endif
            <li class="inline-flex items-center gap-1.5"><x-icon name="cog" class="h-4 w-4 text-ink-3" />{{ $vehicle->transmission->label() }}</li>
            @if ($vehicle->fuel)<li class="inline-flex items-center gap-1.5"><x-icon name="fuel" class="h-4 w-4 text-ink-3" />{{ $vehicle->fuel }}</li>@endif
        </ul>
        <p class="mt-auto pt-4"><span class="price-tag">{{ $vehicle->priceLabel() }}</span></p>
    </div>
</article>
