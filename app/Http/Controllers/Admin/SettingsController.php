<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'rate' => Setting::get('finance_indicative_rate'),
            'capacity' => Setting::serviceCapacity(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'finance_indicative_rate' => ['nullable', 'numeric', 'min:0.5', 'max:40'],
            'service_daily_capacity' => ['required', 'integer', 'min:1', 'max:200'],
        ], [], ['finance_indicative_rate' => 'indicative rate']);

        Setting::put('finance_indicative_rate', isset($data['finance_indicative_rate']) ? plain_number((float) $data['finance_indicative_rate']) : null);
        Setting::put('service_daily_capacity', (string) $data['service_daily_capacity']);

        return back()->with('status', 'Settings saved.');
    }
}
