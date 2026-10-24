@php
    $brandName = $brand ? hadeed('brands.'.$brand.'.name') : null;
    $title = $brand ? 'New '.$brandName : 'New Nissan and Suzuki';
@endphp
<x-layouts.site :title="$title" :description="$brand ? hadeed('brands.'.$brand.'.blurb') : 'The current Nissan and Suzuki line-up at Hadeed Motors, St John\'s, Antigua: trims, features and test drives.'">
    <x-page-header :title="$title" :lead="$brand ? hadeed('brands.'.$brand.'.blurb') : 'Every model we sell new, with the trims and features from our own spec sheets. Prices change with each shipment, so ask the sales team for today\'s figure.'" code="NEW" :label="$brand ? $brandName.' since '.hadeed('brands.'.$brand.'.since') : 'New vehicles'" photo="model-frontier" position="50% 60%">
        <nav class="tag-row mt-8" aria-label="Filter by brand">
            <a href="{{ route('lineup.index') }}" class="tag" @if(! $brand) aria-current="page" @endif>All <span class="count">{{ count(\App\Support\Lineup::all()) }}</span></a>
            @foreach (hadeed('brands') as $key => $b)
                <a href="{{ route('lineup.brand', $key) }}" class="tag" @if($brand === $key) aria-current="page" @endif>{{ $b['name'] }} <span class="count">{{ count(\App\Support\Lineup::all($key)) }}</span></a>
            @endforeach
        </nav>
    </x-page-header>

    @foreach ($brand ? [$brand] : \App\Support\Lineup::brands() as $b)
        <section class="wrap mt-14 md:mt-20" aria-labelledby="brand-{{ $b }}">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-5">
                <h2 id="brand-{{ $b }}" class="display-3">{{ hadeed('brands.'.$b.'.name') }}</h2>
                <p class="text-ink-3">Sold by Hadeed Motors since {{ hadeed('brands.'.$b.'.since') }}</p>
            </div>
            <div class="mt-8 grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (\App\Support\Lineup::all($b) as $key => $model)
                    <x-model-card :key="$key" :model="$model" data-reveal />
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="wrap mt-20">
        <div class="panel flex flex-col gap-6 p-7 md:flex-row md:items-center md:justify-between md:p-10">
            <div>
                <h2 class="display-3">Not sure which one?</h2>
                <p class="mt-2 measure text-ink-2">Book a test drive and tell us how you'll use it. We'll have two to compare ready when you arrive.</p>
            </div>
            <a href="{{ route('test-drive') }}" class="btn btn-accent flex-none">Book a test drive<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        </div>
    </section>
</x-layouts.site>
