<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * A resolved reporting window.
 *
 * Collections, My Collections and Reports all offer the same preset periods plus
 * a custom from/to range, so the range arithmetic lives here rather than being
 * repeated as three drifting `match` blocks.
 */
final class DateRange
{
    private function __construct(
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly string $label,
    ) {}

    /**
     * Resolve a period key into a window. A null result means "no date bound"
     * — the caller should leave its query unfiltered (All Time).
     */
    public static function resolve(string $period, ?string $from = null, ?string $to = null): ?self
    {
        if ($period === 'custom') {
            return self::custom($from, $to);
        }

        return match ($period) {
            'today' => new self(Carbon::today(), Carbon::today()->endOfDay(), 'Today'),
            'week' => new self(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), 'This Week'),
            'month' => new self(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'This Month'),
            'last_month' => new self(
                Carbon::now()->subMonthNoOverflow()->startOfMonth(),
                Carbon::now()->subMonthNoOverflow()->endOfMonth(),
                'Last Month',
            ),
            'quarter' => new self(Carbon::now()->firstOfQuarter(), Carbon::now()->lastOfQuarter()->endOfDay(), 'This Quarter'),
            'year' => new self(Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), 'This Year'),
            default => null,
        };
    }

    /**
     * Build a window from user-supplied dates.
     *
     * A half-filled range stays open on the missing side, and a reversed pair is
     * swapped — a backwards `whereBetween` returns nothing, which reads as "no
     * data" rather than "you typed the dates the wrong way round".
     */
    private static function custom(?string $from, ?string $to): ?self
    {
        $start = self::parse($from);
        $end = self::parse($to);

        if (! $start && ! $end) {
            return null;
        }

        $start ??= $end;
        $end ??= $start;

        $start = $start->startOfDay();
        $end = $end->endOfDay();

        if ($end->lessThan($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return new self($start, $end, $start->format('M j, Y').' – '.$end->format('M j, Y'));
    }

    private static function parse(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * `payment_date` is a date column, so bind plain Y-m-d strings — a datetime
     * upper bound depends on how the driver coerces the comparison.
     */
    public function applyTo(Builder $query, string $column = 'payment_date'): Builder
    {
        return $query->whereBetween($column, [$this->start->toDateString(), $this->end->toDateString()]);
    }
}
