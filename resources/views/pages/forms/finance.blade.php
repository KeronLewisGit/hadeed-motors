@php
    $fdc = hadeed('finance');
@endphp
<x-layouts.site title="Finance pre-qualification" description="Pre-qualify for car finance with Finance & Development Co. at Hadeed Motors. No bank numbers, private document upload." :mobile-bar="false">
    <x-page-header title="Get pre-qualified" lead="About five minutes. This isn't a credit application: it tells FDC enough to call you with options and a list of what to bring." code="FA" label="Finance" photo="model-fronx-interior" position="50% 60%" />

    <div class="wrap mt-10 grid gap-12 md:mt-14 lg:grid-cols-[1.6fr_1fr]">
        <form method="POST" action="{{ route('finance.store') }}" enctype="multipart/form-data" class="space-y-12" x-data="leadForm" @submit="submit" autocomplete="on">
            @csrf
            <x-form-errors />
            <x-contact-fields :step="1" title="About you" />
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">2</span>Residency and work</legend>
                <x-select name="residency" label="Residency" :options="$fdc['residency']" :required="true" />
                <fieldset>
                    <legend class="label">Employment</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($fdc['employment'] as $v => $t)
                            <label class="choice"><input type="radio" name="employment" value="{{ $v }}" required @checked(old('employment') === $v)>{{ $t }}</label>
                        @endforeach
                    </div>
                    <x-error for="employment" />
                </fieldset>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="employer" label="Employer or business name" maxlength="120" autocomplete="organization" hint="Required if you're employed." />
                    <x-field name="job_title" label="Job title" :optional="true" maxlength="120" autocomplete="organization-title" />
                </div>
                <x-select name="years_employed" label="Time in this job or business" :options="$fdc['years_employed']" :required="true" />
            </fieldset>
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">3</span>Income and deposit</legend>
                <fieldset>
                    <legend class="label">Monthly income before deductions</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($fdc['income_bands'] as $v => $t)
                            <label class="choice"><input type="radio" name="income_band" value="{{ $v }}" required @checked(old('income_band') === $v)>{{ $t }}</label>
                        @endforeach
                    </div>
                    <x-error for="income_band" />
                </fieldset>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-field name="deposit" label="Deposit you can put down (EC$)" type="number" :required="true" min="0" step="100" inputmode="numeric" hint="Your trade-in can count towards it." />
                    <x-select name="term" label="Preferred term" :options="collect($fdc['terms'])->mapWithKeys(fn ($t) => [$t => $t.' months'])->all()" :optional="true" placeholder="Not sure" />
                </div>
                <x-select name="interest" label="Car you're interested in" :options="$interests" :required="true" :value="$selected" placeholder="Choose…" />
            </fieldset>
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">4</span>Documents <span class="text-base font-normal text-ink-3">(optional, speeds things up)</span></legend>
                <x-field name="id_document" label="Photo ID" type="file" accept=".pdf,image/*" :optional="true" hint="Passport or driving licence. PDF, JPG or PNG up to 8 MB." />
                <x-field name="payslips[]" label="Recent pay slips" type="file" accept=".pdf,image/*" multiple :optional="true" hint="Up to 3 files." />
                <x-error for="payslips" /><x-error for="payslips.0" />
                <p class="flex gap-3 rounded-[12px] bg-surface-2 p-4 text-[0.9375rem] text-ink-2"><x-icon name="lock" class="mt-0.5 h-5 w-5 flex-none text-accent-text" />Documents are stored privately, never on the public website, and only FDC's finance staff can open them. We never ask for bank account or card numbers.</p>
                <x-field name="message" label="Anything FDC should know?" type="textarea" :optional="true" rows="3" maxlength="1500" />
            </fieldset>
            <div class="space-y-3 border-t border-line pt-8">
                <label class="choice !items-start"><input type="checkbox" name="consent" value="1" required class="mt-1" @checked(old('consent'))>
                    <span>I agree that Hadeed Motors may share these details and documents with {{ $fdc['name'] }} so it can assess my pre-qualification and contact me. I understand this is not a loan offer. <a href="{{ route('privacy') }}" class="text-link" target="_blank">Privacy notice</a></span></label>
                <x-error for="consent" />
            </div>
            <x-submit label="Send to FDC" />
        </form>
        <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">What happens next</h2>
                <ol class="mt-3 space-y-3 text-ink-2">
                    <li class="flex gap-3"><x-odo value="1" class="flex-none text-sm" />You get a reference number by email.</li>
                    <li class="flex gap-3"><x-odo value="2" class="flex-none text-sm" />FDC reviews it, usually within one working day.</li>
                    <li class="flex gap-3"><x-odo value="3" class="flex-none text-sm" />They call with options and what to bring upstairs.</li>
                </ol>
            </div>
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">FDC loans team</h2>
                <a href="{{ tel_href($fdc['phone']) }}" class="font-display mt-2 inline-flex min-h-11 items-center gap-2 text-xl font-bold"><x-icon name="phone" class="h-5 w-5" />{{ format_phone($fdc['phone']) }}</a>
                <p class="mt-1 text-sm text-ink-3">{{ $fdc['hours'] }}</p>
            </div>
        </aside>
    </div>
</x-layouts.site>
