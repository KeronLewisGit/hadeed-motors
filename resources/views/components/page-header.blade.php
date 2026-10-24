@props(['title', 'lead' => null, 'code' => 'HM', 'label' => 'Hadeed Motors', 'photo' => null, 'position' => 'center'])
{{--
    The one hero band every inner page uses, so titles start at the same height and
    left edge site-wide. With a photo it sits behind a scrim; without one the band is
    plain graphite. A tick scale (the instrument-cluster motif) runs along the bottom.
    Extra content (breadcrumbs, actions, chips) goes in the slot.
--}}
<section {{ $attributes->merge(['class' => 'relative isolate overflow-hidden bg-graphite-900 text-white on-dark']) }}>
    @if ($photo)
        <div class="hero-photo absolute inset-0 -z-10">
            <x-photo :key="$photo" alt="" :eager="true" sizes="100vw" class="h-full w-full" :position="$position" />
            <div class="hero-scrim absolute inset-0"></div>
        </div>
    @endif
    <div class="wrap pb-10 pt-10 md:pb-16 md:pt-20">
        <x-plate-tag :code="$code" :label="$label" :dark="true" />
        <h1 class="display-2 mt-5 max-w-4xl">{{ $title }}</h1>
        @if ($lead)<p class="lead mt-4 text-white/85 md:mt-5">{{ $lead }}</p>@endif
        {{ $slot }}
    </div>
    <div class="ticks text-white" aria-hidden="true"></div>
</section>
