<?php

/**
 * The vendor's payment history and rent monitoring.
 *
 * Paid rows are recorded collections; Unpaid rows are generated from each
 * rented stall's monthly rate for every month in view that its paid
 * collections don't cover (see StallDues). Pending/overdue collection
 * records are left out so a shortfall isn't counted twice.
 */

use App\Enums\DueStatus;
use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Support\DateRange;
use App\Support\StallDue;
use App\Support\StallDues;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    private const PER_PAGE = 10;

    public string $statusFilter = 'all';
    public string $stallFilter = 'all';
    public string $periodFilter = 'all';

    #[Validate('nullable|date')]
    public ?string $dateFrom = null;

    #[Validate('nullable|date')]
    public ?string $dateTo = null;

    public function updated(string $property): void
    {
        if (in_array($property, ['dateFrom', 'dateTo'], true)) {
            $this->validateOnly($property);
        }

        if ($property === 'periodFilter' && $this->periodFilter !== 'custom') {
            $this->dateFrom = null;
            $this->dateTo = null;
            $this->resetValidation();
        }

        $this->resetPage();
        unset($this->range, $this->allRecords, $this->records, $this->summary);
    }

    #[Computed]
    public function vendor()
    {
        return Auth::user()->vendor?->load(['stalls' => fn ($q) => $q->orderBy('section')->orderBy('stall_number')]);
    }

    /** Null for All Time — paid rows stay unbounded and dues run from each tenancy's start. */
    #[Computed]
    public function range(): ?DateRange
    {
        return DateRange::resolve($this->periodFilter, $this->dateFrom, $this->dateTo);
    }

    /**
     * Every paid and unpaid row for the chosen stall and period, newest first —
     * before the status filter, so the summary can describe both sides.
     */
    #[Computed]
    public function allRecords(): \Illuminate\Support\Collection
    {
        if (! $this->vendor) {
            return collect();
        }

        $stallId = $this->stallFilter === 'all' ? null : (int) $this->stallFilter;

        $paid = Collection::where('vendor_id', $this->vendor->id)
            ->where('status', PaymentStatus::Paid)
            ->with(['collector', 'stall'])
            ->when($stallId, fn ($q) => $q->where('stall_id', $stallId))
            ->when($this->range, fn ($q) => $this->range->applyTo($q))
            ->get()
            ->map(fn (Collection $payment) => [
                'key' => 'paid-' . $payment->id,
                'date' => $payment->payment_date,
                'stall' => $payment->stall?->stall_number,
                'receipt' => $payment->receipt_number,
                'amount' => (float) $payment->amount,
                'method' => $payment->payment_method,
                'reference' => $payment->reference_number,
                'collector' => $payment->collector?->name,
                'status' => DueStatus::Paid,
                'note' => null,
            ]);

        $stalls = $this->vendor->stalls->when($stallId, fn ($stalls) => $stalls->where('id', $stallId));

        $unpaid = StallDues::forStalls($stalls, $this->range?->start, $this->range?->end)
            ->reject(fn (StallDue $due) => $due->isSettled())
            ->map(fn (StallDue $due) => [
                'key' => 'due-' . $due->stall->id . '-' . $due->month->format('Y-m'),
                'date' => $due->dueDate,
                'stall' => $due->stall->stall_number,
                'receipt' => null,
                'amount' => $due->balance(),
                'method' => null,
                'reference' => null,
                'collector' => null,
                'status' => DueStatus::Unpaid,
                'note' => $due->paid > 0
                    ? __('Balance for :month', ['month' => $due->month->format('F Y')])
                    : __('Rent for :month', ['month' => $due->month->format('F Y')]),
            ]);

        return $paid->concat($unpaid)
            ->sortByDesc(fn ($row) => $row['date']->format('Y-m-d') . ($row['status'] === DueStatus::Unpaid ? '1' : '0'))
            ->values();
    }

    #[Computed]
    public function records(): LengthAwarePaginator
    {
        $rows = $this->statusFilter === 'all'
            ? $this->allRecords
            : $this->allRecords->filter(fn ($row) => $row['status']->value === $this->statusFilter)->values();

        $page = $this->getPage();

        return new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
        );
    }

    #[Computed]
    public function summary(): array
    {
        $paid = $this->allRecords->where('status', DueStatus::Paid);
        $unpaid = $this->allRecords->where('status', DueStatus::Unpaid);
        $paidAmount = (float) $paid->sum('amount');
        $unpaidAmount = (float) $unpaid->sum('amount');
        $due = $paidAmount + $unpaidAmount;

        return [
            'paidCount' => $paid->count(),
            'paidAmount' => $paidAmount,
            'unpaidCount' => $unpaid->count(),
            'unpaidAmount' => $unpaidAmount,
            'due' => $due,
            'collected' => $due > 0 ? (int) round($paidAmount / $due * 100) : 0,
        ];
    }

    public function render()
    {
        return $this->view()->title(__('My Payments'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Payments') }}</flux:heading>
                <flux:subheading>{{ __('View your payment history and track which stall rentals are paid or unpaid.') }}</flux:subheading>
            </div>
        </div>

        {{-- Payment History --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="flex flex-col gap-4 border-b border-orange-100 px-6 py-4 dark:border-zinc-700 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Payment History') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500">{{ __('Filters and monitoring for paid and unpaid stall rentals.') }}</flux:text>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <flux:select wire:model.live="statusFilter" size="sm" class="sm:w-36">
                        <flux:select.option value="all">{{ __('Status: All') }}</flux:select.option>
                        <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                        <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
                    </flux:select>
                    <flux:select wire:model.live="stallFilter" size="sm" class="sm:w-40">
                        <flux:select.option value="all">{{ __('Stall: All stalls') }}</flux:select.option>
                        @foreach($this->vendor?->stalls ?? [] as $stall)
                        <flux:select.option :value="(string) $stall->id">{{ $stall->stall_number }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="periodFilter" size="sm" class="sm:w-40">
                        <flux:select.option value="all">{{ __('Period: All time') }}</flux:select.option>
                        <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
                        <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
                        <flux:select.option value="year">{{ __('This Year') }}</flux:select.option>
                        <flux:select.option value="custom">{{ __('Custom range') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>

            @if($periodFilter === 'custom')
            <div class="grid grid-cols-2 gap-3 border-b border-orange-100 px-6 py-3 dark:border-zinc-700 sm:max-w-md">
                <flux:input wire:model.live="dateFrom" type="date" size="sm" :label="__('From')" max="{{ $dateTo }}" />
                <flux:input wire:model.live="dateTo" type="date" size="sm" :label="__('To')" min="{{ $dateFrom }}" />
            </div>
            @endif

            {{-- Monitoring --}}
            @php $summary = $this->summary; @endphp
            <div class="grid gap-3 px-6 py-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                    <flux:text class="text-xs text-zinc-500">{{ __('Paid') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-lime-600 dark:text-lime-400">{{ $summary['paidCount'] }}</div>
                    <flux:text class="text-sm">₱{{ number_format($summary['paidAmount'], 0) }}</flux:text>
                </div>
                <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                    <flux:text class="text-xs text-zinc-500">{{ __('Unpaid') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $summary['unpaidCount'] }}</div>
                    <flux:text class="text-sm">₱{{ number_format($summary['unpaidAmount'], 0) }}</flux:text>
                </div>
                <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                    <flux:text class="text-xs text-zinc-500">{{ __('Collected') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $summary['collected'] }}%</div>
                    <flux:text class="text-sm">{{ __('of ₱:amount due', ['amount' => number_format($summary['due'], 0)]) }}</flux:text>
                </div>
                <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                    <flux:text class="text-xs text-zinc-500">{{ __('Showing') }}</flux:text>
                    <div class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $this->records->total() }}</div>
                    <flux:text class="text-sm">{{ __('records in view') }}</flux:text>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-y border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Receipt #') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->records as $row)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="{{ $row['key'] }}">
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row['date']->format('M j, Y') }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $row['stall'] ?? '—' }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">
                                @if($row['receipt'])
                                {{ $row['receipt'] }}
                                @if($row['reference'])
                                <div class="text-[11px] text-zinc-400">{{ __('Ref') }}: {{ $row['reference'] }}</div>
                                @endif
                                @else
                                <span class="font-sans text-zinc-400">{{ $row['note'] }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱{{ number_format($row['amount'], 0) }}</td>
                            <td class="px-6 py-3">
                                @if($row['method'])
                                <flux:badge color="zinc" size="sm">{{ ucfirst(str_replace('_', ' ', $row['method'])) }}</flux:badge>
                                @else
                                <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ $row['collector'] ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$row['status']->color()" size="sm">{{ $row['status']->label() }}</flux:badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No payments found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->records->links() }}
            </div>
        </div>
    </div>
</div>
