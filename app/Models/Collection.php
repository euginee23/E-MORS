<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'vendor_id',
        'stall_id',
        'collector_id',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'payment_date' => 'date',
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

    public function stall(): BelongsTo
    {
        return $this->belongsTo(Stall::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public static function generateReceiptNumber(int $marketId): string
    {
        $year = now()->year;
        $lastReceipt = static::where('market_id', $marketId)
            ->whereYear('created_at', $year)
            ->orderByDesc('id')
            ->value('receipt_number');

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('RCP-%d-%04d', $year, $nextNumber);
    }
}
