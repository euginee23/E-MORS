<?php

use App\Enums\StallStatus;
use App\Models\Stall;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $sectionFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingStallId = null;
    public string $formStallNumber = '';
    public string $formSection = 'A';
    public string $formSize = '3x3m';
    public string $formMonthlyRate = '3500';
    public string $formStatus = 'available';
    public ?int $formVendorId = null;

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deletingStallId = null;
    public string $deletingStallNumber = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSectionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFormVendorId(): void
    {
        if ($this->formVendorId) {
            $this->formStatus = 'occupied';
        } elseif ($this->formStatus === 'occupied') {
            $this->formStatus = 'available';
        }
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function stalls()
    {
        return Stall::where('market_id', $this->marketId)
            ->with('vendor')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('stall_number', 'like', '%' . $this->search . '%')
                   ->orWhereHas('vendor', fn ($q3) => $q3->where('contact_name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->sectionFilter !== 'all', fn ($q) => $q->where('section', $this->sectionFilter))
            ->orderBy('section')
            ->orderBy('stall_number')
            ->paginate(15);
    }

    #[Computed]
    public function stallMap(): array
    {
        return Stall::where('market_id', $this->marketId)
            ->orderBy('stall_number')
            ->get()
            ->groupBy('section')
            ->map(fn ($stalls) => $stalls->map(fn ($s) => [
                'id' => $s->id,
                'no' => $s->stall_number,
                'status' => $s->status->value,
                'vendor' => $s->vendor?->contact_name,
            ])->values()->all())
            ->sortKeys()
            ->all();
    }

    #[Computed]
    public function totalStalls(): int
    {
        return Stall::where('market_id', $this->marketId)->count();
    }

    #[Computed]
    public function occupiedCount(): int
    {
        return Stall::where('market_id', $this->marketId)->where('status', StallStatus::Occupied)->count();
    }

    #[Computed]
    public function availableCount(): int
    {
        return Stall::where('market_id', $this->marketId)->where('status', StallStatus::Available)->count();
    }

    #[Computed]
    public function maintenanceCount(): int
    {
        return Stall::where('market_id', $this->marketId)->where('status', StallStatus::Maintenance)->count();
    }

    #[Computed]
    public function availableVendors(): \Illuminate\Support\Collection
    {
        return Vendor::where('market_id', $this->marketId)
            ->whereDoesntHave('stall', fn ($q) => $q->when($this->editingStallId, fn ($q2) => $q2->where('stalls.id', '!=', $this->editingStallId)))
            ->orderBy('contact_name')
            ->get();
    }

    #[Computed]
    public function sections(): array
    {
        return Stall::where('market_id', $this->marketId)
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->all();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $stallId): void
    {
        $stall = Stall::where('market_id', $this->marketId)->findOrFail($stallId);
        $this->editingStallId = $stall->id;
        $this->formStallNumber = $stall->stall_number;
        $this->formSection = $stall->section;
        $this->formSize = $stall->size;
        $this->formMonthlyRate = (string) $stall->monthly_rate;
        $this->formStatus = $stall->status->value;
        $this->formVendorId = $stall->vendor_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formStallNumber' => [
                'required', 'string', 'max:10',
                $this->editingStallId
                    ? Rule::unique('stalls', 'stall_number')->where('market_id', $this->marketId)->ignore($this->editingStallId)
                    : Rule::unique('stalls', 'stall_number')->where('market_id', $this->marketId),
            ],
            'formSection' => ['required', 'string', 'max:5'],
            'formSize' => ['required', 'string', 'max:10'],
            'formMonthlyRate' => ['required', 'numeric', 'min:0'],
            'formStatus' => ['required', Rule::in(array_column(StallStatus::cases(), 'value'))],
            'formVendorId' => ['nullable', 'exists:vendors,id'],
        ]);

        // If vendor is assigned, status must be occupied; if unassigned, can't be occupied
        $status = $this->formStatus;
        if ($this->formVendorId) {
            $status = 'occupied';
        } elseif ($status === 'occupied') {
            $status = 'available';
        }

        $data = [
            'stall_number' => $this->formStallNumber,
            'section' => $this->formSection,
            'size' => $this->formSize,
            'monthly_rate' => $this->formMonthlyRate,
            'status' => $status,
            'vendor_id' => $this->formVendorId ?: null,
        ];

        if ($this->editingStallId) {
            $stall = Stall::where('market_id', $this->marketId)->findOrFail($this->editingStallId);
            $stall->update($data);
            session()->flash('message', 'Stall updated successfully.');
        } else {
            Stall::create(array_merge($data, ['market_id' => $this->marketId]));
            session()->flash('message', 'Stall created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function confirmDelete(int $stallId): void
    {
        $stall = Stall::where('market_id', $this->marketId)->findOrFail($stallId);
        $this->deletingStallId = $stall->id;
        $this->deletingStallNumber = $stall->stall_number;
        $this->showDeleteModal = true;
    }

    public function deleteStall(): void
    {
        $stall = Stall::where('market_id', $this->marketId)->findOrFail($this->deletingStallId);
        $stall->delete();

        $this->showDeleteModal = false;
        $this->deletingStallId = null;
        $this->deletingStallNumber = '';
        session()->flash('message', 'Stall deleted successfully.');
        $this->clearCache();
    }

    private function resetForm(): void
    {
        $this->editingStallId = null;
        $this->formStallNumber = '';
        $this->formSection = 'A';
        $this->formSize = '3x3m';
        $this->formMonthlyRate = '3500';
        $this->formStatus = 'available';
        $this->formVendorId = null;
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->stalls, $this->stallMap, $this->totalStalls, $this->occupiedCount, $this->availableCount, $this->maintenanceCount, $this->availableVendors, $this->sections);
    }

    public function render()
    {
        return $this->view()->title(__('Stall Allocation'));
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
                <flux:heading size="xl">{{ __('Stall Allocation') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Visual stall mapping, real-time availability, and assignment management.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Add Stall') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Stalls') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalStalls }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Occupied') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->occupiedCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Available') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-blue-600">{{ $this->availableCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Under Maintenance') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->maintenanceCount }}</flux:heading>
            </div>
        </div>

        {{-- Stall Map Visual --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stall Map') }}</flux:heading>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-emerald-500"></span> {{ __('Occupied') }}</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-blue-500"></span> {{ __('Available') }}</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-amber-500"></span> {{ __('Maintenance') }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @foreach($this->stallMap as $sectionKey => $sectionStalls)
                <div class="mb-6 last:mb-0">
                    <flux:text class="mb-2 text-sm font-semibold">{{ __('Section :section', ['section' => $sectionKey]) }}</flux:text>
                    <div class="grid grid-cols-10 gap-2 sm:grid-cols-20">
                        @foreach($sectionStalls as $stall)
                        @php
                            $bgColor = match($stall['status']) {
                                'occupied' => 'bg-emerald-500/80 hover:bg-emerald-500',
                                'available' => 'bg-blue-500/80 hover:bg-blue-500',
                                'maintenance' => 'bg-amber-500/80 hover:bg-amber-500',
                                default => 'bg-zinc-400',
                            };
                            $label = $stall['no'] . ' - ' . ucfirst($stall['status']);
                            if ($stall['vendor']) {
                                $label .= ' (' . $stall['vendor'] . ')';
                            }
                        @endphp
                        <flux:tooltip :content="$label">
                            <div class="flex aspect-square items-center justify-center rounded {{ $bgColor }} cursor-pointer text-xs font-medium text-white transition-colors" wire:click="openEditModal({{ $stall['id'] }})">
                                {{ str_replace($sectionKey . '-', '', $stall['no']) }}
                            </div>
                        </flux:tooltip>
                        @endforeach
                    </div>
                </div>
                @endforeach

                @if(empty($this->stallMap))
                <div class="py-8 text-center text-zinc-500">
                    {{ __('No stalls have been created yet. Click "Add Stall" to get started.') }}
                </div>
                @endif
            </div>
        </div>

        {{-- Stalls Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stall Directory') }}</flux:heading>
                    <div class="flex gap-2">
                        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Search stalls...') }}" />
                        <flux:select wire:model.live="sectionFilter" size="sm" class="w-32">
                            <flux:select.option value="all">{{ __('All') }}</flux:select.option>
                            @foreach($this->sections as $sec)
                            <flux:select.option :value="$sec">{{ __('Section :s', ['s' => $sec]) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall No.') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Size') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Assigned Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Monthly Rate') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->stalls as $stall)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="stall-{{ $stall->id }}">
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->stall_number }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ __('Section :s', ['s' => $stall->section]) }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall->size }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$stall->status->color()" size="sm">{{ $stall->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall->vendor?->contact_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">₱ {{ number_format($stall->monthly_rate, 0) }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $stall->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $stall->id }})">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No stalls found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->stalls->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingStallId ? __('Edit Stall') : __('Add Stall') }}</flux:heading>
                <flux:subheading>{{ $editingStallId ? __('Update stall details and assignment.') : __('Create a new stall.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formStallNumber" :label="__('Stall Number')" type="text" required placeholder="e.g. A-01" />
                    <flux:input wire:model="formSection" :label="__('Section')" type="text" required placeholder="e.g. A" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formSize" :label="__('Size')" type="text" required placeholder="e.g. 3x3m" />
                    <flux:input wire:model="formMonthlyRate" :label="__('Monthly Rate (₱)')" type="number" required step="0.01" />
                </div>
                <flux:select wire:model.live="formStatus" :label="__('Status')">
                    <flux:select.option value="available">{{ __('Available') }}</flux:select.option>
                    <flux:select.option value="occupied">{{ __('Occupied') }}</flux:select.option>
                    <flux:select.option value="maintenance">{{ __('Maintenance') }}</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="formVendorId" :label="__('Assign Vendor')">
                    <flux:select.option :value="null">{{ __('— Unassigned —') }}</flux:select.option>
                    @foreach($this->availableVendors as $vendor)
                    <flux:select.option :value="$vendor->id">{{ $vendor->contact_name }} — {{ $vendor->business_name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingStallId ? __('Update Stall') : __('Create Stall') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Stall') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete stall :number?', ['number' => $deletingStallNumber]) }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteStall">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
