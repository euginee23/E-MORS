<?php

namespace App\Enums;

/**
 * Whether a stall's rent for one month has been covered. Derived from the
 * stall's monthly rate against the paid collections dated in that month —
 * there is no stored bill, so this never lives in a column.
 */
enum DueStatus: string
{
    case Paid = 'paid';
    case Partial = 'partial';
    case Unpaid = 'unpaid';

    public function label(): string
    {
        return match ($this) {
            self::Paid => 'Paid',
            self::Partial => 'Partial',
            self::Unpaid => 'Unpaid',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Paid => 'lime',
            self::Partial => 'amber',
            self::Unpaid => 'red',
        };
    }
}
