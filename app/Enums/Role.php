<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Sales = 'sales';
    case Service = 'service';
    case Finance = 'finance';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Sales => 'Sales',
            self::Service => 'Service & parts',
            self::Finance => 'Finance',
        };
    }

    /**
     * Lead types this role works in the inbox.
     *
     * @return list<LeadType>
     */
    public function leadTypes(): array
    {
        return match ($this) {
            self::Admin => LeadType::cases(),
            self::Sales => [LeadType::TestDrive, LeadType::TradeIn, LeadType::Finance, LeadType::Enquiry],
            self::Service => [LeadType::Service, LeadType::Parts, LeadType::Enquiry],
            self::Finance => [LeadType::Finance],
        };
    }
}
