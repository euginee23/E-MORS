<?php

/**
 * Admin-facing collections screen. Recording a payment belongs to collectors in the field,
 * so this page is deliberately read-only: the admin monitors and audits, nothing more.
 */

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Collection;
use App\Models\Stall;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    /** Period presets shown as the segmented control, keyed by DateRange period. */
    public const PERIODS = [
        'all' => 'All',
        'today' => 'Daily',
        'week' => 'Weekly',
        'month' => 'Monthly',
        'year' => 'Yearly',
        'custom' => 'Custom',
    ];

    public string $search = '';
    public string $statusFilter = 'all';
    public string $collectorFilter = 'all';
    public string $sectionFilter = 'all';
    public string $periodFilter = 'all';

    #[Validate('nullable|date')]
    public ?string $dateFrom = null;

    #[Validate('nullable|date')]
    public ?string $dateTo = null;

    // View receipt
    public bool $showReceiptModal = false;
    public ?Collection $viewingCollection = null;

    public function updatedSearch(): void
    {
        $this->clearRangeCache();
    }

    public function updatedStatusFilter(): void
    {
        $this->clearRangeCache();
    }

    public function updatedCollectorFilter(): void
    {
        $this->clearRangeCache();
    }

    public function updatedSectionFilter(): void
    {
        $this->clearRangeCache();
    }

    /**
     * A preset fills the From/To inputs with the window it resolves to, so the
     * dates on screen always describe what the figures cover.
     */
    public function updatedPeriodFilter(): void
    {
        if ($this->periodFilter !== 'custom') {
            $range = DateRange::resolve($this->periodFilter);
            $this->dateFrom = $range?->start->toDateString();
            $this->dateTo = $range?->end->toDateString();
            $this->resetValidation();
        }

        $this->clearRangeCache();
    }

    /** Typing a date is choosing a custom window. */
    public function updatedDateFrom(): void
    {
        $this->periodFilter = 'custom';
        $this->validateOnly('dateFrom');
        $this->clearRangeCache();
    }

    public function updatedDateTo(): void
    {
        $this->periodFilter = 'custom';
        $this->validateOnly('dateTo');
        $this->clearRangeCache();
    }

    public function clearCustomRange(): void
    {
        $this->periodFilter = 'all';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetValidation();
        $this->clearRangeCache();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->collectorFilter = 'all';
        $this->sectionFilter = 'all';
        $this->clearCustomRange();
    }

    /**
     * Every figure on this page follows the filters, so the whole set has to be
     * recomputed whenever any of them move.
     */
    private function clearRangeCache(): void
    {
        $this->resetPage();

        unset(
            $this->range,
            $this->collections,
            $this->rangeTotals,
            $this->byCollector,
            $this->bySection,
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

    #[Computed]
    public function collectorOptions()
    {
        return User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function sectionOptions()
    {
        return Stall::where('market_id', $this->marketId)->distinct()->orderBy('section')->pluck('section');
    }

    /**
     * The one filtered base the table and every summary build on, so they can't
     * disagree about which collections are in view.
     */
    private function filteredQuery(): Builder
    {
        return Collection::query()
            ->where('collections.market_id', $this->marketId)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('receipt_number', 'like', '%' . $this->search . '%')
                   ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('vendor', fn ($q3) => $q3->where('contact_name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('collections.status', $this->statusFilter))
            ->when($this->collectorFilter !== 'all', fn ($q) => $q->where('collections.collector_id', $this->collectorFilter))
            ->when($this->sectionFilter !== 'all', fn ($q) => $q->whereHas('stall', fn ($s) => $s->where('section', $this->sectionFilter)))
            ->when($this->range, fn ($q) => $this->range->applyTo($q, 'collections.payment_date'));
    }

    #[Computed]
    public function collections()
    {
        return $this->filteredQuery()
            ->with(['vendor', 'stall', 'collector'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Headline figures for the filtered window. Amounts only count paid
     * collections; the receipt count covers every record in view.
     */
    #[Computed]
    public function rangeTotals(): array
    {
        $query = $this->filteredQuery();

        return [
            'amount' => (float) $query->clone()->where('collections.status', PaymentStatus::Paid)->sum('collections.amount'),
            'count' => (int) $query->clone()->count(),
            'collectors' => (int) $query->clone()->whereNotNull('collections.collector_id')->distinct()->count('collections.collector_id'),
            'sections' => (int) $query->clone()->join('stalls', 'collections.stall_id', '=', 'stalls.id')->distinct()->count('stalls.section'),
        ];
    }

    #[Computed]
    public function byCollector(): array
    {
        $rows = $this->filteredQuery()
            ->selectRaw('collections.collector_id, COUNT(*) as receipts, SUM(CASE WHEN collections.status = ? THEN collections.amount ELSE 0 END) as total', [PaymentStatus::Paid->value])
            ->groupBy('collections.collector_id')
            ->orderByDesc('total')
            ->get();

        $names = User::whereIn('id', $rows->pluck('collector_id')->filter())->pluck('name', 'id');
        $max = (float) $rows->max('total') ?: 1;

        return $rows->map(fn ($row) => [
            'name' => $names[$row->collector_id] ?? __('Unassigned'),
            'receipts' => (int) $row->receipts,
            'amount' => (float) $row->total,
            'percentage' => round(((float) $row->total / $max) * 100),
        ])->all();
    }

    /** Same shape as the Reports section breakdown, narrowed by this page's filters. */
    #[Computed]
    public function bySection(): array
    {
        return $this->filteredQuery()
            ->join('stalls', 'collections.stall_id', '=', 'stalls.id')
            ->selectRaw('stalls.section, COUNT(*) as receipts, SUM(CASE WHEN collections.status = ? THEN collections.amount ELSE 0 END) as total', [PaymentStatus::Paid->value])
            ->groupBy('stalls.section')
            ->orderBy('stalls.section')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->section,
                'receipts' => (int) $row->receipts,
                'amount' => (float) $row->total,
            ])
            ->all();
    }

    public function viewReceipt(int $collectionId): void
    {
        $this->viewingCollection = Collection::where('market_id', $this->marketId)
            ->with(['vendor', 'stall', 'collector'])
            ->findOrFail($collectionId);
        $this->showReceiptModal = true;
    }

    public function render()
    {
        return $this->view()->title(__('Fee Collection'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('message') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Fee Collection') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Monitor payments and review digital receipts. Collections are recorded by collectors in the field.') }}</flux:subheading>
            </div>
        </div>

        {{-- Period --}}
        <div class="flex flex-col gap-4 rounded-2xl border border-orange-100 bg-white/80 p-4 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 lg:flex-row lg:items-end">
            <div class="flex-1">
                <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Period') }}</flux:text>
                <div class="mt-2 inline-flex flex-wrap gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                    @foreach($this::PERIODS as $key => $label)
                    <button type="button" wire:click="$set('periodFilter', '{{ $key }}')" wire:key="period-{{ $key }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition',
                            'bg-orange-500 text-white shadow-sm' => $periodFilter === $key,
                            'text-zinc-600 hover:bg-white dark:text-zinc-300 dark:hover:bg-zinc-700' => $periodFilter !== $key,
                        ])>
                        {{ __($label) }}
                    </button>
                    @endforeach
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">{{ $this->rangeLabel }}</flux:text>
            </div>
            <div class="grid grid-cols-2 gap-3 lg:w-96">
                <flux:input wire:model.live="dateFrom" type="date" size="sm" :label="__('From')" max="{{ $dateTo }}" />
                <flux:input wire:model.live="dateTo" type="date" size="sm" :label="__('To')" min="{{ $dateFrom }}" />
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collections') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱ {{ number_format($this->rangeTotals['amount'], 0) }}</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->rangeLabel }} · {{ $this->rangeTotals['count'] }} {{ trans_choice('transaction|transactions', $this->rangeTotals['count']) }}</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Receipts') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ number_format($this->rangeTotals['count']) }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collectors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ number_format($this->rangeTotals['collectors']) }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Sections') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ number_format($this->rangeTotals['sections']) }}</flux:heading>
            </div>
        </div>

        {{-- Summaries --}}
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-orange-100 bg-white/80 p-4 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:heading size="sm">{{ __('By Collector') }}</flux:heading>
                <div class="mt-3 space-y-3">
                    @forelse($this->byCollector as $row)
                    <div wire:key="by-collector-{{ $loop->index }}">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $row['name'] }}</span>
                            <span class="text-xs text-zinc-500">{{ trans_choice(':count receipt|:count receipts', $row['receipts']) }}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">₱ {{ number_format($row['amount'], 0) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $row['percentage'] }}%"></div>
                        </div>
                    </div>
                    @empty
                    <flux:text class="text-sm text-zinc-400">{{ __('No collections in view.') }}</flux:text>
                    @endforelse
                </div>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 p-4 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:heading size="sm">{{ __('By Section') }}</flux:heading>
                <div class="mt-3 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($this->bySection as $row)
                    <div class="flex items-center justify-between gap-3 py-2 text-sm" wire:key="by-section-{{ $loop->index }}">
                        <span class="w-20 font-medium text-zinc-900 dark:text-zinc-100">{{ $row['name'] }}</span>
                        <span class="flex-1 text-xs text-zinc-500">{{ trans_choice(':count receipt|:count receipts', $row['receipts']) }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">₱ {{ number_format($row['amount'], 0) }}</span>
                    </div>
                    @empty
                    <flux:text class="text-sm text-zinc-400">{{ __('No collections in view.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by vendor, receipt, or reference number...') }}" />
            </div>
            <flux:select wire:model.live="statusFilter" class="lg:w-40">
                <flux:select.option value="all">{{ __('Status: All') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="collectorFilter" class="lg:w-48">
                <flux:select.option value="all">{{ __('Collector: All') }}</flux:select.option>
                @foreach($this->collectorOptions as $collector)
                <flux:select.option value="{{ $collector->id }}">{{ $collector->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="sectionFilter" class="lg:w-40">
                <flux:select.option value="all">{{ __('Section: All') }}</flux:select.option>
                @foreach($this->sectionOptions as $section)
                <flux:select.option value="{{ $section }}">{{ $section }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="ghost" icon="x-mark" wire:click="clearFilters">{{ __('Clear') }}</flux:button>
        </div>

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
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->collections as $collection)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="collection-{{ $collection->id }}">
                            <td class="px-6 py-3 font-mono text-xs font-medium text-zinc-900 dark:text-zinc-100">{{ $collection->receipt_number }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->payment_date->format('M j, Y') }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->vendor?->contact_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->stall?->stall_number ?? '—' }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($collection->amount, 0) }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection->collector?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$collection->status->color()" size="sm">{{ $collection->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewReceipt({{ $collection->id }})">{{ __('View') }}</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-zinc-500">
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

    {{-- View Receipt Modal --}}
    @if($viewingCollection)
    <flux:modal wire:model="showReceiptModal" class="max-w-md">
        <div class="space-y-6">
            <div class="text-center">
                <flux:heading size="lg">{{ __('Payment Receipt') }}</flux:heading>
                <flux:text class="font-mono text-lg font-bold mt-1">{{ $viewingCollection->receipt_number }}</flux:text>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700 rounded-xl border border-zinc-200 dark:border-zinc-700">
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Date') }}</flux:text>
                    <flux:text class="font-medium">{{ $viewingCollection->payment_date->format('M j, Y') }}</flux:text>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Vendor') }}</flux:text>
                    <flux:text class="font-medium">{{ $viewingCollection->vendor?->contact_name }}</flux:text>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Stall') }}</flux:text>
                    <flux:text class="font-medium">{{ $viewingCollection->stall?->stall_number ?? '—' }}</flux:text>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Amount') }}</flux:text>
                    <flux:text class="font-bold text-lg">₱ {{ number_format($viewingCollection->amount, 2) }}</flux:text>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Method') }}</flux:text>
                    <flux:text class="font-medium">{{ ucfirst(str_replace('_', ' ', $viewingCollection->payment_method)) }}</flux:text>
                </div>
                @if($viewingCollection->reference_number)
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Reference No.') }}</flux:text>
                    <flux:text class="font-mono font-medium">{{ $viewingCollection->reference_number }}</flux:text>
                </div>
                @endif
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Collector') }}</flux:text>
                    <flux:text class="font-medium">{{ $viewingCollection->collector?->name ?? '—' }}</flux:text>
                </div>
                <div class="flex justify-between px-4 py-3">
                    <flux:text class="text-zinc-500">{{ __('Status') }}</flux:text>
                    <flux:badge :color="$viewingCollection->status->color()" size="sm">{{ $viewingCollection->status->label() }}</flux:badge>
                </div>
                @if($viewingCollection->notes)
                <div class="px-4 py-3">
                    <flux:text class="text-zinc-500 mb-1">{{ __('Notes') }}</flux:text>
                    <flux:text>{{ $viewingCollection->notes }}</flux:text>
                </div>
                @endif
            </div>

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="$set('showReceiptModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
    </flux:modal>
    @endif


</div>
