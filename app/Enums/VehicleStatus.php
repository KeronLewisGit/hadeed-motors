<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Available => 'good',
            self::Reserved => 'attention',
            self::Sold => 'muted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Available => 'check-circle',
            self::Reserved => 'clock',
            self::Sold => 'x',
        };
    }
}
