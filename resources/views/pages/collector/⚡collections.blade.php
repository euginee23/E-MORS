<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Support\DateRange;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $periodFilter = 'month';

    #[Validate('nullable|date')]
    public ?string $dateFrom = null;

    #[Validate('nullable|date')]
    public ?string $dateTo = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodFilter(): void
    {
        $this->clearRangeCache();
    }

    public function updatedDateFrom(): void
    {
        $this->validateOnly('dateFrom');
        $this->clearRangeCache();
    }

    public function updatedDateTo(): void
    {
        $this->validateOnly('dateTo');
        $this->clearRangeCache();
    }

    public function clearCustomRange(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetValidation();
        $this->clearRangeCache();
    }

    private function clearRangeCache(): void
    {
        $this->resetPage();

        unset(
            $this->range,
            $this->collections,
            $this->rangeTotal,
            $this->rangeReceipts,
            $this->avgDaily,
        );
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    /** Null while "All Time" is selected — the queries then stay unbounded. */
    #[Computed]
    public function range(): ?DateRange
    {
        return DateRange::resolve($this->periodFilter, $this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function rangeLabel(): string
    {
        return $this->range?->label ?? __('All Time');
    }

    /** Every summary card below is this query plus one aggregate. */
    private function rangeQuery()
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->when($this->range, fn ($q) => $this->range->applyTo($q));
    }

    #[Computed]
    public function collections()
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->with(['vendor', 'stall'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('receipt_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('vendor', fn ($q3) => $q3->where('contact_name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->range, fn ($q) => $this->range->applyTo($q))
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    #[Computed]
    public function weekTotal(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');
        return number_format($total, 0);
    }

    #[Computed]
    public function rangeTotal(): string
    {
        return number_format((float) $this->rangeQuery()->sum('amount'), 0);
    }

    #[Computed]
    public function rangeReceipts(): int
    {
        return $this->rangeQuery()->count();
    }

    #[Computed]
    public function avgDaily(): string
    {
        $days = $this->rangeQuery()->distinct()->count(\DB::raw('DATE(payment_date)'));

        if ($days === 0) return '0';

        return number_format((float) $this->rangeQuery()->sum('amount') / $days, 0);
    }

    public function render()
    {
        return $this->view()->title(__('My Collections'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Collections') }}</flux:heading>
                <flux:subheading>{{ __('History of all payments you\'ve collected.') }}</flux:subheading>
            </div>
            <flux:button variant="primary" size="sm" icon="plus" :href="route('collector.collect')" wire:navigate>
                {{ __('Record New') }}
            </flux:button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('This Week') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.banknotes class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->weekTotal }}</flux:heading>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Collected') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.calendar class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->rangeTotal }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->rangeLabel }}</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Receipts') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.document-text class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->rangeReceipts }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->rangeLabel }}</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Avg. Daily') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.chart-bar class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->avgDaily }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">per working day</flux:text>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by vendor name or receipt number...') }}" />
            </div>
            <flux:select wire:model.live="statusFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="periodFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
                <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
                <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
                <flux:select.option value="custom">{{ __('Custom range') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Custom Date Range --}}
        @if($periodFilter === 'custom')
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
            <flux:text class="text-sm text-zinc-500 sm:ml-auto sm:pb-2">{{ __('Showing') }}: <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->rangeLabel }}</span></flux:text>
        </div>
        @endif

        {{-- Collections Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Receipt #') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->collections as $collection)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="col-{{ $collection->id }}">
                            <td class="px-6 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $collection->receipt_number }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->payment_date->format('M j, Y') }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $collection->vendor?->contact_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->stall?->stall_number ?? '—' }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($collection->amount, 0) }}</td>
                            <td class="px-6 py-3">
                                <flux:badge color="zinc" size="sm">{{ ucfirst($collection->payment_method) }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$collection->status->color()" size="sm">{{ $collection->status->label() }}</flux:badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No collections found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->collections->links() }}
            </div>
        </div>
    </div>
</div>
