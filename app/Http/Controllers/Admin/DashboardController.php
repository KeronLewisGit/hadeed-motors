<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinanceStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Enums\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const int CHART_DAYS = 30;

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $mine = fn () => Lead::visibleTo($user);
        $chartStart = today()->subDays(self::CHART_DAYS - 1);

        $overdue = $mine()->overdue();
        $oldest = (clone $overdue)->min('created_at');

        $dates = $mine()->where('created_at', '>=', $chartStart)->pluck('created_at')->countBy(fn (Carbon $d) => $d->toDateString());
        $daily = collect(range(0, self::CHART_DAYS - 1))->map(function (int $i) use ($chartStart, $dates) {
            $day = $chartStart->copy()->addDays($i);

            return ['date' => $day, 'count' => (int) ($dates[$day->toDateString()] ?? 0)];
        });

        // Median minutes from arrival to first contact over the last 30 days.
        $responses = $mine()->whereNotNull('first_contacted_at')->where('created_at', '>=', now()->subDays(30))->get(['created_at', 'first_contacted_at'])
            ->map(fn (Lead $l) => (int) $l->created_at->diffInMinutes($l->first_contacted_at))->sort()->values();
        $median = $responses->isEmpty() ? null : (int) $responses->median();

        $tiles = [
            ['label' => 'New leads, last 7 days', 'value' => $mine()->where('created_at', '>=', now()->subDays(7))->count(),
                'previous' => $mine()->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count(), 'period' => 'vs previous 7 days',
                'href' => route('admin.leads.index')],
            ['label' => 'Median time to first call', 'value' => $median, 'format' => 'minutes', 'note' => 'last 30 days, '.$responses->count().' leads', 'href' => route('admin.leads.index')],
        ];
        if ($user->canWork(LeadType::TestDrive)) {
            $tiles[] = ['label' => 'Test drives, next 7 days', 'value' => $mine()->where('type', LeadType::TestDrive)->where('status', '!=', LeadStatus::Lost)
                ->whereBetween('appointment_date', [today(), today()->addDays(7)])->count(), 'note' => 'booked from the website', 'href' => route('admin.leads.index', ['type' => 'test_drive'])];
            $tiles[] = ['label' => 'Cars available', 'value' => Vehicle::where('status', VehicleStatus::Available)->where('published', true)->count(),
                'note' => Vehicle::where('status', VehicleStatus::Reserved)->count().' reserved', 'href' => route('admin.vehicles.index')];
        }
        if ($user->canWork(LeadType::Service)) {
            $todayBooked = Lead::holdingServiceSlot()->whereDate('appointment_date', today())->count();
            $tiles[] = ['label' => 'Service bookings today', 'value' => $todayBooked, 'note' => 'of '.Setting::serviceCapacity().' places', 'href' => route('admin.service.index')];
        }
        if ($user->canWork(LeadType::Finance)) {
            $tiles[] = ['label' => 'Finance applications to review', 'value' => Lead::where('type', LeadType::Finance)
                ->whereIn('finance_status', [FinanceStatus::Received, FinanceStatus::Reviewing, FinanceStatus::NeedsDocuments])->count(),
                'note' => 'received, reviewing or waiting on documents', 'href' => route('admin.finance.index')];
        }

        return view('admin.dashboard', [
            'overdueCount' => $overdue->count(),
            'oldestOverdue' => $oldest ? Carbon::parse($oldest) : null,
            'newCount' => $mine()->where('status', LeadStatus::New)->count(),
            'tiles' => $tiles,
            'daily' => $daily,
            'dailyMax' => max(1, (int) $daily->max('count')),
            'chartDays' => self::CHART_DAYS,
            'byType' => collect($user->role->leadTypes())->map(fn (LeadType $t) => [
                'type' => $t, 'count' => $mine()->where('type', $t)->where('created_at', '>=', $chartStart)->count(),
            ])->sortByDesc('count')->values(),
            'pipeline' => collect(LeadStatus::cases())->map(fn (LeadStatus $s) => ['status' => $s, 'count' => $mine()->where('status', $s)->count()]),
            'queue' => $mine()->with('vehicle')->where('status', LeadStatus::New)->oldest()->limit(8)->get(),
            'upcoming' => $mine()->with('vehicle')->whereNotNull('appointment_date')->where('status', '!=', LeadStatus::Lost)
                ->whereBetween('appointment_date', [today(), today()->addDays(2)])->orderBy('appointment_date')->orderBy('appointment_time')->limit(8)->get(),
            'notifyMissing' => $user->isAdmin() && Notify::recipients() === [],
            'notifyInvalid' => $user->isAdmin() ? Notify::invalid() : [],
        ]);
    }
}
