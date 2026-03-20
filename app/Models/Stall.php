<?php

namespace App\Models;

use App\Enums\StallStatus;
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
    ];

    protected function casts(): array
    {
        return [
            'status' => StallStatus::class,
            'monthly_rate' => 'decimal:2',
        ];
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
