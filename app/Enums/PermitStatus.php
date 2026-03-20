<?php

namespace App\Enums;

enum PermitStatus: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Pending => 'Pending',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'lime',
            self::Pending => 'yellow',
            self::Expired => 'red',
        };
    }
}
