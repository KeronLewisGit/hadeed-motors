<x-layouts.admin title="Finance pipeline">
    <p class="-mt-2 mb-5 max-w-3xl text-ink-2">Pre-qualifications sent to Finance &amp; Development Co. Move a card by changing its stage. ID and pay slips open for the finance role only{{ auth()->user()->isAdmin() ? ', so administrators see a lock' : '' }}.</p>
    <div class="flex snap-x gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $col)
            <section class="w-72 flex-none snap-start" aria-labelledby="col-{{ $col['status']->value }}">
                <h2 id="col-{{ $col['status']->value }}" class="mb-3 flex items-center justify-between"><x-chip :tone="$col['status']->tone()" :icon="$col['status']->icon()">{{ $col['status']->label() }}</x-chip><span class="num text-sm text-ink-3">{{ $col['leads']->count() }}</span></h2>
                <ul class="space-y-3">
                    @forelse ($col['leads']->take(30) as $l)
                        <li class="a-card p-4">
                            <a href="{{ route('admin.leads.show', $l) }}" class="font-semibold hover:underline">{{ $l->name }}</a>
                            <p class="num text-sm text-ink-3">{{ $l->reference }} · {{ $l->created_at->format('j M') }}</p>
                            <p class="mt-2 truncate text-sm">{{ $l->subject() ?? 'Vehicle not chosen' }}</p>
                            <p class="mt-1 text-sm text-ink-2">{{ hadeed('finance.income_bands.'.$l->detail('income_band'), '') }}@if($l->detail('deposit') !== null) · {{ money($l->detail('deposit')) }} down @endif</p>
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <span class="inline-flex items-center gap-1 text-xs text-ink-3"><x-icon name="file" class="h-3.5 w-3.5" />{{ $l->files_count }} {{ \Illuminate\Support\Str::plural('file', $l->files_count) }}</span>
                                <form method="POST" action="{{ route('admin.finance.update', $l) }}" x-data @change="$el.requestSubmit()">
                                    @csrf @method('PATCH')
                                    <label class="sr-only" for="fs-{{ $l->id }}">Stage for {{ $l->name }}</label>
                                    <select id="fs-{{ $l->id }}" name="finance_status" class="a-input !min-h-9 !py-1 text-xs">@foreach (\App\Enums\FinanceStatus::cases() as $s)<option value="{{ $s->value }}" @selected($l->finance_status === $s)>{{ $s->label() }}</option>@endforeach</select>
                                    <noscript><button class="a-btn a-btn-ghost !min-h-9 text-xs">Move</button></noscript>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-line p-4 text-sm text-ink-3">None</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.admin>
