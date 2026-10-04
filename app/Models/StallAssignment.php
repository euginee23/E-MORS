<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One vendor's tenancy of one stall. Rows are written by Stall's saved hook,
 * so every assign/unassign path records history without remembering to.
 */
class StallAssignment extends Model
{
    protected $fillable = [
        'market_id',
        'stall_id',
        'vendor_id',
        'start_date',
        'end_date',
        'rent_expiry',
        'monthly_rate',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_expiry' => 'date',
            'monthly_rate' => 'decimal:2',
        ];
    }

    public function stall(): BelongsTo
    {
        return $this->belongsTo(Stall::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }

    public function isCurrent(): bool
    {
        return $this->end_date === null;
    }

    public function durationInMonths(): int
    {
        if (! $this->start_date) {
            return 0;
        }

        $end = $this->end_date ?? now();

        return max(1, (int) round($this->start_date->diffInMonths($end)));
    }
}
