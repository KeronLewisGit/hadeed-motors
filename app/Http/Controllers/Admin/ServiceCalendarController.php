<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Support\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Month grid of workshop bookings against the daily capacity, plus the day's job list. */
class ServiceCalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $day = self::date(input_string($request->query('day'))) ?? today();
        $month = (self::date(input_string($request->query('month')).'-01') ?? $day)->copy()->startOfMonth();

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $counts = Lead::holdingServiceSlot()->whereBetween('appointment_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->pluck('appointment_date')->countBy(fn (Carbon $d) => $d->toDateString());

        $weeks = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $weeks[intdiv((int) $gridStart->diffInDays($d), 7)][] = [
                'date' => $d->copy(),
                'count' => (int) ($counts[$d->toDateString()] ?? 0),
                'open' => Booking::isOpenOn($d),
            ];
        }

        return view('admin.service.index', [
            'month' => $month,
            'weeks' => $weeks,
            'capacity' => Setting::serviceCapacity(),
            'day' => $day,
            'bookings' => Lead::where('type', LeadType::Service)->whereDate('appointment_date', $day)->with('assignee')
                ->orderBy('appointment_time')->get(),
        ]);
    }

    /** A real Y-m-d date, or null: 2026-13-45 and 2026-02-30 are refused rather than rolled over. */
    private static function date(string $value): ?Carbon
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }

        return Carbon::createFromFormat('!Y-m-d', $value) ?: null;
    }
}
