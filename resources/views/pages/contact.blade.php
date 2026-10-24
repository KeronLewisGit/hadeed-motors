@php
    $topic = old('topic', in_array(request('topic'), ['sales', 'callback', 'service', 'other'], true) ? request('topic') : 'sales');
    $address = hadeed('address');
@endphp
<x-layouts.site title="Contact and opening hours" :description="'Hadeed Motors, Hadeed Motors Complex, Old Parham Road, St John\'s, Antigua. '.format_phone(hadeed('phone')).'. '.\App\Support\Hours::summary().'.'">
    <x-page-header title="Contact us" lead="Call, WhatsApp, or send a message and we'll call you back during opening hours." code="HM" label="Contact" photo="stjohns-harbour" />

    <div class="wrap mt-12 grid gap-12 md:mt-16 lg:grid-cols-[1fr_1.3fr] lg:gap-16">
        <div class="space-y-8">
            <section aria-labelledby="dir-title">
                <h2 id="dir-title" class="display-3">Directory</h2>
                <x-spec class="mt-5" :rows="[
                    'Sales' => format_phone(hadeed('phone')),
                    'Service centre' => format_phone(hadeed('phone')),
                    'Parts and accessories' => format_phone(hadeed('phone')),
                    'Finance (FDC)' => format_phone(hadeed('finance.phone')),
                    'Email' => hadeed('email'),
                ]" />
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ tel_href(hadeed('phone')) }}" class="btn btn-primary"><x-icon name="phone" class="h-4 w-4" />Call {{ format_phone(hadeed('phone')) }}</a>
                    <a href="{{ wa_href(null, 'Hello Hadeed Motors') }}" class="btn btn-outline" target="_blank" rel="noopener"><x-icon name="whatsapp" class="h-4 w-4" />WhatsApp</a>
                </div>
            </section>
            <section aria-labelledby="hours-title">
                <h2 id="hours-title" class="display-3">Opening hours</h2>
                <x-spec class="mt-5" :rows="\App\Support\Hours::rows()" />
            </section>
            <section aria-labelledby="addr-title">
                <h2 id="addr-title" class="display-3">Find us</h2>
                <address class="mt-4 not-italic text-ink-2">{{ $address['building'] }}<br>{{ $address['street'] }}, {{ $address['area'] }}<br>{{ $address['city'] }}, {{ $address['country'] }}</address>
                <a href="{{ hadeed('map_url') }}" class="btn btn-outline mt-5" target="_blank" rel="noopener"><x-icon name="map-pin" class="h-4 w-4" />Open in Google Maps</a>
            </section>
        </div>

        <section aria-labelledby="msg-title">
            <form method="POST" action="{{ route('contact.store') }}" class="panel space-y-8 p-6 md:p-9" x-data="leadForm" @submit="submit">
                @csrf
                <h2 id="msg-title" class="display-3">Send a message</h2>
                <x-form-errors />
                <fieldset>
                    <legend class="label">What's it about?</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (['sales' => 'Buying a car', 'callback' => 'Please call me back', 'service' => 'Service or parts', 'other' => 'Something else'] as $v => $t)
                            <label class="choice"><input type="radio" name="topic" value="{{ $v }}" required @checked($topic === $v)>{{ $t }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <x-select name="interest" label="Vehicle" :options="collect($interests)->mapWithKeys(fn ($l, $k) => ['m:'.$k => $l])->all()" :optional="true" placeholder="None in particular" />
                <x-field name="message" label="Message" type="textarea" :required="true" rows="4" maxlength="3000" />
                <x-contact-fields :step="2" title="How to reach you" />
                <x-submit label="Send message" />
            </form>
        </section>
    </div>
</x-layouts.site>
