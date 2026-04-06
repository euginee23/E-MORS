<?php

namespace App\Models;

use App\Enums\AnnouncementCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'author_id',
        'title',
        'body',
        'category',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'published_at' => 'datetime',
        ];
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function isReadBy(User $user): bool
    {
        return $this->readers()->where('user_id', $user->id)->exists();
    }
}
