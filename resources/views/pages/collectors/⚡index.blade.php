<?php

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $periodFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingCollectorId = null;
    public string $formName = '';
    public string $formEmail = '';
    public string $formPassword = '';
    public string $formPasswordConfirmation = '';



    // View details
    public bool $showDetailModal = false;
    public ?User $viewingCollector = null;

    public function updatedSearch(): void
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
    public function collectors()
    {
        return User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('email', 'like', '%' . $this->search . '%')
            ))
            ->withCount(['collectedPayments as total_collections'])
            ->withSum(['collectedPayments as total_collected' => fn ($q) => $q->where('status', PaymentStatus::Paid)], 'amount')
            ->withCount(['collectedPayments as today_collections' => fn ($q) => $q->whereDate('payment_date', today())])
            ->withSum(['collectedPayments as today_collected' => fn ($q) => $q->where('status', PaymentStatus::Paid)->whereDate('payment_date', today())], 'amount')
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function totalCollectors(): int
    {
        return User::where('market_id', $this->marketId)->where('role', UserRole::Collector)->count();
    }

    #[Computed]
    public function activeToday(): int
    {
        return Collection::where('market_id', $this->marketId)
            ->whereDate('payment_date', today())
            ->distinct('collector_id')
            ->count('collector_id');
    }

    #[Computed]
    public function todayTotalCollected(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function monthTotalCollected(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $collectorId): void
    {
        $collector = User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->findOrFail($collectorId);
        $this->editingCollectorId = $collector->id;
        $this->formName = $collector->name;
        $this->formEmail = $collector->email;
        $this->formPassword = '';
        $this->formPasswordConfirmation = '';
        $this->showModal = true;
    }

    public function viewDetails(int $collectorId): void
    {
        $this->viewingCollector = User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->findOrFail($collectorId);
        $this->showDetailModal = true;
    }

    #[Computed]
    public function collectorRecentCollections(): \Illuminate\Support\Collection
    {
        if (! $this->viewingCollector) {
            return collect();
        }

        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', $this->viewingCollector->id)
            ->with(['vendor', 'stall'])
            ->orderByDesc('payment_date')
            ->limit(15)
            ->get();
    }

    #[Computed]
    public function collectorStats(): array
    {
        if (! $this->viewingCollector) {
            return [];
        }

        $collectorId = $this->viewingCollector->id;
        $marketId = $this->marketId;

        $todayAmount = Collection::where('market_id', $marketId)
            ->where('collector_id', $collectorId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');

        $weekAmount = Collection::where('market_id', $marketId)
            ->where('collector_id', $collectorId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $monthAmount = Collection::where('market_id', $marketId)
            ->where('collector_id', $collectorId)
            ->where('status', PaymentStatus::Paid)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $totalTransactions = Collection::where('market_id', $marketId)
            ->where('collector_id', $collectorId)
            ->count();

        $todayTransactions = Collection::where('market_id', $marketId)
            ->where('collector_id', $collectorId)
            ->whereDate('payment_date', today())
            ->count();

        return [
            'todayAmount' => '₱ ' . number_format($todayAmount, 0),
            'weekAmount' => '₱ ' . number_format($weekAmount, 0),
            'monthAmount' => '₱ ' . number_format($monthAmount, 0),
            'totalTransactions' => $totalTransactions,
            'todayTransactions' => $todayTransactions,
        ];
    }

    public function save(): void
    {
        $rules = [
            'formName' => ['required', 'string', 'max:255'],
            'formEmail' => [
                'required', 'string', 'email', 'max:255',
                $this->editingCollectorId
                    ? Rule::unique(User::class, 'email')->ignore($this->editingCollectorId)
                    : Rule::unique(User::class, 'email'),
            ],
        ];

        if (! $this->editingCollectorId) {
            $rules['formPassword'] = ['required', 'string', 'min:8', 'same:formPasswordConfirmation'];
        } elseif ($this->formPassword !== '') {
            $rules['formPassword'] = ['string', 'min:8', 'same:formPasswordConfirmation'];
        }

        $this->validate($rules, [
            'formName.required' => 'Name is required.',
            'formEmail.required' => 'Email is required.',
            'formPassword.same' => 'Passwords do not match.',
        ]);

        if ($this->editingCollectorId) {
            $collector = User::where('market_id', $this->marketId)
                ->where('role', UserRole::Collector)
                ->findOrFail($this->editingCollectorId);
            $collector->name = $this->formName;
            $collector->email = $this->formEmail;

            if ($this->formPassword !== '') {
                $collector->password = Hash::make($this->formPassword);
            }

            $collector->save();
            $this->dispatch('toast', message: 'Collector updated successfully.', type: 'success');
        } else {
            User::create([
                'name' => $this->formName,
                'email' => $this->formEmail,
                'password' => Hash::make($this->formPassword),
                'role' => UserRole::Collector,
                'market_id' => $this->marketId,
            ]);
            $this->dispatch('toast', message: 'Collector added successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function deleteCollector(int $collectorId): void
    {
        $collector = User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->findOrFail($collectorId);

        // Unassign collections from this collector
        Collection::where('collector_id', $collector->id)->update(['collector_id' => null]);

        $collector->delete();
        $this->dispatch('toast', message: 'Collector removed successfully.', type: 'success');
        $this->clearCache();
    }

    public function resetPassword(int $collectorId): void
    {
        $collector = User::where('market_id', $this->marketId)
            ->where('role', UserRole::Collector)
            ->findOrFail($collectorId);
        $tempPassword = 'password123';
        $collector->password = Hash::make($tempPassword);
        $collector->save();
        $this->dispatch('toast', message: "Password for {$collector->name} has been reset to: {$tempPassword}", type: 'info');
    }

    private function resetForm(): void
    {
        $this->editingCollectorId = null;
        $this->formName = '';
        $this->formEmail = '';
        $this->formPassword = '';
        $this->formPasswordConfirmation = '';
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->collectors, $this->totalCollectors, $this->activeToday, $this->todayTotalCollected, $this->monthTotalCollected);
    }

    public function render()
    {
        return $this->view()->title(__('Collectors Management'));
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
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Collectors Management') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage fee collectors, track performance, and monitor daily collection activity.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Add Collector') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Collectors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalCollectors }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Active Today') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-purple-600">{{ $this->activeToday }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __("Today's Revenue") }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->todayTotalCollected }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Monthly Revenue') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-blue-600">{{ $this->monthTotalCollected }}</flux:heading>
            </div>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search collectors by name or email...') }}" />
            </div>
        </div>

        {{-- Collectors Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Today') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total Collections') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total Collected') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->collectors as $collector)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="collector-{{ $collector->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$collector->name" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $collector->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collector->email }}</td>
                            <td class="px-6 py-3">
                                <div>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($collector->today_collected ?? 0, 0) }}</span>
                                    <span class="text-xs text-zinc-500 ml-1">({{ $collector->today_collections ?? 0 }})</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ number_format($collector->total_collections ?? 0) }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($collector->total_collected ?? 0, 0) }}</td>
                            <td class="px-6 py-3">
                                @if(($collector->today_collections ?? 0) > 0)
                                    <flux:badge color="lime" size="sm">{{ __('Active') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Idle') }}</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="viewDetails({{ $collector->id }})">{{ __('View Details') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $collector->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.item icon="key" x-on:click="$dispatch('open-confirm', { title: 'Reset Password', message: 'Reset password for {{ $collector->name }}?', confirm: 'Reset', variant: 'warning', onConfirm: () => $wire.resetPassword({{ $collector->id }}) })">{{ __('Reset Password') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" x-on:click="$dispatch('open-confirm', { title: 'Remove Collector', message: 'Are you sure you want to remove {{ $collector->name }}? Their existing collection records will be preserved but unassigned.', confirm: 'Remove', variant: 'danger', onConfirm: () => $wire.deleteCollector({{ $collector->id }}) })">{{ __('Remove') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No collectors found. Click "Add Collector" to get started.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->collectors->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingCollectorId ? __('Edit Collector') : __('Add Collector') }}</flux:heading>
                <flux:subheading>{{ $editingCollectorId ? __('Update collector account details.') : __('Create a new collector account.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formName" :label="__('Full Name')" type="text" required />
                    <flux:input wire:model="formEmail" :label="__('Email Address')" type="email" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formPassword" :label="$editingCollectorId ? __('New Password (leave blank to keep)') : __('Password')" type="password" :required="!$editingCollectorId" />
                    <flux:input wire:model="formPasswordConfirmation" :label="__('Confirm Password')" type="password" :required="!$editingCollectorId" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingCollectorId ? __('Update Collector') : __('Add Collector') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Detail/Performance Modal --}}
    @if($viewingCollector)
    <flux:modal wire:model="showDetailModal" class="max-w-2xl">
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <flux:avatar size="lg" :name="$viewingCollector->name" />
                <div>
                    <flux:heading size="lg">{{ $viewingCollector->name }}</flux:heading>
                    <flux:subheading>{{ $viewingCollector->email }}</flux:subheading>
                </div>
            </div>

            {{-- Collector Stats --}}
            @php $stats = $this->collectorStats; @endphp
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:text class="text-xs text-zinc-500">{{ __('Today') }}</flux:text>
                    <div class="mt-0.5 font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['todayAmount'] ?? '₱ 0' }}</div>
                    <flux:text class="text-xs text-zinc-400">{{ $stats['todayTransactions'] ?? 0 }} {{ __('transactions') }}</flux:text>
                </div>
                <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:text class="text-xs text-zinc-500">{{ __('This Week') }}</flux:text>
                    <div class="mt-0.5 font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['weekAmount'] ?? '₱ 0' }}</div>
                </div>
                <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <flux:text class="text-xs text-zinc-500">{{ __('This Month') }}</flux:text>
                    <div class="mt-0.5 font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['monthAmount'] ?? '₱ 0' }}</div>
                </div>
            </div>

            {{-- Recent Collections --}}
            <div>
                <flux:heading size="sm" class="mb-3">{{ __('Recent Collections') }}</flux:heading>
                <div class="rounded-xl border border-orange-100 dark:border-zinc-700 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-orange-100 bg-orange-50/50 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <th class="px-4 py-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                                <th class="px-4 py-2 text-left font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                            @forelse($this->collectorRecentCollections as $collection)
                            <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $collection->payment_date->format('M j') }}</td>
                                <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $collection->vendor?->contact_name ?? '—' }}</td>
                                <td class="px-4 py-2 text-zinc-700 dark:text-zinc-300">{{ $collection->stall?->stall_number ?? '—' }}</td>
                                <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($collection->amount, 0) }}</td>
                                <td class="px-4 py-2">
                                    <flux:badge :color="$collection->status->color()" size="sm">{{ $collection->status->label() }}</flux:badge>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-zinc-500">{{ __('No collections recorded yet.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="$set('showDetailModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
    </flux:modal>
    @endif


</div>
