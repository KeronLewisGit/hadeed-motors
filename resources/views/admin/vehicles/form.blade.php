@php
    /** @var \App\Models\Vehicle $vehicle */
    $editing = $vehicle->exists;
    $v = fn (string $k) => old($k, $vehicle->{$k} instanceof \BackedEnum ? $vehicle->{$k}->value : $vehicle->{$k});
    $input = 'a-input w-full';
@endphp
<x-layouts.admin :title="$editing ? $vehicle->title : 'Add a vehicle'">
    <x-slot:actions>
        @if ($editing)<a href="{{ route('inventory.show', $vehicle) }}" target="_blank" class="a-btn a-btn-ghost"><x-icon name="external" class="h-4 w-4" />View on site</a>@endif
        <a href="{{ route('admin.vehicles.index') }}" class="a-btn a-btn-ghost">Back to inventory</a>
    </x-slot:actions>

    <form method="POST" action="{{ $editing ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        @csrf @if ($editing) @method('PUT') @endif
        <div class="space-y-6">
            <section class="a-card space-y-4 p-5">
                <h2 class="font-semibold">Vehicle</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="mb-1 block text-sm font-semibold" for="condition">Condition</label><select id="condition" name="condition" class="{{ $input }}">@foreach (\App\Enums\Condition::cases() as $c)<option value="{{ $c->value }}" @selected($v('condition') === $c->value)>{{ $c->label() }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="year">Year</label><input id="year" name="year" type="number" value="{{ $v('year') }}" class="{{ $input }}" required></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="stock_no">Stock no. <span class="font-normal text-ink-3">(optional)</span></label><input id="stock_no" name="stock_no" value="{{ $v('stock_no') }}" class="{{ $input }}"></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="mb-1 block text-sm font-semibold" for="make">Make</label><input id="make" name="make" value="{{ $v('make') }}" list="makes" class="{{ $input }}" required><datalist id="makes">@foreach (hadeed('makes') as $m)<option value="{{ $m }}">@endforeach</datalist></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="model">Model</label><input id="model" name="model" value="{{ $v('model') }}" class="{{ $input }}" required></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="trim">Trim <span class="font-normal text-ink-3">(optional)</span></label><input id="trim" name="trim" value="{{ $v('trim') }}" class="{{ $input }}"></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="mb-1 block text-sm font-semibold" for="body_type">Body</label><select id="body_type" name="body_type" class="{{ $input }}">@foreach (hadeed('body_types') as $b)<option @selected($v('body_type') === $b)>{{ $b }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="transmission">Transmission</label><select id="transmission" name="transmission" class="{{ $input }}">@foreach (\App\Enums\Transmission::cases() as $t)<option value="{{ $t->value }}" @selected($v('transmission') === $t->value)>{{ $t->label() }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="fuel">Fuel</label><select id="fuel" name="fuel" class="{{ $input }}"><option value="">—</option>@foreach (['Petrol', 'Diesel', 'Hybrid', 'Electric'] as $f)<option @selected($v('fuel') === $f)>{{ $f }}</option>@endforeach</select></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div><label class="mb-1 block text-sm font-semibold" for="mileage_km">Odometer (km)</label><input id="mileage_km" name="mileage_km" type="number" min="0" value="{{ $v('mileage_km') }}" class="{{ $input }}"></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="engine">Engine</label><input id="engine" name="engine" value="{{ $v('engine') }}" class="{{ $input }}" placeholder="1.5 litre"></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="drive">Drive</label><select id="drive" name="drive" class="{{ $input }}"><option value="">—</option>@foreach (['2WD', '4WD', 'AWD'] as $dr)<option @selected($v('drive') === $dr)>{{ $dr }}</option>@endforeach</select></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="seats">Seats</label><input id="seats" name="seats" type="number" min="1" max="30" value="{{ $v('seats') }}" class="{{ $input }}"></div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-semibold" for="colour">Colour</label><input id="colour" name="colour" value="{{ $v('colour') }}" class="{{ $input }}"></div>
                    <div><label class="mb-1 block text-sm font-semibold" for="model_key">Line-up model <span class="font-normal text-ink-3">(new stock)</span></label><select id="model_key" name="model_key" class="{{ $input }}"><option value="">None</option>@foreach (\App\Support\Lineup::options() as $k => $l)<option value="{{ $k }}" @selected($v('model_key') === $k)>{{ $l }}</option>@endforeach</select><p class="mt-1 text-xs text-ink-3">New units appear under "On the lot now" on that model's page.</p></div>
                </div>
            </section>
            <section class="a-card space-y-4 p-5">
                <h2 class="font-semibold">Description</h2>
                <div><label class="mb-1 block text-sm font-semibold" for="description">About this vehicle</label><textarea id="description" name="description" rows="5" class="{{ $input }}">{{ $v('description') }}</textarea></div>
                <div><label class="mb-1 block text-sm font-semibold" for="features_text">Features, one per line</label><textarea id="features_text" name="features_text" rows="5" class="{{ $input }}">{{ old('features_text', implode("\n", $vehicle->features ?? [])) }}</textarea></div>
            </section>
            <section id="photos" class="a-card p-5">
                <h2 class="font-semibold">Photos</h2>
                @if ($editing && $vehicle->photos->isNotEmpty())
                    <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($vehicle->photos as $p)
                            <li class="overflow-hidden rounded-lg border border-line">
                                <img src="{{ $p->url() }}" alt="Photo {{ $loop->iteration }}" class="aspect-[3/2] w-full object-cover">
                                <div class="flex items-center justify-between gap-1 p-1.5">
                                    @if ($loop->first)<span class="px-1.5 text-xs font-semibold">Cover</span>@else
                                        <button form="cover-{{ $p->id }}" class="a-btn !min-h-9 px-2 text-xs hover:bg-surface-2">Make cover</button>@endif
                                    <button form="del-{{ $p->id }}" class="a-btn !min-h-9 px-2 text-xs text-[color:var(--critical-text)] hover:bg-surface-2" aria-label="Delete photo {{ $loop->iteration }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <label class="mt-4 block text-sm font-semibold" for="photos-input">Add photos</label>
                <input id="photos-input" type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="a-input mt-1 w-full">
                <p class="mt-1 text-xs text-ink-3">JPG, PNG or WebP, at least 800 px wide, up to 8 MB each, {{ \App\Http\Controllers\Admin\VehicleController::MAX_PHOTOS }} per vehicle. The first photo is the cover.</p>
            </section>
        </div>
        <div class="space-y-6">
            <section class="a-card space-y-4 p-5 xl:sticky xl:top-24">
                <h2 class="font-semibold">Listing</h2>
                <div><label class="mb-1 block text-sm font-semibold" for="price">Price (EC$)</label><input id="price" name="price" type="number" min="1000" step="100" value="{{ $v('price') }}" class="{{ $input }}"><p class="mt-1 text-xs text-ink-3">Leave blank to show "Call for price".</p></div>
                <div><label class="mb-1 block text-sm font-semibold" for="vstatus">Status</label><select id="vstatus" name="status" class="{{ $input }}">@foreach (\App\Enums\VehicleStatus::cases() as $s)<option value="{{ $s->value }}" @selected($v('status') === $s->value)>{{ $s->label() }}</option>@endforeach</select><p class="mt-1 text-xs text-ink-3">Sold cars stay listed as Sold for 7 days, then drop off the site.</p></div>
                <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="featured" value="1" class="h-4 w-4" @checked(old('featured', $vehicle->featured))>Feature on the home page</label>
                <label class="flex min-h-11 items-center gap-2"><input type="checkbox" name="published" value="1" class="h-4 w-4" @checked(old('published', $vehicle->published))>Show on the website</label>
                <button class="a-btn a-btn-primary w-full">{{ $editing ? 'Save changes' : 'Add vehicle' }}</button>
            </section>
            @if ($editing)
                <section class="a-card p-5">
                    <h2 class="font-semibold">Delete</h2>
                    <p class="mt-1 text-sm text-ink-3">Removes the listing and its photos. Leads keep their history. Prefer "Sold" for cars that sold.</p>
                    <button form="delete-vehicle" class="a-btn a-btn-ghost mt-3 text-[color:var(--critical-text)]" onclick="return confirm('Delete this vehicle and its photos?')"><x-icon name="trash" class="h-4 w-4" />Delete vehicle</button>
                </section>
            @endif
        </div>
    </form>
    @if ($editing)
        <form id="delete-vehicle" method="POST" action="{{ route('admin.vehicles.destroy', $vehicle) }}">@csrf @method('DELETE')</form>
        @foreach ($vehicle->photos as $p)
            <form id="del-{{ $p->id }}" method="POST" action="{{ route('admin.vehicles.photos.destroy', [$vehicle, $p]) }}">@csrf @method('DELETE')</form>
            <form id="cover-{{ $p->id }}" method="POST" action="{{ route('admin.vehicles.photos.cover', [$vehicle, $p]) }}">@csrf @method('PATCH')</form>
        @endforeach
    @endif
</x-layouts.admin>
