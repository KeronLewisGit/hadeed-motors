<?php

namespace App\Support;

/**
 * Opening hours, worded for people and for schema.org, all derived from
 * config('hadeed.opening') so the hours are stated in exactly one place.
 */
final class Hours
{
    private const array DAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

    /**
     * Consecutive days with the same hours, Monday first.
     *
     * @return list<array{days: list<string>, open: string|null, close: string|null}>
     */
    public static function groups(): array
    {
        $opening = (array) config('hadeed.opening', []);
        $groups = [];
        foreach (self::DAYS as $iso => $name) {
            $hours = isset($opening[$iso]) ? array_values((array) $opening[$iso]) : [null, null];
            $last = array_key_last($groups);
            if ($last !== null && $groups[$last]['open'] === $hours[0] && $groups[$last]['close'] === $hours[1]) {
                $groups[$last]['days'][] = $name;
            } else {
                $groups[] = ['days' => [$name], 'open' => $hours[0], 'close' => $hours[1]];
            }
        }

        return $groups;
    }

    /** @return array<string, string> "Monday to Thursday" => "8am – 5pm", closed days last */
    public static function rows(): array
    {
        $open = $closed = [];
        foreach (self::groups() as $g) {
            if ($g['open'] === null) {
                array_push($closed, ...$g['days']);
            } else {
                $open[self::span($g['days'])] = self::time((string) $g['open']).' – '.self::time((string) $g['close']);
            }
        }
        if ($closed !== []) {
            $open[implode(', ', $closed).' and public holidays'] = 'Closed';
        }

        return $open;
    }

    /** "Monday to Thursday 8am to 5pm, Friday 8am to 4pm", or the short "Mon–Thu 8am–5pm, Fri 8am–4pm". */
    public static function summary(bool $short = false): string
    {
        $parts = [];
        foreach (self::groups() as $g) {
            if ($g['open'] === null) {
                continue;
            }
            $parts[] = $short
                ? self::span($g['days'], true).' '.self::time((string) $g['open']).'–'.self::time((string) $g['close'])
                : self::span($g['days']).' '.self::time((string) $g['open']).' to '.self::time((string) $g['close']);
        }

        return implode(', ', $parts);
    }

    /** @return list<array<string, mixed>> openingHoursSpecification entries */
    public static function schema(): array
    {
        $out = [];
        foreach (self::groups() as $g) {
            if ($g['open'] !== null) {
                $out[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => count($g['days']) === 1 ? $g['days'][0] : $g['days'], 'opens' => $g['open'], 'closes' => $g['close']];
            }
        }

        return $out;
    }

    /** 08:00 → 8am, 17:30 → 5:30pm */
    public static function time(string $hhmm): string
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm) + [1 => '0']);

        return ($h % 12 ?: 12).($m ? sprintf(':%02d', $m) : '').($h < 12 ? 'am' : 'pm');
    }

    /** @param list<string> $days */
    private static function span(array $days, bool $short = false): string
    {
        $f = fn (string $d) => $short ? substr($d, 0, 3) : $d;
        if (count($days) === 1) {
            return $f($days[0]);
        }

        return $f($days[0]).($short ? '–' : ' to ').$f($days[count($days) - 1]);
    }
}
