<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'vendor_id',
        'collection_id',
        'notice_type',
        'issue_key',
        'issue_date',
        'details',
        'last_sent_at',
        'sent_count',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'details' => 'array',
            'last_sent_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sent_count' => 'integer',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
