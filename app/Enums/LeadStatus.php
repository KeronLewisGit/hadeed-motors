<?php

namespace App\Enums;

/** The sales pipeline every lead moves through. Colour is never the only signal: each has an icon and text. */
enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Appointment = 'appointment';
    case Negotiating = 'negotiating';
    case Sold = 'sold';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Appointment => 'Appointment',
            self::Negotiating => 'Negotiating',
            self::Sold => 'Sold / done',
            self::Lost => 'Lost',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'attention',
            self::Sold => 'good',
            self::Lost => 'muted',
            default => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'dot',
            self::Contacted => 'phone',
            self::Appointment => 'calendar',
            self::Negotiating => 'swap',
            self::Sold => 'check-circle',
            self::Lost => 'x',
        };
    }
}
