<x-layouts.site title="Request a part" description="Order genuine Nissan and Suzuki parts from Hadeed Motors, St John's. Send your VIN, the part you need and a photo." :mobile-bar="false">
    <x-page-header title="Request a part" lead="Send us your VIN, the part you need and a photo if you have one. The parts counter checks stock and calls you with availability and price." code="PT" label="Parts counter" photo="parts-store" />

    <div class="wrap mt-10 grid gap-12 md:mt-14 lg:grid-cols-[1.6fr_1fr]">
        <form method="POST" action="{{ route('parts.store') }}" enctype="multipart/form-data" class="space-y-12" x-data="leadForm" @submit="submit">
            @csrf
            <x-form-errors />
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">1</span>Your vehicle</legend>
                <x-field name="vin" label="VIN (vehicle identification number)" :required="true" maxlength="20" autocapitalize="characters" spellcheck="false" class="input font-semibold uppercase tracking-[0.12em]" hint="17 letters and numbers. It's on your registration and at the bottom of the windscreen, driver's side." />
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-select name="vehicle_make" label="Make" :options="array_combine(hadeed('makes'), hadeed('makes'))" :required="true" />
                    <x-field name="vehicle_model" label="Model" :required="true" maxlength="60" />
                    <x-field name="vehicle_year" label="Year" type="number" :optional="true" min="1970" :max="now()->year + 1" inputmode="numeric" />
                </div>
            </fieldset>
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">2</span>The part</legend>
                <div class="grid gap-5 sm:grid-cols-[1fr_8rem]">
                    <x-field name="part" label="What do you need?" :required="true" maxlength="160" placeholder="e.g. front brake pads" />
                    <x-field name="quantity" label="How many" type="number" min="1" max="50" value="1" inputmode="numeric" />
                </div>
                <x-field name="photo" label="Photo of the part" type="file" :optional="true" accept="image/*" hint="JPG, PNG or HEIC up to 8 MB. A photo of the old part or its label helps a lot." />
                <x-field name="message" label="Notes" type="textarea" :optional="true" rows="3" maxlength="1500" />
            </fieldset>
            <x-contact-fields :step="3" />
            <div class="flex flex-col gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                <x-submit label="Send parts request" />
            </div>
        </form>
        <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">Where to find your VIN</h2>
                <ul class="mt-3 space-y-2 text-ink-2">
                    <li>Your vehicle registration certificate</li>
                    <li>A plate at the bottom of the windscreen, driver's side</li>
                    <li>A sticker inside the driver's door frame</li>
                </ul>
                <p class="mt-3 text-sm text-ink-3">It never contains the letters I, O or Q.</p>
            </div>
        </aside>
    </div>
</x-layouts.site>
