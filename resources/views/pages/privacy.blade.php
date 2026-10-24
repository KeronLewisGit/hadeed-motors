<x-layouts.site title="Privacy notice" description="How Hadeed Motors handles the details you send through this website.">
    <x-page-header title="Privacy notice" lead="What we collect through this website, why, who sees it and how long we keep it." code="PN" label="Privacy" />
    <section class="wrap mt-12 md:mt-16">
        <div class="prose-copy measure text-ink-2">
            <p><strong class="text-ink">Draft for review by Hadeed Motors' legal adviser before launch.</strong> Last updated {{ now()->format('j F Y') }}.</p>
            <h2>Who we are</h2>
            <p>{{ hadeed('name') }}, {{ hadeed('address.building') }}, {{ hadeed('address.street') }}, {{ hadeed('address.city') }}, Antigua and Barbuda. Phone {{ format_phone(hadeed('phone')) }}, email <a href="mailto:{{ hadeed('email') }}">{{ hadeed('email') }}</a>.</p>
            <h2>What we collect</h2>
            <ul>
                <li>Your name, phone number, email address and how you prefer to be contacted, on every form.</li>
                <li>Test drives and service bookings: the vehicle, day and time, your licence plate and odometer reading.</li>
                <li>Parts requests and trade-ins: your VIN or vehicle details and any photos you upload.</li>
                <li>Finance pre-qualification: residency, employment, an income band, your deposit, the car you are interested in and, if you choose, a photo ID and pay slips.</li>
            </ul>
            <p>We never ask for bank account numbers, card numbers or PINs through this website.</p>
            <h2>Why we use it</h2>
            <p>To answer your request: confirm a booking, call you about a car, a part or a valuation, and (with your consent) pass a finance pre-qualification to Finance &amp; Development Co. Ltd. so it can assess it. We don't sell your details or use them for unrelated marketing.</p>
            <h2>Who can see it</h2>
            <p>Hadeed Motors staff who handle your type of request. Finance applications can only be opened by finance staff; uploaded ID and pay slips are stored privately, outside the public website, and only the finance role can open them. Each time a document is opened it is recorded.</p>
            <h2>How long we keep it</h2>
            <p>Enquiries and bookings for up to two years; finance pre-qualifications that don't proceed for up to twelve months, after which documents are deleted. You can ask us to delete your details sooner.</p>
            <h2>Your choices</h2>
            <p>You can ask for a copy of the details we hold about you, ask us to correct or delete them, or withdraw your consent for FDC to contact you. Call or email us using the details above.</p>
            <h2>Cookies</h2>
            <p>This site uses only the cookies needed to send forms securely and to keep staff signed in. There are no advertising or tracking cookies. Your light or dark theme choice is kept in your browser.</p>
        </div>
    </section>
</x-layouts.site>
