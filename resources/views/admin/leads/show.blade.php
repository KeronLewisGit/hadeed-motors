@php
    /** @var \App\Models\Lead $lead */
    $d = $lead->details ?? [];
    $isFinance = $lead->type === \App\Enums\LeadType::Finance;
    $labels = [
        'registration' => 'Licence plate', 'vehicle_make' => 'Make', 'vehicle_model' => 'Model', 'vehicle_year' => 'Year',
        'mileage' => 'Odometer', 'service_type' => 'Service', 'waiting' => 'Waiting in showroom', 'vin' => 'VIN', 'part' => 'Part',
        'quantity' => 'Quantity', 'transmission' => 'Transmission', 'condition' => 'Condition', 'owed' => 'Money owed',
        'topic' => 'Topic', 'licence_confirmed' => 'Driving licence confirmed',
    ];
    $format = function (string $k, mixed $v): string {
        return match (true) {
            is_bool($v) => $v ? 'Yes' : 'No',
            $k === 'mileage' => number_format((int) $v).' km',
            $k === 'service_type' => (string) hadeed('service_types.'.$v, $v),
            $k === 'condition' => ucfirst(str_replace('_', ' ', (string) $v)),
            default => (string) $v,
        };
    };
    $general = collect($d)->only(array_keys($labels))->filter(fn ($v) => $v !== null && $v !== '')->mapWithKeys(fn ($v, $k) => [$labels[$k] => $format($k, $v)])->all();
    $waText = 'Hello '.strtok($lead->name, ' ').', this is Hadeed Motors about your '.strtolower($lead->type->label()).' ('.$lead->reference.').';
@endphp
<x-layouts.admin :title="$lead->name">
    <x-slot:actions>
        @if ($neighbours['prev'])<a href="{{ route('admin.leads.show', $neighbours['prev']) }}" class="a-btn a-btn-ghost" aria-label="Previous lead in the inbox"><x-icon name="chevron-left" class="h-4 w-4" /></a>@endif
        @if ($neighbours['next'])<a href="{{ route('admin.leads.show', $neighbours['next']) }}" class="a-btn a-btn-ghost" aria-label="Next lead in the inbox"><x-icon name="chevron-right" class="h-4 w-4" /></a>@endif
        <a href="{{ route('admin.leads.index') }}" class="a-btn a-btn-ghost">Back to inbox</a>
    </x-slot:actions>

    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="chip num" data-tone="neutral"><x-icon :name="$lead->type->icon()" />{{ $lead->type->label() }} · {{ $lead->reference }}</span>
        <x-status :status="$lead->status" />
        @if ($lead->finance_status)<x-chip :tone="$lead->finance_status->tone()" :icon="$lead->finance_status->icon()">FDC: {{ $lead->finance_status->label() }}</x-chip>@endif
        @if ($lead->isOverdue())<span class="chip" data-tone="critical"><x-icon name="clock" />Waiting {{ $lead->created_at->diffForHumans(null, true) }}</span>@endif
        <span class="text-ink-3">Received {{ $lead->created_at->format('D j M Y, H:i') }}</span>
    </div>

    <div class="mt-5 grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="space-y-6">
            <section class="a-card p-5" aria-labelledby="contact-title">
                <h2 id="contact-title" class="font-semibold">Contact</h2>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-lg font-semibold num">{{ format_phone($lead->phone) }}</p>
                        <p class="text-ink-2">{{ $lead->email }}</p>
                        <p class="mt-1 text-sm text-ink-3">Prefers {{ ['phone' => 'a phone call', 'whatsapp' => 'WhatsApp', 'email' => 'email'][$lead->contact_pref] ?? $lead->contact_pref }}</p>
                    </div>
                    <div class="flex flex-wrap content-start gap-2">
                        <a href="{{ tel_href($lead->phone) }}" class="a-btn a-btn-primary"><x-icon name="phone" class="h-4 w-4" />Call</a>
                        <a href="{{ wa_href($lead->phone, $waText) }}" class="a-btn a-btn-ghost" target="_blank" rel="noopener"><x-icon name="whatsapp" class="h-4 w-4" />WhatsApp</a>
                        @if ($lead->email)<a href="mailto:{{ $lead->email }}?subject={{ rawurlencode('Hadeed Motors: '.$lead->reference) }}" class="a-btn a-btn-ghost"><x-icon name="mail" class="h-4 w-4" />Email</a>@endif
                    </div>
                </div>
            </section>

            <section class="a-card p-5" aria-labelledby="req-title">
                <h2 id="req-title" class="font-semibold">Request</h2>
                @if ($lead->vehicle)
                    <a href="{{ route('inventory.show', $lead->vehicle) }}" target="_blank" class="mt-3 flex items-center gap-3 rounded-lg border border-line p-3 hover:bg-surface-2">
                        @if ($lead->vehicle->photoUrl())<img src="{{ $lead->vehicle->photoUrl() }}" alt="" class="h-14 w-20 rounded object-cover">@endif
                        <div><p class="font-semibold">{{ $lead->vehicle->title }}</p><p class="text-sm text-ink-3">{{ $lead->vehicle->stock_no }} · {{ $lead->vehicle->priceLabel() }} · {{ $lead->vehicle->status->label() }}</p></div>
                    </a>
                @elseif ($lead->model_key)
                    <p class="mt-3"><span class="text-ink-3">Interested in:</span> <strong>New {{ \App\Support\Lineup::label($lead->model_key) }}</strong></p>
                @endif
                @if ($lead->appointment_date)
                    <p class="mt-3"><span class="text-ink-3">Requested:</span> <strong>{{ $lead->appointment_date->format('l j F') }}{{ $lead->appointment_time ? ' at '.$lead->appointment_time : '' }}</strong></p>
                @endif
                @if ($general)<x-spec class="mt-3 max-w-xl" :rows="$general" />@endif
                @if ($lead->message && (! $isFinance || $canSeeFinance))
                    <blockquote class="mt-4 rounded-lg bg-surface-2 p-4 whitespace-pre-line">{{ $lead->message }}</blockquote>
                @endif
                @if ($lead->source_url)<p class="mt-3 truncate text-xs text-ink-3">From {{ $lead->source_url }}</p>@endif
            </section>

            @if ($isFinance)
                <section class="a-card p-5" aria-labelledby="fin-title">
                    <h2 id="fin-title" class="flex items-center gap-2 font-semibold"><x-icon name="lock" class="h-4 w-4 text-ink-3" />Finance details</h2>
                    @if ($canSeeFinance)
                        <x-spec class="mt-3 max-w-xl" :rows="[
                            'Residency' => hadeed('finance.residency.'.($d['residency'] ?? '')),
                            'Employment' => hadeed('finance.employment.'.($d['employment'] ?? '')),
                            'Employer' => $d['employer'] ?? null,
                            'Job title' => $d['job_title'] ?? null,
                            'Time in job' => hadeed('finance.years_employed.'.($d['years_employed'] ?? '')),
                            'Monthly income' => hadeed('finance.income_bands.'.($d['income_band'] ?? '')),
                            'Deposit' => isset($d['deposit']) ? money($d['deposit']) : null,
                            'Preferred term' => isset($d['term']) ? $d['term'].' months' : 'Not sure',
                            'Consent to share with FDC' => $lead->consented_at?->format('j M Y H:i'),
                        ]" />
                    @else
                        <p class="mt-2 text-ink-2">Employment, income and documents are only visible to the finance team.</p>
                    @endif
                </section>
            @endif

            @if ($lead->files->isNotEmpty())
                <section class="a-card p-5" aria-labelledby="files-title">
                    <h2 id="files-title" class="font-semibold">Uploads</h2>
                    <ul class="mt-3 divide-y divide-line">
                        @foreach ($lead->files as $f)
                            <li class="flex flex-wrap items-center gap-3 py-3">
                                <x-icon :name="$f->isImage() ? 'image' : 'file'" class="h-5 w-5 text-ink-3" />
                                <div class="min-w-0 flex-1"><p class="font-semibold">{{ $f->label() }}</p><p class="truncate text-sm text-ink-3">{{ $f->original_name }} · {{ number_format($f->size / 1024) }} KB</p></div>
                                @can('openFile', [$lead, $f])
                                    <a href="{{ route('admin.leads.file', [$lead, $f]) }}" class="a-btn a-btn-ghost" target="_blank" rel="noopener">{{ $f->isImage() ? 'View' : 'Open' }}</a>
                                @else
                                    <span class="chip" data-tone="muted"><x-icon name="lock" />Finance team only</span>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            <section class="a-card p-5" aria-labelledby="manage-title">
                <h2 id="manage-title" class="font-semibold">Manage</h2>
                <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="mt-3 space-y-4">
                    @csrf @method('PATCH')
                    <div><label for="status" class="mb-1 block text-sm font-semibold">Status</label>
                        <select id="status" name="status" class="a-input w-full">@foreach (\App\Enums\LeadStatus::cases() as $s)<option value="{{ $s->value }}" @selected($lead->status === $s)>{{ $s->label() }}</option>@endforeach</select></div>
                    <div><label for="assigned_to" class="mb-1 block text-sm font-semibold">Assigned to</label>
                        <select id="assigned_to" name="assigned_to" class="a-input w-full"><option value="">Nobody</option>@foreach ($staff as $u)<option value="{{ $u->id }}" @selected($lead->assigned_to === $u->id)>{{ $u->name }} ({{ $u->role->label() }})</option>@endforeach</select></div>
                    @if ($lead->type->hasAppointment())
                        <div class="grid grid-cols-[1fr_7rem] gap-2">
                            <div><label for="appointment_date" class="mb-1 block text-sm font-semibold">Appointment</label><input id="appointment_date" type="date" name="appointment_date" value="{{ $lead->appointment_date?->toDateString() }}" class="a-input w-full"></div>
                            <div><label for="appointment_time" class="mb-1 block text-sm font-semibold">Time</label><input id="appointment_time" type="time" name="appointment_time" value="{{ $lead->appointment_time }}" class="a-input w-full"></div>
                        </div>
                    @endif
                    <button class="a-btn a-btn-primary w-full">Save</button>
                </form>
                @if ($isFinance && auth()->user()->hasRole(\App\Enums\Role::Admin, \App\Enums\Role::Finance))
                    <form method="POST" action="{{ route('admin.finance.update', $lead) }}" class="mt-5 border-t border-line pt-5">
                        @csrf @method('PATCH')
                        <label for="finance_status" class="mb-1 block text-sm font-semibold">FDC decision stage</label>
                        <div class="flex gap-2">
                            <select id="finance_status" name="finance_status" class="a-input flex-1">@foreach (\App\Enums\FinanceStatus::cases() as $s)<option value="{{ $s->value }}" @selected($lead->finance_status === $s)>{{ $s->label() }}</option>@endforeach</select>
                            <button class="a-btn a-btn-ghost">Update</button>
                        </div>
                    </form>
                @endif
            </section>

            <section id="timeline" class="a-card p-5" aria-labelledby="tl-title">
                <h2 id="tl-title" class="font-semibold">Timeline</h2>
                <form method="POST" action="{{ route('admin.leads.note', $lead) }}" class="mt-3 space-y-2">
                    @csrf
                    <label for="body" class="sr-only">Add a note</label>
                    <textarea id="body" name="body" rows="3" class="a-input w-full" placeholder="Called, left a message, booked for Friday…" required maxlength="3000"></textarea>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label class="flex min-h-11 items-center gap-2 text-sm"><input type="checkbox" name="contacted" value="1" class="h-4 w-4" @checked($lead->status === \App\Enums\LeadStatus::New)>I spoke to the customer</label>
                        <button class="a-btn a-btn-primary">Add note</button>
                    </div>
                </form>
                <ol class="mt-5 space-y-4 border-l-2 border-line pl-5">
                    @foreach ($lead->notes as $n)
                        <li class="relative">
                            <span @class(['absolute -left-[1.6rem] top-1.5 h-2.5 w-2.5 rounded-full', 'bg-accent' => $n->kind === 'note', 'bg-[color:var(--series-1)]' => in_array($n->kind, ['status', 'assign', 'finance'], true), 'bg-line-strong' => $n->kind === 'system']) aria-hidden="true"></span>
                            <p @class(['whitespace-pre-line', 'text-ink-2' => $n->kind !== 'note'])>{{ $n->body }}</p>
                            <p class="mt-0.5 text-xs text-ink-3">{{ $n->user->name ?? 'Website' }} · {{ $n->created_at->format('j M, H:i') }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</x-layouts.admin>
