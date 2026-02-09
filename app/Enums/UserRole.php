<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Collector = 'collector';
    case Vendor = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Collector => 'Collector',
            self::Vendor => 'Vendor',
        };
    }
}
