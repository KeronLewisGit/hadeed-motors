@props(['code'])
@php
    $copy = [
        '404' => ['That page has moved', 'The link may be from our old website. Everything is still here: new Nissan and Suzuki, pre-owned stock, finance and service.'],
        '403' => ['You can\'t open that', 'Your account doesn\'t have access to this page. Ask an administrator if you need it.'],
        '419' => ['Your session timed out', 'For your security the form expired. Go back, refresh the page and send it again.'],
        '429' => ['Too many tries', 'Please wait a minute and try again, or call us.'],
        '500' => ['Something went wrong on our side', 'Please try again in a minute. If you were sending a form, call us and we will take it by phone.'],
        '503' => ['Back shortly', 'We are updating the website. Please try again in a few minutes.'],
    ][$code] ?? ['Something went wrong', 'Please try again.'];
@endphp
<x-layouts.site :title="$copy[0]" :mobile-bar="false">
    <x-page-header :title="$copy[0]" :lead="$copy[1]" :code="$code" label="Error" />
    <section class="wrap mt-12 flex flex-wrap gap-3 md:mt-16">
        <a href="{{ route('home') }}" class="btn btn-accent">Go to the home page<x-icon name="arrow-right" class="nudge h-4 w-4" /></a>
        @if ($code === '404')
            <a href="{{ route('inventory.index') }}" class="btn btn-outline">Browse pre-owned</a>
        @endif
        <a href="{{ tel_href(hadeed('phone')) }}" class="btn btn-outline"><x-icon name="phone" class="h-4 w-4" />{{ format_phone(hadeed('phone')) }}</a>
    </section>
</x-layouts.site>
