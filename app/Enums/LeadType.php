<?php

namespace App\Enums;

enum LeadType: string
{
    case TestDrive = 'test_drive';
    case Service = 'service';
    case Parts = 'parts';
    case TradeIn = 'trade_in';
    case Finance = 'finance';
    case Enquiry = 'enquiry';

    public function label(): string
    {
        return match ($this) {
            self::TestDrive => 'Test drive',
            self::Service => 'Service booking',
            self::Parts => 'Parts request',
            self::TradeIn => 'Trade-in valuation',
            self::Finance => 'Finance pre-qualification',
            self::Enquiry => 'Enquiry',
        };
    }

    /** Two-letter prefix of the customer reference, e.g. TD-26-00042. */
    public function prefix(): string
    {
        return match ($this) {
            self::TestDrive => 'TD',
            self::Service => 'SV',
            self::Parts => 'PT',
            self::TradeIn => 'TI',
            self::Finance => 'FA',
            self::Enquiry => 'EN',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TestDrive => 'steering',
            self::Service => 'wrench',
            self::Parts => 'cog',
            self::TradeIn => 'swap',
            self::Finance => 'percent',
            self::Enquiry => 'message',
        };
    }

    public function hasAppointment(): bool
    {
        return in_array($this, [self::TestDrive, self::Service], true);
    }
}
