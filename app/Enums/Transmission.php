<?php

namespace App\Enums;

enum Transmission: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
