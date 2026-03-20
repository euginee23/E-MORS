<?php

namespace App\Enums;

enum StallStatus: string
{
    case Occupied = 'occupied';
    case Available = 'available';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Occupied => 'Occupied',
            self::Available => 'Available',
            self::Maintenance => 'Maintenance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Occupied => 'lime',
            self::Available => 'sky',
            self::Maintenance => 'yellow',
        };
    }
}
