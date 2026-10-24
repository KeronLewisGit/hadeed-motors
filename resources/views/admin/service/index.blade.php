<x-layouts.admin title="Service calendar">
    <x-slot:actions>
        <a href="{{ route('admin.service.index', ['month' => $month->copy()->subMonth()->format('Y-m'), 'day' => $day->toDateString()]) }}" class="a-btn a-btn-ghost" aria-label="Previous month"><x-icon name="chevron-left" class="h-4 w-4" /></a>
        <a href="{{ route('admin.service.index') }}" class="a-btn a-btn-ghost">Today</a>
        <a href="{{ route('admin.service.index', ['month' => $month->copy()->addMonth()->format('Y-m'), 'day' => $day->toDateString()]) }}" class="a-btn a-btn-ghost" aria-label="Next month"><x-icon name="chevron-right" class="h-4 w-4" /></a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <section class="a-card min-w-0 p-3 sm:p-5" aria-labelledby="month-title">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="month-title" class="text-lg font-semibold">{{ $month->format('F Y') }}</h2>
                <p class="text-sm text-ink-3">{{ $capacity }} places a day @if (auth()->user()->isAdmin())· change in <a href="{{ route('admin.settings.edit') }}" class="underline">Settings</a>@endif</p>
            </div>
            <table class="mt-4 w-full table-fixed border-separate border-spacing-0.5 text-sm sm:border-spacing-1">
                <thead><tr>@foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $w)<th class="pb-1 text-left text-xs font-semibold uppercase tracking-wide text-ink-3" scope="col">{{ $w }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($weeks as $week)
                        <tr>
                            @foreach ($week as $cell)
                                @php
                                    $inMonth = $cell['date']->month === $month->month;
                                    $selected = $cell['date']->isSameDay($day);
                                    $full = $cell['count'] >= $capacity;
                                @endphp
                                <td class="align-top">
                                    <a href="{{ route('admin.service.index', ['month' => $month->format('Y-m'), 'day' => $cell['date']->toDateString()]) }}"
                                        @class(['cal-day block overflow-hidden rounded-lg border p-1 hover:bg-surface-2 sm:p-2', 'border-ink ring-1 ring-ink' => $selected, 'border-line' => ! $selected, 'opacity-50' => ! $inMonth, 'bg-surface-2/60' => ! $cell['open']])
                                        aria-label="{{ $cell['date']->format('l j F') }}: {{ $cell['open'] ? $cell['count'].' of '.$capacity.' booked' : 'closed' }}" @if($selected) aria-current="date" @endif>
                                        <span @class(['num font-semibold', 'rounded-full bg-accent px-1.5 text-accent-ink' => $cell['date']->isToday()])>{{ $cell['date']->day }}</span>
                                        @if ($cell['open'])
                                            <span class="mt-2 block text-xs num {{ $full ? 'font-bold text-accent-text' : 'text-ink-3' }}">{{ $cell['count'] }}/{{ $capacity }}<span class="hidden sm:inline">{{ $full ? ' full' : '' }}</span></span>
                                            <span class="cal-meter mt-1 block" data-full="{{ $full ? 'true' : 'false' }}"><span style="width: {{ min(100, $cell['count'] / $capacity * 100) }}%"></span></span>
                                        @else
                                            <span class="mt-2 block text-xs text-ink-3"><span class="sm:hidden" aria-hidden="true">–</span><span class="hidden sm:inline">Closed</span></span>
                                        @endif
                                    </a>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="a-card min-w-0" aria-labelledby="day-title">
            <div class="p-5 pb-3">
                <h2 id="day-title" class="text-lg font-semibold">{{ $day->format('l j F') }}</h2>
                <p class="text-sm text-ink-3 num">{{ $bookings->where('status', '!=', \App\Enums\LeadStatus::Lost)->count() }} of {{ $capacity }} places booked</p>
            </div>
            @forelse ($bookings as $b)
                <a href="{{ route('admin.leads.show', $b) }}" @class(['flex gap-3 border-t border-line px-5 py-3 hover:bg-surface-2', 'opacity-60' => $b->status === \App\Enums\LeadStatus::Lost])>
                    <p class="w-12 flex-none num font-semibold">{{ $b->appointment_time }}</p>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold">{{ $b->detail('registration') }} · {{ trim($b->detail('vehicle_make').' '.$b->detail('vehicle_model')) }}</p>
                        <p class="truncate text-sm text-ink-3">{{ hadeed('service_types.'.$b->detail('service_type'), '') }} · {{ $b->name }}@if($b->detail('waiting')) · waiting @endif</p>
                    </div>
                    <x-status :status="$b->status" />
                </a>
            @empty
                <p class="border-t border-line px-5 py-6 text-ink-3">No bookings on this day.</p>
            @endforelse
        </section>
    </div>
</x-layouts.admin>
