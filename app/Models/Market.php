<?php

namespace App\Models;

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::Admin);
    }

    /**
     * Markets vendors can apply to: only those with a verified admin,
     * so a pending or duplicate admin signup never collects vendors nobody can see.
     */
    public function scopeOpenForVendors(Builder $query): Builder
    {
        return $query->whereHas('admins', fn (Builder $q) => $q->where('status', AdminStatus::Verified));
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function stalls(): HasMany
    {
        return $this->hasMany(Stall::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
