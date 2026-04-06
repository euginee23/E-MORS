<?php

use App\Mail\StallAssigned;
use App\Models\Stall;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    // Assign stall modal
    public bool $showAssignModal = false;
    public ?int $assigningVendorId = null;
    public string $assigningVendorName = '';
    public ?int $selectedStallId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function applications()
    {
        return Vendor::where('market_id', $this->marketId)
            ->where('permit_status', 'pending')
            ->with(['user', 'stall'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('business_name', 'like', '%' . $this->search . '%')
                   ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                   ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%' . $this->search . '%'))
            ))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Vendor::where('market_id', $this->marketId)
            ->where('permit_status', 'pending')
            ->count();
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
            ->where('permit_status', 'pending')
            ->with(['user'])
            ->findOrFail($this->assigningVendorId);

        $stall = Stall::where('market_id', $this->marketId)
            ->where('status', 'available')
            ->findOrFail($this->selectedStallId);

        // Assign
        $stall->update([
            'vendor_id' => $vendor->id,
            'status' => 'occupied',
        ]);

        $vendor->update(['permit_status' => 'active']);

        // Send stall assigned email
        if ($vendor->user) {
            $stall->load('market');
            Mail::to($vendor->user->email)->send(
                new StallAssigned($vendor->user, $vendor, $stall)
            );
        }

        $this->showAssignModal = false;
        $this->assigningVendorId = null;
        $this->assigningVendorName = '';
        $this->selectedStallId = null;

        session()->flash('message', "Stall {$stall->stall_number} assigned to {$vendor->contact_name} successfully.");
        $this->clearCache();
    }

    private function clearCache(): void
    {
        unset($this->applications, $this->pendingCount, $this->availableStalls);
    }

    public function render()
    {
        return $this->view()->title(__('Vendor Applications'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Flash --}}
        @if(session('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('message') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vendor Applications') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Review and assign stalls to pending vendor applicants.') }}</flux:subheading>
            </div>
            @if($this->pendingCount > 0)
            <flux:badge color="yellow" size="lg">{{ $this->pendingCount }} pending</flux:badge>
            @endif
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Applications') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->pendingCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Available Stalls') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-sky-600">{{ $this->availableStalls->count() }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Can Assign Now') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">
                    {{ min($this->pendingCount, $this->availableStalls->count()) }}
                </flux:heading>
            </div>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by name, business, or email…') }}" />
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Applicant') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Business') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Product Type') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Address') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Applied') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->applications as $vendor)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="app-{{ $vendor->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$vendor->contact_name" />
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor->contact_name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $vendor->user?->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->business_name }}</td>
                            <td class="px-6 py-3">
                                @if($vendor->product_type)
                                <flux:badge color="orange" size="sm">{{ $vendor->product_type }}</flux:badge>
                                @else
                                <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->contact_phone ?? '—' }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300 max-w-48">
                                <span title="{{ $vendor->address }}">{{ Str::limit($vendor->address, 40) ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400">{{ $vendor->created_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                <flux:button size="sm" variant="primary" wire:click="openAssignModal({{ $vendor->id }})">
                                    {{ __('Assign Stall') }}
                                </flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-zinc-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm">{{ __('No pending applications.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-zinc-700">
                {{ $this->applications->links() }}
            </div>
        </div>
    </div>

    {{-- Assign Stall Modal --}}
    <flux:modal wire:model="showAssignModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Assign a Stall') }}</flux:heading>
                <flux:subheading>{{ __('Assign an available stall to :name. They will receive an email notification.', ['name' => $assigningVendorName]) }}</flux:subheading>
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
                    <p class="mt-2 text-sm text-amber-600 dark:text-amber-400">
                        No available stalls at the moment. Update a stall's status to "Available" first.
                    </p>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showAssignModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" :disabled="$this->availableStalls->isEmpty()">
                        {{ __('Assign & Notify') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
