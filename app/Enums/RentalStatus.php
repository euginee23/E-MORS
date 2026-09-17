<?php

namespace App\Enums;

enum RentalStatus: string
{
    case Unassigned = 'unassigned';
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Unassigned => 'Unassigned',
            self::Active => 'Active',
            self::Expiring => 'Expiring Soon',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unassigned => 'zinc',
            self::Active => 'lime',
            self::Expiring => 'yellow',
            self::Expired => 'red',
        };
    }

    /**
     * Ranking used when rolling several stalls up to a single vendor-level status.
     * Higher wins, so the most urgent state is what the admin sees.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Unassigned => 0,
            self::Active => 1,
            self::Expiring => 2,
            self::Expired => 3,
        };
    }
}
