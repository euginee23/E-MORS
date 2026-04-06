<?php

use App\Enums\PermitStatus;
use App\Mail\StallAssigned;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingVendorId = null;
    public string $formBusinessName = '';
    public string $formContactName = '';
    public string $formContactPhone = '';
    public string $formPermitNumber = '';
    public string $formPermitStatus = 'pending';
    public ?string $formPermitExpiry = null;

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deletingVendorId = null;
    public string $deletingVendorName = '';

    // Assign stall
    public bool $showAssignModal = false;
    public ?int $assigningVendorId = null;
    public string $assigningVendorName = '';
    public ?int $selectedStallId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function vendors()
    {
        return Vendor::where('market_id', $this->marketId)
            ->with(['stall', 'user'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('business_name', 'like', '%' . $this->search . '%')
                   ->orWhere('contact_name', 'like', '%' . $this->search . '%')
            ))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('permit_status', $this->statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function totalVendors(): int
    {
        return Vendor::where('market_id', $this->marketId)->count();
    }

    #[Computed]
    public function activeCount(): int
    {
        return Vendor::where('market_id', $this->marketId)->where('permit_status', PermitStatus::Active)->count();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Vendor::where('market_id', $this->marketId)->where('permit_status', PermitStatus::Pending)->count();
    }

    #[Computed]
    public function expiredCount(): int
    {
        return Vendor::where('market_id', $this->marketId)->where('permit_status', PermitStatus::Expired)->count();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $vendorId): void
    {
        $vendor = Vendor::where('market_id', $this->marketId)->findOrFail($vendorId);
        $this->editingVendorId = $vendor->id;
        $this->formBusinessName = $vendor->business_name;
        $this->formContactName = $vendor->contact_name;
        $this->formContactPhone = $vendor->contact_phone ?? '';
        $this->formPermitNumber = $vendor->permit_number ?? '';
        $this->formPermitStatus = $vendor->permit_status->value;
        $this->formPermitExpiry = $vendor->permit_expiry?->format('Y-m-d');
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'formBusinessName' => ['required', 'string', 'max:255'],
            'formContactName' => ['required', 'string', 'max:255'],
            'formContactPhone' => ['nullable', 'string', 'max:20'],
            'formPermitNumber' => ['nullable', 'string', 'max:50'],
            'formPermitStatus' => ['required', Rule::in(array_column(PermitStatus::cases(), 'value'))],
            'formPermitExpiry' => ['nullable', 'date'],
        ]);

        $data = [
            'business_name' => $this->formBusinessName,
            'contact_name' => $this->formContactName,
            'contact_phone' => $this->formContactPhone ?: null,
            'permit_number' => $this->formPermitNumber ?: null,
            'permit_status' => $this->formPermitStatus,
            'permit_expiry' => $this->formPermitExpiry ?: null,
        ];

        if ($this->editingVendorId) {
            $vendor = Vendor::where('market_id', $this->marketId)->findOrFail($this->editingVendorId);
            $vendor->update($data);
            session()->flash('message', 'Vendor updated successfully.');
        } else {
            Vendor::create(array_merge($data, ['market_id' => $this->marketId]));
            session()->flash('message', 'Vendor created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function confirmDelete(int $vendorId): void
    {
        $vendor = Vendor::where('market_id', $this->marketId)->findOrFail($vendorId);
        $this->deletingVendorId = $vendor->id;
        $this->deletingVendorName = $vendor->contact_name;
        $this->showDeleteModal = true;
    }

    public function deleteVendor(): void
    {
        $vendor = Vendor::where('market_id', $this->marketId)->findOrFail($this->deletingVendorId);

        // Unassign any stall
        if ($vendor->stall) {
            $vendor->stall->update(['vendor_id' => null, 'status' => 'available']);
        }

        $vendor->delete();

        $this->showDeleteModal = false;
        $this->deletingVendorId = null;
        $this->deletingVendorName = '';
        session()->flash('message', 'Vendor deleted successfully.');
        $this->clearCache();
    }

    private function resetForm(): void
    {
        $this->editingVendorId = null;
        $this->formBusinessName = '';
        $this->formContactName = '';
        $this->formContactPhone = '';
        $this->formPermitNumber = '';
        $this->formPermitStatus = 'pending';
        $this->formPermitExpiry = null;
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->vendors, $this->totalVendors, $this->activeCount, $this->pendingCount, $this->expiredCount);
    }

    #[Computed]
    public function availableStalls()
    {
        return Stall::where('market_id', $this->marketId)
            ->where('status', 'available')
            ->orderBy('section')
            ->orderBy('stall_number')
            ->get();
    }

    public function openAssignModal(int $vendorId): void
    {
        $vendor = Vendor::where('market_id', $this->marketId)->findOrFail($vendorId);
        $this->assigningVendorId = $vendor->id;
        $this->assigningVendorName = $vendor->contact_name;
        $this->selectedStallId = null;
        $this->showAssignModal = true;
    }

    public function assignStall(): void
    {
        $this->validate([
            'selectedStallId' => ['required', 'integer'],
        ], [
            'selectedStallId.required' => 'Please select a stall.',
        ]);

        $vendor = Vendor::where('market_id', $this->marketId)
            ->with('user')
            ->findOrFail($this->assigningVendorId);

        $stall = Stall::where('market_id', $this->marketId)
            ->where('status', 'available')
            ->findOrFail($this->selectedStallId);

        $stall->update(['vendor_id' => $vendor->id, 'status' => 'occupied']);
        $vendor->update(['permit_status' => 'active']);

        if ($vendor->user) {
            $stall->load('market');
            Mail::to($vendor->user->email)->send(new StallAssigned($vendor->user, $vendor, $stall));
        }

        $this->showAssignModal = false;
        $this->assigningVendorId = null;
        $this->assigningVendorName = '';
        $this->selectedStallId = null;

        session()->flash('message', "Stall {$stall->stall_number} assigned to {$vendor->contact_name} successfully.");
        unset($this->availableStalls);
        $this->clearCache();
    }

    public function render()
    {
        return $this->view()->title(__('Vendor Management'));
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
                <flux:heading size="xl">{{ __('Vendor Management') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage vendor registrations, permits, and renewals.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Add Vendor') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Vendors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalVendors }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Active') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->activeCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Renewal') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->pendingCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Expired') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">{{ $this->expiredCount }}</flux:heading>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search vendors by name or business...') }}" />
            </div>
            <flux:select wire:model.live="statusFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Vendors Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Business') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Permit Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Permit Expiry') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->vendors as $vendor)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="vendor-{{ $vendor->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$vendor->contact_name" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor->contact_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->business_name }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->stall?->stall_number ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$vendor->permit_status->color()" size="sm">{{ $vendor->permit_status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->permit_expiry?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $vendor->id }})">{{ __('Edit') }}</flux:menu.item>
                                        @if($vendor->permit_status === \App\Enums\PermitStatus::Pending && !$vendor->stall)
                                        <flux:menu.item icon="building-storefront" wire:click="openAssignModal({{ $vendor->id }})">{{ __('Assign Stall') }}</flux:menu.item>
                                        @endif
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $vendor->id }})">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No vendors found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->vendors->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingVendorId ? __('Edit Vendor') : __('Add Vendor') }}</flux:heading>
                <flux:subheading>{{ $editingVendorId ? __('Update vendor information.') : __('Register a new vendor.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="formBusinessName" :label="__('Business Name')" type="text" required />
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formContactName" :label="__('Contact Name')" type="text" required />
                    <flux:input wire:model="formContactPhone" :label="__('Contact Phone')" type="text" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formPermitNumber" :label="__('Permit Number')" type="text" />
                    <flux:select wire:model="formPermitStatus" :label="__('Permit Status')">
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                    </flux:select>
                </div>
                <flux:input wire:model="formPermitExpiry" :label="__('Permit Expiry Date')" type="date" />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingVendorId ? __('Update Vendor') : __('Create Vendor') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Vendor') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete :name? This will also unassign their stall.', ['name' => $deletingVendorName]) }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteVendor">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Assign Stall Modal --}}
    <flux:modal wire:model="showAssignModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Assign a Stall') }}</flux:heading>
                <flux:subheading>{{ __('Assign an available stall to :name.', ['name' => $assigningVendorName]) }}</flux:subheading>
            </div>
            <form wire:submit="assignStall" class="space-y-4">
                <div>
                    <flux:select wire:model="selectedStallId" :label="__('Available Stalls')">
                        <flux:select.option value="">{{ __('Select a stall…') }}</flux:select.option>
                        @foreach($this->availableStalls as $stall)
                        <flux:select.option :value="$stall->id">
                            {{ $stall->stall_number }} — {{ $stall->section }} ({{ $stall->size }}, ₱{{ number_format($stall->monthly_rate, 2) }}/mo)
                        </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('selectedStallId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @if($this->availableStalls->isEmpty())
                    <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">No available stalls at the moment.</p>
                    @endif
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showAssignModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" :disabled="$this->availableStalls->isEmpty()">{{ __('Assign & Notify') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
