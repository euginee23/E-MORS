<?php

namespace App\Support;

use App\Enums\DueStatus;
use App\Models\Stall;
use Carbon\CarbonInterface;

/**
 * One stall's rent for one month, as worked out by StallDues.
 */
final class StallDue
{
    public function __construct(
        public readonly Stall $stall,
        public readonly CarbonInterface $month,
        public readonly CarbonInterface $dueDate,
        public readonly float $due,
        public readonly float $paid,
        public readonly ?CarbonInterface $lastPaymentDate,
    ) {}

    public function balance(): float
    {
        return max(0.0, round($this->due - $this->paid, 2));
    }

    public function status(): DueStatus
    {
        return StallDues::statusOf($this->paid, $this->due);
    }

    public function isSettled(): bool
    {
        return $this->status() === DueStatus::Paid;
    }
}
