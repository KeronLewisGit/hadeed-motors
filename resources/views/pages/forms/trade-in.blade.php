@php
    $interestOptions = ['New models' => $interests['models'], 'In stock now' => $interests['stock'], 'Other' => ['undecided' => 'Not sure yet']];
@endphp
<x-layouts.site title="Value my trade-in" description="Get a trade-in valuation from Hadeed Motors, St John's. Send photos and a few details about your car." :mobile-bar="false">
    <x-page-header title="Value my trade-in" lead="Send a few details and some photos. A sales consultant looks them over and calls you with a first figure; the final number follows a quick inspection at the showroom." code="TI" label="Trade-in" photo="used-swift" />

    <div class="wrap mt-10 grid gap-12 md:mt-14 lg:grid-cols-[1.6fr_1fr]">
        <form method="POST" action="{{ route('trade-in.store') }}" enctype="multipart/form-data" class="space-y-12" x-data="leadForm" @submit="submit">
            @csrf
            <x-form-errors />
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">1</span>Your car</legend>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-select name="vehicle_make" label="Make" :options="array_combine(hadeed('makes'), hadeed('makes'))" :required="true" />
                    <x-field name="vehicle_model" label="Model" :required="true" maxlength="60" />
                    <x-field name="vehicle_year" label="Year" type="number" :required="true" min="1970" :max="now()->year + 1" inputmode="numeric" />
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-field name="mileage" label="Odometer (km)" type="number" :required="true" min="0" inputmode="numeric" />
                    <x-select name="transmission" label="Transmission" :options="['automatic' => 'Automatic', 'manual' => 'Manual']" :optional="true" placeholder="Choose…" />
                    <x-field name="registration" label="Licence plate" :optional="true" maxlength="12" />
                </div>
                <fieldset>
                    <legend class="label">Overall condition</legend>
                    <div class="grid gap-2 sm:grid-cols-4">
                        @foreach (['excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair', 'needs_work' => 'Needs work'] as $v => $t)
                            <label class="choice"><input type="radio" name="condition" value="{{ $v }}" required @checked(old('condition') === $v)>{{ $t }}</label>
                        @endforeach
                    </div>
                    <x-error for="condition" />
                </fieldset>
                <label class="choice"><input type="checkbox" name="owed" value="1" @checked(old('owed'))>There is still money owed on this car</label>
            </fieldset>
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">2</span>Photos</legend>
                <x-field name="photos[]" label="Up to 6 photos" type="file" multiple accept="image/*" :optional="true" hint="Front, back, both sides, the dashboard with the engine running, and any damage. JPG, PNG or HEIC up to 8 MB each." />
                <x-error for="photos" /><x-error for="photos.0" /><x-error for="photos.1" /><x-error for="photos.2" />
                <x-field name="message" label="Anything we should know?" type="textarea" :optional="true" rows="3" maxlength="1500" placeholder="Service history, new tyres, dents, accidents…" />
            </fieldset>
            <fieldset class="space-y-5">
                <legend class="fieldset-title mb-5"><span class="step">3</span>What you'd like next</legend>
                <x-select name="interest" label="Car you're interested in" :options="$interestOptions" :optional="true" placeholder="Just the valuation for now" :value="request('interest')" />
            </fieldset>
            <x-contact-fields :step="4" />
            <div class="flex flex-col gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                <x-submit label="Send for valuation" />
            </div>
        </form>
        <aside class="space-y-6 lg:sticky lg:top-24 lg:h-fit">
            <div class="panel p-6">
                <h2 class="text-lg font-bold font-sans">How the valuation works</h2>
                <ol class="mt-3 space-y-3 text-ink-2">
                    <li class="flex gap-3"><x-odo value="1" class="flex-none text-sm" />We look at your photos and details.</li>
                    <li class="flex gap-3"><x-odo value="2" class="flex-none text-sm" />A sales consultant calls with a first figure.</li>
                    <li class="flex gap-3"><x-odo value="3" class="flex-none text-sm" />Bring the car in; the final figure follows a short inspection.</li>
                </ol>
            </div>
        </aside>
    </div>
</x-layouts.site>
