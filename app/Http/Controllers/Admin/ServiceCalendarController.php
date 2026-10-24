<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Month grid of workshop bookings against the daily capacity, plus the day's job list. */
class ServiceCalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $month = preg_match('/^\d{4}-\d{2}$/', input_string($request->query('month'))) ? Carbon::createFromFormat('Y-m-d', input_string($request->query('month')).'-01')->startOfDay() : today()->startOfMonth();
        $day = preg_match('/^\d{4}-\d{2}-\d{2}$/', input_string($request->query('day'))) ? Carbon::parse(input_string($request->query('day'))) : today();

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $counts = Lead::holdingServiceSlot()->whereBetween('appointment_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->pluck('appointment_date')->countBy(fn (Carbon $d) => $d->toDateString());

        $weeks = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $weeks[intdiv((int) $gridStart->diffInDays($d), 7)][] = [
                'date' => $d->copy(),
                'count' => (int) ($counts[$d->toDateString()] ?? 0),
                'open' => config('hadeed.opening.'.$d->dayOfWeekIso) !== null && ! in_array($d->toDateString(), (array) config('hadeed.closed_dates'), true),
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
}
