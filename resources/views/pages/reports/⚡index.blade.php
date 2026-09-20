<?php

use App\Actions\Reports\ExportCollectionReport;
use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Support\DateRange;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $period = 'month';

    #[Validate('nullable|date')]
    public ?string $dateFrom = null;

    #[Validate('nullable|date')]
    public ?string $dateTo = null;

    public function updatedPeriod(): void
    {
        $this->clearCache();
    }

    public function updatedDateFrom(): void
    {
        $this->validateOnly('dateFrom');
        $this->clearCache();
    }

    public function updatedDateTo(): void
    {
        $this->validateOnly('dateTo');
        $this->clearCache();
    }

    public function clearCustomRange(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetValidation();
        $this->clearCache();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    /**
     * A report always covers *some* window — an unresolvable one (a blank custom
     * range) falls back to the month, matching the old `default` arm.
     */
    private function range(): DateRange
    {
        return DateRange::resolve($this->period, $this->dateFrom, $this->dateTo)
            ?? DateRange::resolve('month');
    }

    private function periodRange(): array
    {
        $range = $this->range();

        return [$range->start, $range->end];
    }

    #[Computed]
    public function totalRevenue(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function avgDailyCollection(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $days = max(1, $start->diffInDaysFiltered(fn (Carbon $date) => ! $date->isSunday(), min($end, Carbon::today())) ?: 1);
        return '₱ ' . number_format(round($total / $days), 0);
    }

    #[Computed]
    public function collectionEfficiency(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->whereBetween('payment_date', [$start, $end])
            ->count();
        $paid = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->count();
        $rate = $total > 0 ? round(($paid / $total) * 100, 1) : 0;
        return $rate . '%';
    }

    #[Computed]
    public function outstandingBalance(): array
    {
        $amount = Collection::where('market_id', $this->marketId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->sum('amount');
        $vendorCount = Collection::where('market_id', $this->marketId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->distinct('vendor_id')
            ->count('vendor_id');
        return [
            'amount' => '₱ ' . number_format($amount, 0),
            'vendors' => $vendorCount,
        ];
    }

    /**
     * The row-level ledger shown in the spreadsheet grid. Paginated so a year's
     * worth of collections does not have to render in one page.
     */
    #[Computed]
    public function ledger()
    {
        [$start, $end] = $this->periodRange();

        return Collection::where('market_id', $this->marketId)
            ->whereBetween('payment_date', [$start, $end])
            ->with(['vendor', 'stall', 'collector'])
            ->orderBy('payment_date')
            ->orderBy('id')
            ->paginate(25);
    }

    /**
     * Totals for the whole period, not just the visible page.
     */
    #[Computed]
    public function ledgerTotals(): array
    {
        [$start, $end] = $this->periodRange();

        $query = Collection::where('market_id', $this->marketId)
            ->whereBetween('payment_date', [$start, $end]);

        return [
            'count' => (int) $query->clone()->count(),
            'amount' => (float) $query->clone()->sum('amount'),
            'paid' => (float) $query->clone()->where('status', PaymentStatus::Paid)->sum('amount'),
        ];
    }

    public function periodLabel(): string
    {
        return $this->range()->label;
    }

    public function export()
    {
        [$start, $end] = $this->periodRange();

        $exporter = new ExportCollectionReport(
            marketId: $this->marketId,
            start: $start,
            end: $end,
            periodLabel: $this->periodLabel(),
        );

        // PhpSpreadsheet writes to a stream, so stage the workbook in a temp file first.
        $tempPath = tempnam(sys_get_temp_dir(), 'emors_report_');

        try {
            $exporter->writeTo($tempPath);
        } catch (\Throwable $e) {
            // Never leave the staging file behind when the workbook could not be built.
            @unlink($tempPath);

            throw $e;
        }

        // A custom range label carries commas and an en dash, so strip it down to
        // characters that are safe in a filename rather than only spaces.
        $slug = trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $this->periodLabel()), '_');

        $filename = 'EMORS_Report_' . $slug . '_' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($tempPath) {
            try {
                readfile($tempPath);
            } finally {
                @unlink($tempPath);
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function clearCache(): void
    {
        $this->resetPage();

        unset(
            $this->totalRevenue,
            $this->avgDailyCollection,
            $this->collectionEfficiency,
            $this->outstandingBalance,
            $this->ledger,
            $this->ledgerTotals,
        );
    }

    public function render()
    {
        return $this->view()->title(__('Reports & Analytics'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Reports & Analytics') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Real-time dashboards and comprehensive reports for data-driven decisions.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:select wire:model.live="period" class="sm:w-40">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
                    <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
                    <flux:select.option value="quarter">{{ __('This Quarter') }}</flux:select.option>
                    <flux:select.option value="year">{{ __('This Year') }}</flux:select.option>
                    <flux:select.option value="custom">{{ __('Custom range') }}</flux:select.option>
                </flux:select>
                <flux:button icon="arrow-down-tray" variant="outline" wire:click="export">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </div>

        {{-- Custom Date Range --}}
        @if($period === 'custom')
        <div class="flex flex-col gap-3 rounded-2xl border border-orange-100 bg-white/80 p-4 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 sm:flex-row sm:items-end">
            <div class="sm:w-48">
                <flux:input wire:model.live="dateFrom" type="date" :label="__('From Date')" max="{{ $dateTo }}" />
            </div>
            <div class="sm:w-48">
                <flux:input wire:model.live="dateTo" type="date" :label="__('To Date')" min="{{ $dateFrom }}" />
            </div>
            <flux:button variant="ghost" icon="x-mark" wire:click="clearCustomRange">
                {{ __('Clear') }}
            </flux:button>
            <flux:text class="text-sm text-zinc-500 sm:ml-auto sm:pb-2">{{ __('Reporting on') }}: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->periodLabel() }}</span></flux:text>
        </div>
        @endif

        {{-- Revenue Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Revenue') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalRevenue }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Avg Daily Collection') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->avgDailyCollection }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collection Efficiency') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->collectionEfficiency }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Outstanding Balance') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">{{ $this->outstandingBalance['amount'] }}</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->outstandingBalance['vendors'] }} {{ __('vendors with balance') }}</flux:text>
            </div>
        </div>

        {{-- Collection Ledger — spreadsheet layout --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="flex flex-col gap-2 border-b border-orange-100 px-6 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Collection Ledger') }}</flux:heading>
                    <flux:subheading class="mt-0.5">{{ $this->periodLabel() }} · {{ $this->ledgerTotals['count'] }} {{ __('transactions') }}</flux:subheading>
                </div>
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Period total') }}:
                    <span class="font-mono font-semibold text-zinc-900 dark:text-zinc-100">₱ {{ number_format($this->ledgerTotals['amount'], 2) }}</span>
                </flux:text>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-orange-50 text-left dark:bg-zinc-800">
                            <th class="w-12 border border-orange-100 px-3 py-2 text-center font-semibold text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">#</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Receipt No.') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Date') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Vendor') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Stall') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Section') }}</th>
                            <th class="border border-orange-100 px-3 py-2 text-right font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Amount') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Method') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Collector') }}</th>
                            <th class="border border-orange-100 px-3 py-2 font-semibold text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->ledger as $i => $entry)
                        <tr class="{{ $i % 2 === 1 ? 'bg-orange-50/30 dark:bg-zinc-800/30' : '' }} hover:bg-orange-50 dark:hover:bg-zinc-800/60" wire:key="ledger-{{ $entry->id }}">
                            <td class="border border-orange-100 px-3 py-1.5 text-center font-mono text-xs text-zinc-400 dark:border-zinc-700">{{ $this->ledger->firstItem() + $i }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 font-mono text-xs text-zinc-900 dark:border-zinc-700 dark:text-zinc-100">{{ $entry->receipt_number }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 font-mono text-xs text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ $entry->payment_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ $entry->vendor?->contact_name ?? '—' }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ $entry->stall?->stall_number ?? '—' }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ $entry->stall?->section ?? '—' }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-right font-mono text-zinc-900 dark:border-zinc-700 dark:text-zinc-100">{{ number_format($entry->amount, 2) }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ ucfirst(str_replace('_', ' ', $entry->payment_method)) }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">{{ $entry->collector?->name ?? '—' }}</td>
                            <td class="border border-orange-100 px-3 py-1.5 dark:border-zinc-700">
                                <flux:badge :color="$entry->status->color()" size="sm">{{ $entry->status->label() }}</flux:badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="border border-orange-100 px-3 py-8 text-center text-zinc-500 dark:border-zinc-700">
                                {{ __('No collections recorded for this period.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($this->ledgerTotals['count'] > 0)
                    <tfoot>
                        <tr class="bg-orange-100/60 font-semibold dark:bg-zinc-800">
                            <td colspan="6" class="border border-orange-100 px-3 py-2 text-right text-zinc-700 dark:border-zinc-700 dark:text-zinc-200">{{ __('TOTAL') }} ({{ $this->periodLabel() }})</td>
                            <td class="border border-orange-100 px-3 py-2 text-right font-mono text-zinc-900 dark:border-zinc-700 dark:text-zinc-100">{{ number_format($this->ledgerTotals['amount'], 2) }}</td>
                            <td colspan="3" class="border border-orange-100 px-3 py-2 text-zinc-500 dark:border-zinc-700">
                                {{ __('Paid') }}: <span class="font-mono">{{ number_format($this->ledgerTotals['paid'], 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="border-t border-orange-100 px-6 py-3 dark:border-zinc-700">
                {{ $this->ledger->links() }}
            </div>
        </div>
    </div>
</div>
