<?php

namespace App\Support;

use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Support\Carbon;

/**
 * Bookable days and times. Only weekdays the showroom is open (config hadeed.opening),
 * never public holidays (hadeed.closed_dates), from tomorrow up to days_ahead.
 */
final class Booking
{
    /** @return list<Carbon> */
    public static function days(): array
    {
        $out = [];
        $closed = (array) config('hadeed.closed_dates', []);
        $opening = (array) config('hadeed.opening', []);
        $day = today()->addDay();
        $end = today()->addDays((int) config('hadeed.booking.days_ahead', 30));
        while ($day->lte($end)) {
            if (isset($opening[$day->dayOfWeekIso]) && ! in_array($day->toDateString(), $closed, true)) {
                $out[] = $day->copy();
            }
            $day->addDay();
        }

        return $out;
    }

    public static function isBookableDay(string $date): bool
    {
        foreach (self::days() as $d) {
            if ($d->toDateString() === $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test-drive start times on a day: each slot needs an hour before closing.
     *
     * @return list<string>
     */
    public static function testDriveTimes(Carbon $day): array
    {
        $hours = config('hadeed.opening.'.$day->dayOfWeekIso);
        if (! $hours) {
            return [];
        }

        return array_values(array_filter((array) config('hadeed.booking.test_drive_slots'), fn (string $t) => $t >= $hours[0] && self::addHour($t) <= $hours[1]));
    }

    public static function testDriveTaken(string $date, string $time): int
    {
        return Lead::where('type', LeadType::TestDrive)->where('status', '!=', LeadStatus::Lost)
            ->whereDate('appointment_date', $date)->where('appointment_time', $time)->count();
    }

    public static function testDriveFull(string $date, string $time): bool
    {
        return self::testDriveTaken($date, $time) >= (int) config('hadeed.booking.test_drives_per_slot', 2);
    }

    public static function serviceBooked(string $date): int
    {
        return Lead::holdingServiceSlot()->whereDate('appointment_date', $date)->count();
    }

    public static function serviceRemaining(string $date): int
    {
        return max(0, Setting::serviceCapacity() - self::serviceBooked($date));
    }

    /**
     * Service days with the places left, for the booking form.
     *
     * @return list<array{date: Carbon, left: int}>
     */
    public static function serviceDays(): array
    {
        $days = self::days();
        if ($days === []) {
            return [];
        }
        $capacity = Setting::serviceCapacity();
        $booked = Lead::holdingServiceSlot()
            ->whereBetween('appointment_date', [$days[0]->toDateString(), end($days)->toDateString()])
            ->pluck('appointment_date')->countBy(fn (Carbon $d) => $d->toDateString());

        return array_map(fn (Carbon $d) => ['date' => $d, 'left' => max(0, $capacity - (int) ($booked[$d->toDateString()] ?? 0))], $days);
    }

    private static function addHour(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->addHour()->format('H:i');
    }
}
