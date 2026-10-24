@props(['key', 'model', 'headingLevel' => 'h3'])
@php($brand = hadeed('brands.'.$model['brand'].'.name'))
<article {{ $attributes->merge(['class' => 'card-link group flex flex-col']) }}>
    <x-photo :key="$model['photo']" :alt="$brand.' '.$model['name']" sizes="(min-width: 1024px) 30vw, (min-width: 640px) 45vw, 100vw" class="card-img aspect-[3/2] rounded-[14px]" />
    <div class="mt-4 flex items-baseline justify-between gap-3">
        <{{ $headingLevel }} class="text-xl font-bold"><a href="{{ route('lineup.show', [$model['brand'], $key]) }}" class="stretch"><span class="text-ink-3">{{ $brand }}</span> {{ $model['name'] }}</a></{{ $headingLevel }}>
        <span class="chip flex-none" data-tone="neutral">{{ $model['body'] }}</span>
    </div>
    <p class="mt-1.5 text-ink-2">{{ $model['tagline'] }}</p>
    <p class="mt-3 inline-flex items-center gap-1.5 font-semibold text-accent-text">See trims and book a drive<x-icon name="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" /></p>
</article>
