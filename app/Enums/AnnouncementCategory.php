<?php

namespace App\Enums;

enum AnnouncementCategory: string
{
    case General = 'general';
    case Maintenance = 'maintenance';
    case Policy = 'policy';
    case Safety = 'safety';
    case Holiday = 'holiday';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Maintenance => 'Maintenance',
            self::Policy => 'Policy Update',
            self::Safety => 'Safety',
            self::Holiday => 'Holiday',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::General => 'zinc',
            self::Maintenance => 'yellow',
            self::Policy => 'blue',
            self::Safety => 'red',
            self::Holiday => 'purple',
        };
    }
}
