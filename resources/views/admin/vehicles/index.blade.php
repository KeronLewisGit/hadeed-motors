<x-layouts.admin title="Inventory">
    <x-slot:actions><a href="{{ route('admin.vehicles.create') }}" class="a-btn a-btn-primary"><x-icon name="plus" class="h-4 w-4" />Add vehicle</a></x-slot:actions>

    <form method="GET" class="flex flex-wrap items-end gap-3" x-data @change="$el.requestSubmit()">
        <div><label for="status" class="mb-1 block text-xs font-semibold text-ink-3">Status</label>
            <select id="status" name="status" class="a-input"><option value="">All ({{ $counts->sum() }})</option>@foreach (\App\Enums\VehicleStatus::cases() as $s)<option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }} ({{ $counts[$s->value] ?? 0 }})</option>@endforeach</select></div>
        <div><label for="condition" class="mb-1 block text-xs font-semibold text-ink-3">Condition</label>
            <select id="condition" name="condition" class="a-input"><option value="">New and pre-owned</option>@foreach (\App\Enums\Condition::cases() as $c)<option value="{{ $c->value }}" @selected($filters['condition'] === $c->value)>{{ $c->label() }}</option>@endforeach</select></div>
        <div class="flex-1"><label for="vq" class="mb-1 block text-xs font-semibold text-ink-3">Search</label><input id="vq" name="q" value="{{ $filters['q'] }}" class="a-input w-full min-w-48" placeholder="Make, model or stock number"></div>
        <button class="a-btn a-btn-ghost">Filter</button>
    </form>

    <div class="a-card relative mt-5 overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Vehicle</th><th>Stock no.</th><th class="r">Odometer</th><th class="r">Price</th><th>Photos</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @forelse ($vehicles as $v)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                @if ($v->photoUrl())<img src="{{ $v->photoUrl() }}" alt="" class="h-11 w-16 flex-none rounded object-cover">@else<span class="grid h-11 w-16 flex-none place-items-center rounded bg-surface-2"><x-icon name="car" class="h-5 w-5 text-ink-3" /></span>@endif
                                <div class="min-w-44"><a href="{{ route('admin.vehicles.edit', $v) }}" class="font-semibold hover:underline">{{ $v->title }}</a>
                                    <p class="text-xs text-ink-3">{{ $v->condition->label() }}@if($v->featured) · Featured @endif @if(! $v->published) · Hidden @endif @if($v->is_demo) · Sample @endif</p></div>
                            </div>
                        </td>
                        <td class="num whitespace-nowrap">{{ $v->stock_no ?? '—' }}</td>
                        <td class="r whitespace-nowrap">{{ $v->mileage_km !== null ? number_format($v->mileage_km).' km' : '—' }}</td>
                        <td class="r whitespace-nowrap">{{ $v->priceLabel() }}</td>
                        <td class="num">{{ $v->photos_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.vehicles.status', $v) }}" x-data @change="$el.requestSubmit()">
                                @csrf @method('PATCH')
                                <label class="sr-only" for="vs-{{ $v->id }}">Status of {{ $v->title }}</label>
                                <select id="vs-{{ $v->id }}" name="status" class="a-input !py-1 text-sm sm:!min-h-9">@foreach (\App\Enums\VehicleStatus::cases() as $s)<option value="{{ $s->value }}" @selected($v->status === $s)>{{ $s->label() }}</option>@endforeach</select>
                                <noscript><button class="a-btn a-btn-ghost !min-h-9">Set</button></noscript>
                            </form>
                        </td>
                        <td class="whitespace-nowrap"><a href="{{ route('inventory.show', $v) }}" target="_blank" class="text-sm underline">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-ink-3">No vehicles yet. <a href="{{ route('admin.vehicles.create') }}" class="underline">Add the first one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $vehicles->links() }}</div>
</x-layouts.admin>
