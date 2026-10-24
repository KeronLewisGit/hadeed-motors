@php
    $active = array_filter($filters, fn ($v) => $v !== null);
    $sorts = ['newest' => 'Newest listings', 'price_asc' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'year_desc' => 'Year, newest first', 'km_asc' => 'Lowest mileage'];
@endphp
<x-layouts.site title="Pre-owned cars for sale" description="Pre-owned cars, SUVs and pickups for sale at Hadeed Motors, Old Parham Road, St John's, Antigua. Filter by make, body type, price, year and transmission.">
    <x-page-header title="Pre-owned cars" :lead="'Checked, serviced and ready to drive. '.$total.' '.\Illuminate\Support\Str::plural('vehicle', $total).' listed right now; finance can be arranged upstairs with FDC.'" code="USED" label="Pre-owned stock" photo="used-xtrail" position="50% 55%" />

    <div class="wrap mt-10 grid gap-10 lg:mt-14 lg:grid-cols-[17rem_1fr]">
        {{-- Filters: a plain GET form. With JS the results update on change; without it, press Show results. --}}
        <aside aria-labelledby="filter-title">
            <form method="GET" action="{{ route('inventory.index') }}" class="panel space-y-5 p-5 lg:sticky lg:top-24" x-data @change="$el.requestSubmit()">
                <h2 id="filter-title" class="flex items-center gap-2 text-lg font-bold font-sans"><x-icon name="sliders" class="h-5 w-5" />Filter</h2>
                <x-select name="make" label="Make" :options="$makes->mapWithKeys(fn ($n, $m) => [$m => $m.' ('.$n.')'])->all()" :value="$filters['make']" placeholder="Any make" />
                <x-select name="body" label="Body type" :options="$bodies->mapWithKeys(fn ($n, $b) => [$b => $b.' ('.$n.')'])->all()" :value="$filters['body']" placeholder="Any body type" />
                <x-select name="price_max" label="Price up to" :options="collect(\App\Http\Controllers\InventoryController::PRICE_STEPS)->mapWithKeys(fn ($p) => [$p => money($p)])->all()" :value="$filters['price_max']" placeholder="Any price" hint="Cars listed as Call for price are left out when you set a maximum." />
                <div class="grid grid-cols-2 gap-3">
                    <x-select name="year_min" label="Year from" :options="$years->mapWithKeys(fn ($y) => [$y => $y])->all()" :value="$filters['year_min']" placeholder="Any" />
                    <x-select name="year_max" label="Year to" :options="$years->mapWithKeys(fn ($y) => [$y => $y])->all()" :value="$filters['year_max']" placeholder="Any" />
                </div>
                <fieldset>
                    <legend class="label">Transmission</legend>
                    <div class="grid gap-2">
                        @foreach (['' => 'Any', 'automatic' => 'Automatic', 'manual' => 'Manual'] as $v => $t)
                            <label class="choice !min-h-11 !py-2"><input type="radio" name="transmission" value="{{ $v }}" @checked(($filters['transmission'] ?? '') === $v)>{{ $t }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <input type="hidden" name="sort" value="{{ $sort }}">
                <div class="flex flex-wrap gap-2 pt-1">
                    <button class="btn btn-primary btn-sm flex-1">Show results</button>
                    @if ($active)<a href="{{ route('inventory.index') }}" class="btn btn-outline btn-sm">Clear</a>@endif
                </div>
            </form>
        </aside>

        <section aria-labelledby="results-title">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 id="results-title" class="text-lg font-bold font-sans" aria-live="polite">{{ $vehicles->total() }} {{ \Illuminate\Support\Str::plural('vehicle', $vehicles->total()) }}{{ $active ? ' match' : '' }}</h2>
                <form method="GET" action="{{ route('inventory.index') }}" class="flex items-center gap-2" x-data @change="$el.requestSubmit()">
                    @foreach ($active as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                    <label for="sort" class="text-sm font-semibold text-ink-2">Sort</label>
                    <select id="sort" name="sort" class="input !min-h-11 !w-auto !py-2 text-[0.9375rem]">
                        @foreach ($sorts as $k => $t)<option value="{{ $k }}" @selected($sort === $k)>{{ $t }}</option>@endforeach
                    </select>
                    <noscript><button class="btn btn-outline btn-sm">Sort</button></noscript>
                </form>
            </div>
            @if ($active)
                <ul class="mt-4 flex flex-wrap gap-2" aria-label="Active filters">
                    @foreach ($active as $k => $v)
                        <li><a href="{{ request()->fullUrlWithoutQuery([$k, 'page']) }}" class="tag !min-h-10 text-sm" aria-label="Remove filter {{ $v }}">{{ $k === 'price_max' ? 'Up to '.money((int) $v) : ($k === 'year_min' ? 'From '.$v : ($k === 'year_max' ? 'To '.$v : ucfirst((string) $v))) }}<x-icon name="x" class="h-3.5 w-3.5" /></a></li>
                    @endforeach
                </ul>
            @endif

            @if ($vehicles->isEmpty())
                <div class="panel mt-6 p-8 text-center">
                    <p class="text-lg font-bold">Nothing matches those filters right now.</p>
                    <p class="mx-auto mt-2 max-w-md text-ink-2">New trade-ins arrive every week. Tell us what you're after and we'll call when one comes in.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline">Clear filters</a>
                        <a href="{{ route('contact', ['topic' => 'sales']) }}" class="btn btn-accent">Tell us what you want</a>
                    </div>
                </div>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($vehicles as $vehicle)<x-vehicle-card :vehicle="$vehicle" />@endforeach
                </div>
                <div class="mt-10">{{ $vehicles->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.site>
