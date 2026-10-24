<?php

namespace App\Enums;

enum Condition: string
{
    case New = 'new';
    case Used = 'used';

    public function label(): string
    {
        return $this === self::New ? 'New' : 'Pre-owned';
    }
}
