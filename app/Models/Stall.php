<?php

namespace App\Models;

use App\Enums\RentalStatus;
use App\Enums\StallStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Keep stall_assignments in step with vendor_id. Every assign/unassign path
     * goes through save(), so history is recorded here rather than at each call site.
     */
    protected static function booted(): void
    {
        static::saved(function (Stall $stall) {
            if ($stall->wasChanged('vendor_id') || ($stall->wasRecentlyCreated && $stall->vendor_id)) {
                $stall->assignments()->whereNull('end_date')->update(['end_date' => now()->toDateString()]);

                if ($stall->vendor_id) {
                    $stall->assignments()->create([
                        'market_id' => $stall->market_id,
                        'vendor_id' => $stall->vendor_id,
                        'start_date' => $stall->rent_start ?? now()->toDateString(),
                        'rent_expiry' => $stall->rent_expiry,
                        'monthly_rate' => $stall->monthly_rate ?? 0,
                    ]);
                }

                return;
            }

            if ($stall->vendor_id && $stall->wasChanged(['rent_start', 'rent_expiry', 'monthly_rate'])) {
                $changes = [
                    'rent_expiry' => $stall->rent_expiry?->toDateString(),
                    'monthly_rate' => $stall->monthly_rate ?? 0,
                ];

                // A cleared start date leaves the recorded one alone rather than erasing when the tenancy began.
                if ($stall->rent_start) {
                    $changes['start_date'] = $stall->rent_start->toDateString();
                }

                $stall->assignments()->whereNull('end_date')->update($changes);
            }
        });
    }

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

    public function assignments(): HasMany
    {
        return $this->hasMany(StallAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(StallAssignment::class)->whereNull('end_date')->latestOfMany();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
