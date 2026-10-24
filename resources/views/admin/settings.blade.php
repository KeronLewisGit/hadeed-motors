<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl space-y-6">
        @csrf @method('PUT')
        <section class="a-card space-y-3 p-5">
            <h2 class="font-semibold">Loan estimator</h2>
            <p class="text-sm text-ink-2">The indicative annual rate FDC is happy to publish. While this is blank the estimator stays hidden on the website and customers are asked to pre-qualify instead.</p>
            <div class="max-w-xs"><label for="rate" class="mb-1 block text-sm font-semibold">Indicative rate (% a year)</label>
                <input id="rate" name="finance_indicative_rate" type="number" step="0.01" min="0.5" max="40" value="{{ old('finance_indicative_rate', $rate) }}" class="a-input w-full" placeholder="Blank = hidden"></div>
            <p class="text-xs text-ink-3">Every estimate on the site is labelled as an estimate, not an offer.</p>
        </section>
        <section class="a-card space-y-3 p-5">
            <h2 class="font-semibold">Workshop capacity</h2>
            <p class="text-sm text-ink-2">How many service bookings the website accepts per day. Days that reach it show as fully booked.</p>
            <div class="max-w-xs"><label for="cap" class="mb-1 block text-sm font-semibold">Bookings per day</label>
                <input id="cap" name="service_daily_capacity" type="number" min="1" max="200" value="{{ old('service_daily_capacity', $capacity) }}" class="a-input w-full" required></div>
        </section>
        <button class="a-btn a-btn-primary">Save settings</button>
    </form>
</x-layouts.admin>
