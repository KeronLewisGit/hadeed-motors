@php
    $next = match ($type) {
        \App\Enums\LeadType::TestDrive => 'A sales consultant will call to confirm your test drive.',
        \App\Enums\LeadType::Service => 'Your place is held. The service team will call to confirm your drop-off time.',
        \App\Enums\LeadType::Parts => 'The parts counter will check stock and call you with availability and price.',
        \App\Enums\LeadType::TradeIn => 'A sales consultant will look at your details and call you with a first figure.',
        \App\Enums\LeadType::Finance => 'Finance & Development Co. will review your details and call you, usually within one working day.',
        default => 'We will get back to you during opening hours.',
    };
@endphp
<x-layouts.site title="Thank you" :mobile-bar="false">
    <x-page-header title="Thank you. We have it." :lead="$next" code="OK" :label="$type->label()" />
    <section class="wrap mt-12 md:mt-16">
        <div class="panel max-w-xl border-l-[6px] border-l-accent p-7 md:p-9">
            <p class="kicker text-ink-3">Your reference</p>
            <x-odo :value="$reference" class="mt-3 text-2xl sm:text-3xl" />
            <p class="mt-5 text-ink-2">We've emailed it to you too. Quote it if you call, and we'll find your request straight away.</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ tel_href(hadeed('phone')) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone(hadeed('phone')) }}</a>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline">Browse pre-owned</a>
            <a href="{{ route('home') }}" class="btn btn-primary">Back to the home page</a>
        </div>
    </section>
</x-layouts.site>
