<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $periodFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingCollectionId = null;
    public ?int $formVendorId = null;
    public string $formAmount = '';
    public string $formPaymentDate = '';
    public string $formPaymentMethod = 'cash';
    public string $formStatus = 'paid';
    public string $formNotes = '';

    // View receipt
    public bool $showReceiptModal = false;
    public ?Collection $viewingCollection = null;



    public function mount(): void
    {
        $this->formPaymentDate = now()->toDateString();
    }

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

    public function updatedFormVendorId(): void
    {
        if ($this->formVendorId) {
            $vendor = Vendor::with('stall')->find($this->formVendorId);
            if ($vendor?->stall) {
                $this->formAmount = (string) $vendor->stall->monthly_rate;
            }
        }
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

    #[Computed]
    public function vendorsWithStalls(): \Illuminate\Support\Collection
    {
        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->with('stall')
            ->orderBy('contact_name')
            ->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->formPaymentDate = now()->toDateString();
        $this->showModal = true;
    }

    public function openEditModal(int $collectionId): void
    {
        $collection = Collection::where('market_id', $this->marketId)->findOrFail($collectionId);
        $this->editingCollectionId = $collection->id;
        $this->formVendorId = $collection->vendor_id;
        $this->formAmount = (string) $collection->amount;
        $this->formPaymentDate = $collection->payment_date->format('Y-m-d');
        $this->formPaymentMethod = $collection->payment_method;
        $this->formStatus = $collection->status->value;
        $this->formNotes = $collection->notes ?? '';
        $this->showModal = true;
    }

    public function viewReceipt(int $collectionId): void
    {
        $this->viewingCollection = Collection::where('market_id', $this->marketId)
            ->with(['vendor', 'stall', 'collector'])
            ->findOrFail($collectionId);
        $this->showReceiptModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formVendorId' => ['required', 'exists:vendors,id'],
            'formAmount' => ['required', 'numeric', 'min:0.01'],
            'formPaymentDate' => ['required', 'date'],
            'formPaymentMethod' => ['required', 'string', 'max:50'],
            'formStatus' => ['required', Rule::in(array_column(PaymentStatus::cases(), 'value'))],
            'formNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $vendor = Vendor::with('stall')->findOrFail($this->formVendorId);

        if ($this->editingCollectionId) {
            $collection = Collection::where('market_id', $this->marketId)->findOrFail($this->editingCollectionId);
            $collection->update([
                'vendor_id' => $this->formVendorId,
                'stall_id' => $vendor->stall?->id,
                'amount' => $this->formAmount,
                'payment_date' => $this->formPaymentDate,
                'payment_method' => $this->formPaymentMethod,
                'status' => $this->formStatus,
                'notes' => $this->formNotes ?: null,
                'collector_id' => $this->formStatus === 'paid' ? Auth::id() : null,
            ]);
            $this->dispatch('toast', message: 'Collection updated successfully.', type: 'success');
        } else {
            $receiptNumber = Collection::generateReceiptNumber($this->marketId);
            Collection::create([
                'market_id' => $this->marketId,
                'vendor_id' => $this->formVendorId,
                'stall_id' => $vendor->stall?->id,
                'collector_id' => $this->formStatus === 'paid' ? Auth::id() : null,
                'receipt_number' => $receiptNumber,
                'amount' => $this->formAmount,
                'payment_date' => $this->formPaymentDate,
                'payment_method' => $this->formPaymentMethod,
                'status' => $this->formStatus,
                'notes' => $this->formNotes ?: null,
            ]);
            $this->dispatch('toast', message: 'Payment recorded successfully. Receipt: ' . $receiptNumber, type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function deleteCollection(int $collectionId): void
    {
        Collection::where('market_id', $this->marketId)->findOrFail($collectionId)->delete();
        $this->dispatch('toast', message: 'Collection deleted successfully.', type: 'success');
        $this->clearCache();
    }

    private function resetForm(): void
    {
        $this->editingCollectionId = null;
        $this->formVendorId = null;
        $this->formAmount = '';
        $this->formPaymentDate = now()->toDateString();
        $this->formPaymentMethod = 'cash';
        $this->formStatus = 'paid';
        $this->formNotes = '';
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->collections, $this->todayTotal, $this->weekTotal, $this->pendingCount, $this->collectionRate);
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
                <flux:subheading class="mt-1">{{ __('Track payments, issue digital receipts, and monitor collection status.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Record Payment') }}
            </flux:button>
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
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="viewReceipt({{ $collection->id }})">{{ __('View Receipt') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $collection->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" x-on:click="$dispatch('open-confirm', { title: 'Delete Collection', message: 'Are you sure you want to delete receipt {{ $collection->receipt_number }}?', confirm: 'Delete', variant: 'danger', onConfirm: () => $wire.deleteCollection({{ $collection->id }}) })">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
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

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCollectionId ? __('Edit Collection') : __('Record Payment') }}</flux:heading>
                <flux:subheading>{{ $editingCollectionId ? __('Update payment details.') : __('Record a new fee collection.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:select wire:model.live="formVendorId" :label="__('Vendor')" required>
                    <flux:select.option :value="null">{{ __('— Select Vendor —') }}</flux:select.option>
                    @foreach($this->vendorsWithStalls as $vendor)
                    <flux:select.option :value="$vendor->id">{{ $vendor->contact_name }} — {{ $vendor->stall->stall_number }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formAmount" :label="__('Amount (₱)')" type="number" step="0.01" required />
                    <flux:input wire:model="formPaymentDate" :label="__('Payment Date')" type="date" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="formPaymentMethod" :label="__('Payment Method')">
                        <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                        <flux:select.option value="gcash">{{ __('GCash') }}</flux:select.option>
                        <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                    </flux:select>
                    <flux:select wire:model="formStatus" :label="__('Status')">
                        <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
                    </flux:select>
                </div>
                <flux:textarea wire:model="formNotes" :label="__('Notes')" rows="2" />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingCollectionId ? __('Update') : __('Record Payment') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

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
