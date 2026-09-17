<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\StallStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stall extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'vendor_id',
        'stall_number',
        'section',
        'size',
        'monthly_rate',
        'status',
        'rent_start',
        'rent_expiry',
    ];

    protected function casts(): array
    {
        return [
            'status' => StallStatus::class,
            'monthly_rate' => 'decimal:2',
            'rent_start' => 'date',
            'rent_expiry' => 'date',
        ];
    }

    /**
     * Where this stall's rental term stands — distinct from `status`, which is
     * physical occupancy (available/occupied/maintenance).
     */
    protected function rentalStatus(): Attribute
    {
        return Attribute::get(function (): RentalStatus {
            if (! $this->vendor_id) {
                return RentalStatus::Unassigned;
            }

            if (! $this->rent_expiry) {
                return RentalStatus::Active;
            }

            $today = now()->startOfDay();

            if ($this->rent_expiry->lt($today)) {
                return RentalStatus::Expired;
            }

            return $this->rent_expiry->lte($today->copy()->addDays(30))
                ? RentalStatus::Expiring
                : RentalStatus::Active;
        });
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
