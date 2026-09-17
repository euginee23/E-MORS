<?php

/**
 * Admin-facing collections screen. Recording a payment belongs to collectors in the field,
 * so this page is deliberately read-only: the admin monitors and audits, nothing more.
 */

use App\Enums\PaymentStatus;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $periodFilter = 'all';

    // View receipt
    public bool $showReceiptModal = false;
    public ?Collection $viewingCollection = null;

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
        $this->resetPage();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function collections()
    {
        return Collection::where('market_id', $this->marketId)
            ->with(['vendor', 'stall', 'collector'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('receipt_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('vendor', fn ($q3) => $q3->where('contact_name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->periodFilter !== 'all', fn ($q) => match ($this->periodFilter) {
                'today' => $q->whereDate('payment_date', today()),
                'week' => $q->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $q->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year),
                default => $q,
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function todayTotal(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function weekTotal(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Pending)
            ->count();
    }

    #[Computed]
    public function collectionRate(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->whereMonth('payment_date', now()->month)
            ->count();
        $paid = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereMonth('payment_date', now()->month)
            ->count();
        $rate = $total > 0 ? round(($paid / $total) * 100, 1) : 0;
        return $rate . '%';
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

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __("Today's Collections") }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->todayTotal }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('This Week') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->weekTotal }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Payments') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->pendingCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collection Rate') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->collectionRate }}</flux:heading>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by vendor or receipt number...') }}" />
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
            </flux:select>
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
                    <flux:text class="font-medium">{{ ucfirst($viewingCollection->payment_method) }}</flux:text>
                </div>
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
