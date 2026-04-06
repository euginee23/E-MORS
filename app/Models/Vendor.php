<?php

namespace App\Models;

use App\Enums\PermitStatus;
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

    public function stall(): HasOne
    {
        return $this->hasOne(Stall::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
