<?php

namespace App\Support;

use App\Enums\DueStatus;
use App\Enums\PaymentStatus;
use App\Models\Collection as Payment;
use App\Models\Stall;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Monthly rent dues, derived rather than stored.
 *
 * There is no bill table: a stall owes its monthly_rate for every month of its
 * current tenancy, and that month is settled by the paid collections recorded
 * against the stall with a payment_date inside it. Paid ≥ rate is Paid,
 * anything less but above zero is Partial, nothing is Unpaid.
 */
final class StallDues
{
    public static function statusOf(float $paid, float $due): DueStatus
    {
        if ($paid > 0 && $paid + 0.005 >= $due) {
            return DueStatus::Paid;
        }

        return $paid > 0 ? DueStatus::Partial : DueStatus::Unpaid;
    }

    /**
     * Paid amounts per stall per month, keyed `[stall_id]['Y-m']`.
     *
     * Bucketed in PHP so the grouping doesn't depend on the driver's date functions.
     *
     * @param  array<int>  $stallIds
     * @return array<int, array<string, array{paid: float, last: CarbonInterface}>>
     */
    public static function paidByMonth(array $stallIds, ?CarbonInterface $from = null, ?CarbonInterface $to = null, ?int $vendorId = null): array
    {
        if ($stallIds === []) {
            return [];
        }

        $rows = Payment::query()
            ->whereIn('stall_id', $stallIds)
            ->where('status', PaymentStatus::Paid)
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to->toDateString()))
            ->get(['stall_id', 'payment_date', 'amount']);

        $buckets = [];

        foreach ($rows as $row) {
            $key = $row->payment_date->format('Y-m');
            $bucket = $buckets[$row->stall_id][$key] ?? ['paid' => 0.0, 'last' => $row->payment_date];
            $bucket['paid'] += (float) $row->amount;
            $bucket['last'] = $row->payment_date->max($bucket['last']);
            $buckets[$row->stall_id][$key] = $bucket;
        }

        return $buckets;
    }

    /**
     * Every billable month for the given stalls inside the window, newest first.
     *
     * A stall bills from its rent start (or the start of the current month when
     * no start was recorded) through the earlier of the window end, its rent
     * expiry and the current month — future months are never due. Only stalls
     * with a vendor bill, and only that vendor's payments count toward them.
     *
     * @param  iterable<Stall>  $stalls
     * @return Collection<int, StallDue>
     */
    public static function forStalls(iterable $stalls, ?CarbonInterface $from = null, ?CarbonInterface $to = null): Collection
    {
        $stalls = collect($stalls)->filter(fn (Stall $stall) => $stall->vendor_id);

        if ($stalls->isEmpty()) {
            return collect();
        }

        $paid = [];
        foreach ($stalls->groupBy('vendor_id') as $vendorId => $group) {
            $paid += self::paidByMonth($group->pluck('id')->all(), null, null, (int) $vendorId);
        }

        $currentMonthEnd = Carbon::now()->endOfMonth();
        $dues = collect();

        foreach ($stalls as $stall) {
            $termStart = ($stall->rent_start ?? Carbon::now())->copy()->startOfDay();

            $start = $termStart->copy()->startOfMonth();
            if ($from && $from->copy()->startOfMonth()->gt($start)) {
                $start = $from->copy()->startOfMonth();
            }

            $end = $to ? $to->copy()->min($currentMonthEnd) : $currentMonthEnd->copy();
            if ($stall->rent_expiry) {
                $end = $end->min($stall->rent_expiry->copy()->endOfDay());
            }

            for ($month = $start->copy(); $month->lte($end); $month = $month->copy()->addMonthNoOverflow()) {
                $bucket = $paid[$stall->id][$month->format('Y-m')] ?? null;

                $dues->push(new StallDue(
                    stall: $stall,
                    month: $month->copy(),
                    dueDate: $month->copy()->max($termStart),
                    due: (float) $stall->monthly_rate,
                    paid: $bucket['paid'] ?? 0.0,
                    lastPaymentDate: $bucket['last'] ?? null,
                ));
            }
        }

        return $dues->sortByDesc(fn (StallDue $due) => $due->month->format('Y-m').'|'.$due->stall->stall_number)->values();
    }

    /**
     * Each stall's due for the current month, keyed by stall id.
     *
     * @param  iterable<Stall>  $stalls
     * @return Collection<int, StallDue>
     */
    public static function currentMonth(iterable $stalls): Collection
    {
        $now = Carbon::now();

        return self::forStalls($stalls, $now->copy()->startOfMonth(), $now->copy()->endOfMonth())
            ->keyBy(fn (StallDue $due) => $due->stall->id);
    }
}
