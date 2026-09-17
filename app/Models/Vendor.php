<?php

namespace App\Models;

use App\Enums\PermitStatus;
use App\Enums\RentalStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'market_id',
        'user_id',
        'business_name',
        'contact_name',
        'contact_phone',
        'address',
        'product_type',
        'permit_number',
        'permit_status',
        'permit_expiry',
    ];

    protected function casts(): array
    {
        return [
            'permit_status' => PermitStatus::class,
            'permit_expiry' => 'date',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The vendor's primary stall. Kept for the collector and collection screens,
     * which bill against a single stall; use stalls() when every rented space matters.
     */
    public function stall(): HasOne
    {
        return $this->hasOne(Stall::class);
    }

    public function stalls(): HasMany
    {
        return $this->hasMany(Stall::class);
    }

    /**
     * The most urgent rental status across every stall this vendor rents.
     */
    public function stallRentalStatus(): RentalStatus
    {
        $statuses = $this->stalls->map(fn (Stall $stall) => $stall->rental_status);

        if ($statuses->isEmpty()) {
            return RentalStatus::Unassigned;
        }

        return $statuses->sortByDesc(fn (RentalStatus $status) => $status->severity())->first();
    }

    public function soonestRentExpiry(): ?CarbonInterface
    {
        return $this->stalls
            ->pluck('rent_expiry')
            ->filter()
            ->sort()
            ->first();
    }

    public function totalMonthlyRent(): float
    {
        return (float) $this->stalls->sum('monthly_rate');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(VendorNotice::class);
    }
}
