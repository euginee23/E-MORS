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



    // Inline map editing
    public bool $showAddSection = false;
    public string $newSectionLetter = '';
    public ?string $addingStallSection = null;
    public string $inlineStallNumber = '';
    public string $inlineSize = '3x3m';
    public string $inlineRate = '3500';

    // Section editing
    public ?string $editingSection = null;
    public string $editingSectionName = '';

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
            $this->dispatch('toast', message: 'Stall updated successfully.', type: 'success');
        } else {
            Stall::create(array_merge($data, ['market_id' => $this->marketId]));
            $this->dispatch('toast', message: 'Stall created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function deleteStall(int $stallId): void
    {
        Stall::where('market_id', $this->marketId)->findOrFail($stallId)->delete();
        $this->dispatch('toast', message: 'Stall deleted successfully.', type: 'success');
        $this->clearCache();
    }

    public function unassignVendor(int $stallId): void
    {
        $stall = Stall::where('market_id', $this->marketId)->findOrFail($stallId);
        $stall->update(['vendor_id' => null, 'status' => 'available']);
        $this->dispatch('toast', message: 'Vendor unassigned from stall ' . $stall->stall_number . '.', type: 'success');
        $this->clearCache();
    }

    public function startEditSection(string $section): void
    {
        $this->editingSection = $section;
        $this->editingSectionName = $section;
        $this->resetValidation();
    }

    public function saveSection(): void
    {
        $this->validate(['editingSectionName' => ['required', 'alpha', 'max:5']]);
        $old = $this->editingSection;
        $new = strtoupper(trim($this->editingSectionName));

        if ($old === $new) {
            $this->editingSection = null;
            return;
        }

        if (array_key_exists($new, $this->stallMap)) {
            $this->addError('editingSectionName', 'Section ' . $new . ' already exists.');
            return;
        }

        Stall::where('market_id', $this->marketId)
            ->where('section', $old)
            ->get()
            ->each(function ($stall) use ($old, $new) {
                $newNumber = str_replace($old . '-', $new . '-', $stall->stall_number);
                $stall->update(['section' => $new, 'stall_number' => $newNumber]);
            });

        $this->editingSection = null;
        $this->editingSectionName = '';
        $this->dispatch('toast', message: 'Section renamed to ' . $new . '.', type: 'success');
        $this->clearCache();
    }

    public function cancelEditSection(): void
    {
        $this->editingSection = null;
        $this->editingSectionName = '';
        $this->resetValidation();
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

    public function generateSampleMap(): void
    {
        $marketId = $this->marketId;

        if (! $marketId) {
            $this->dispatch('toast', message: 'No market assigned to your account.', type: 'error');
            return;
        }

        $sections = [
            'A' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3000.00],
            'B' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'C' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'D' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 2500.00],
        ];

        $maintenanceStalls = ['A-07', 'B-05', 'C-10', 'D-03'];

        foreach ($sections as $section => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                $stallNumber = $section . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                $status = in_array($stallNumber, $maintenanceStalls)
                    ? StallStatus::Maintenance
                    : StallStatus::Available;

                Stall::updateOrCreate(
                    [
                        'market_id'    => $marketId,
                        'stall_number' => $stallNumber,
                    ],
                    [
                        'vendor_id'    => null,
                        'section'      => $section,
                        'size'         => $config['defaultSize'],
                        'monthly_rate' => $config['defaultRate'],
                        'status'       => $status,
                    ],
                );
            }
        }

        $this->dispatch('toast', message: 'Sample stall map generated successfully.', type: 'success');
        $this->clearCache();
    }

    public function openAddSection(): void
    {
        $this->showAddSection = true;
        $this->newSectionLetter = '';
        $this->addingStallSection = null;
        $this->resetValidation();
    }

    public function createSection(): void
    {
        $this->validate(['newSectionLetter' => ['required', 'alpha', 'max:5']]);
        $letter = strtoupper(trim($this->newSectionLetter));

        if (array_key_exists($letter, $this->stallMap)) {
            $this->addError('newSectionLetter', 'Section already exists.');
            return;
        }

        $this->showAddSection = false;
        $this->newSectionLetter = '';
        $this->openInlineAddStall($letter);
    }

    public function openInlineAddStall(string $section): void
    {
        $this->addingStallSection = $section;
        $existing = $this->stallMap[$section] ?? [];
        $nextNum = count($existing) + 1;
        $this->inlineStallNumber = $section . '-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
        $this->inlineSize = '3x3m';
        $this->inlineRate = '3500';
        $this->showAddSection = false;
        $this->resetValidation();
    }

    public function cancelInlineAdd(): void
    {
        $this->addingStallSection = null;
        $this->inlineStallNumber = '';
        $this->showAddSection = false;
        $this->newSectionLetter = '';
        $this->resetValidation();
    }

    public function quickSaveStall(): void
    {
        if (!$this->addingStallSection) {
            return;
        }

        $this->validate([
            'inlineStallNumber' => [
                'required', 'string', 'max:10',
                Rule::unique('stalls', 'stall_number')->where('market_id', $this->marketId),
            ],
            'inlineSize' => ['required', 'string', 'max:10'],
            'inlineRate' => ['required', 'numeric', 'min:0'],
        ]);

        Stall::create([
            'stall_number' => $this->inlineStallNumber,
            'section'      => $this->addingStallSection,
            'size'         => $this->inlineSize,
            'monthly_rate' => $this->inlineRate,
            'status'       => 'available',
            'vendor_id'    => null,
            'market_id'    => $this->marketId,
        ]);

        $section = $this->addingStallSection;
        $this->clearCache();
        $this->cancelInlineAdd();
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
            <div class="relative p-6">
                <div wire:loading.flex wire:target="save,deleteStall,quickSaveStall,saveSection,generateSampleMap,createSection,unassignVendor" class="pointer-events-none absolute inset-0 z-30 hidden items-center justify-center rounded-b-2xl bg-white/70 dark:bg-zinc-900/70">
                    <div class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm font-medium text-orange-700 shadow-sm dark:border-orange-700/60 dark:bg-zinc-800 dark:text-orange-300">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        {{ __('Updating stall map...') }}
                    </div>
                </div>
                @foreach($this->stallMap as $sectionKey => $sectionStalls)
                <div class="mb-6" wire:key="section-{{ $sectionKey }}">
                    {{-- Section header with inline rename --}}
                    @if($editingSection === $sectionKey)
                    <div class="mb-2 flex items-center gap-2">
                        <input wire:model="editingSectionName" wire:keydown.enter="saveSection" wire:keydown.escape="cancelEditSection" type="text" maxlength="5" class="w-32 rounded-lg border border-orange-400 bg-white px-3 py-1.5 text-sm font-semibold uppercase tracking-wider shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:bg-zinc-800 dark:text-zinc-200" />
                        @error('editingSectionName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        <button type="button" wire:click="saveSection" wire:loading.attr="disabled" wire:target="saveSection" class="cursor-pointer rounded-md bg-orange-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-70">
                            <span wire:loading.remove wire:target="saveSection">{{ __('Save') }}</span>
                            <span wire:loading wire:target="saveSection" class="inline-flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                {{ __('Saving...') }}
                            </span>
                        </button>
                        <button type="button" wire:click="cancelEditSection" wire:loading.attr="disabled" wire:target="saveSection" class="cursor-pointer rounded-md border border-zinc-300 px-3 py-1.5 text-xs text-zinc-500 hover:bg-zinc-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:hover:bg-zinc-800">{{ __('Cancel') }}</button>
                    </div>
                    @else
                    <div class="mb-2 flex items-center gap-1.5">
                        <flux:text class="text-sm font-semibold">{{ __('Section :section', ['section' => $sectionKey]) }}</flux:text>
                        <button wire:click="startEditSection('{{ $sectionKey }}')" class="cursor-pointer rounded p-0.5 text-zinc-400 transition-colors hover:text-orange-500">
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    </div>
                    @endif
                    <div class="grid gap-2" style="grid-template-columns: repeat({{ count($sectionStalls) + ($addingStallSection === $sectionKey ? 0 : 1) }}, minmax(0, 3.5rem))">
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
                        <div x-data="{ open: false }" class="relative" wire:key="stall-{{ $stall['id'] }}">
                            <button
                                @click="open = !open"
                                @click.outside="open = false"
                                title="{{ $label }}"
                                class="flex aspect-square w-full items-center justify-center rounded {{ $bgColor }} cursor-pointer text-xs font-medium text-white transition-colors select-none"
                            >
                                {{ str_replace($sectionKey . '-', '', $stall['no']) }}
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 top-full z-50 mt-1 min-w-[170px] rounded-xl border border-zinc-200 bg-white py-1 shadow-xl dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <p class="border-b border-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">{{ $stall['no'] }}</p>
                                <button
                                    @click="open = false"
                                    wire:click="openEditModal({{ $stall['id'] }})"
                                    class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700/50"
                                >
                                    <svg class="size-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ __('Edit stall') }}
                                </button>
                                @if($stall['vendor'])
                                <button
                                    @click="open = false"
                                    wire:click="unassignVendor({{ $stall['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="unassignVendor"
                                    class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-700/50"
                                >
                                    <svg class="size-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zm5-7l5 5m0-5l-5 5"/></svg>
                                    <span wire:loading.remove wire:target="unassignVendor">{{ __('Unassign vendor') }}</span>
                                    <span wire:loading wire:target="unassignVendor" class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        {{ __('Unassigning...') }}
                                    </span>
                                </button>
                                @endif
                                <div class="my-1 border-t border-zinc-100 dark:border-zinc-700"></div>
                                <button
                                    x-on:click="open = false; $dispatch('open-confirm', { title: 'Delete Stall', message: 'Are you sure you want to delete stall {{ $stall['no'] }}? This cannot be undone.', confirm: 'Delete', variant: 'danger', onConfirm: () => $wire.deleteStall({{ $stall['id'] }}) })"
                                    class="flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                >
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    {{ __('Remove stall') }}
                                </button>
                            </div>
                        </div>
                        @endforeach

                        {{-- + stall box --}}
                        @if($addingStallSection !== $sectionKey)
                        <flux:tooltip :content="__('Add stall to Section :s', ['s' => $sectionKey])">
                            <button wire:click="openInlineAddStall('{{ $sectionKey }}')" wire:loading.attr="disabled" wire:target="openInlineAddStall" class="flex aspect-square w-full cursor-pointer items-center justify-center rounded border-2 border-dashed border-zinc-300 text-lg font-light text-zinc-400 transition-colors hover:border-orange-400 hover:text-orange-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600">
                                <span wire:loading.remove wire:target="openInlineAddStall">+</span>
                                <svg wire:loading wire:target="openInlineAddStall" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </button>
                        </flux:tooltip>
                        @endif
                    </div>

                    {{-- Inline add-stall form --}}
                    @if($addingStallSection === $sectionKey)
                    <div class="mt-3 rounded-xl border-2 border-orange-300 bg-orange-50 p-5 dark:border-orange-700/60 dark:bg-orange-900/20">
                        <p class="mb-4 text-sm font-semibold text-orange-700 dark:text-orange-400">{{ __('Add stall to Section :s', ['s' => $sectionKey]) }}</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_1fr_1fr_auto]">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Stall No.') }}</label>
                                <input wire:model="inlineStallNumber" wire:keydown.enter="quickSaveStall" type="text" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="{{ $sectionKey }}-01" />
                                @error('inlineStallNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Size') }}</label>
                                <input wire:model="inlineSize" type="text" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="3x3m" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Monthly Rate (₱)') }}</label>
                                <input wire:model="inlineRate" type="number" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="3500" />
                            </div>
                            <div class="flex items-end gap-2">
                                <button wire:click="quickSaveStall" wire:loading.attr="disabled" wire:target="quickSaveStall" class="cursor-pointer rounded-lg bg-orange-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="quickSaveStall">{{ __('Add') }}</span>
                                    <span wire:loading wire:target="quickSaveStall" class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        {{ __('Adding...') }}
                                    </span>
                                </button>
                                <button wire:click="cancelInlineAdd" wire:loading.attr="disabled" wire:target="quickSaveStall" class="cursor-pointer rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-600 shadow-sm transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- New section row (not yet in stallMap) --}}
                @if($addingStallSection && !array_key_exists($addingStallSection, $this->stallMap))
                <div class="mb-6" wire:key="section-new-{{ $addingStallSection }}">
                    <flux:text class="mb-2 text-sm font-semibold">{{ __('Section :section', ['section' => $addingStallSection]) }}</flux:text>
                    <div class="rounded-xl border-2 border-orange-300 bg-orange-50 p-5 dark:border-orange-700/60 dark:bg-orange-900/20">
                        <p class="mb-4 text-sm font-semibold text-orange-700 dark:text-orange-400">{{ __('Add first stall to Section :s', ['s' => $addingStallSection]) }}</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_1fr_1fr_auto]">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Stall No.') }}</label>
                                <input wire:model="inlineStallNumber" wire:keydown.enter="quickSaveStall" type="text" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="{{ $addingStallSection }}-01" />
                                @error('inlineStallNumber') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Size') }}</label>
                                <input wire:model="inlineSize" type="text" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="3x3m" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Monthly Rate (₱)') }}</label>
                                <input wire:model="inlineRate" type="number" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="3500" />
                            </div>
                            <div class="flex items-end gap-2">
                                <button wire:click="quickSaveStall" wire:loading.attr="disabled" wire:target="quickSaveStall" class="cursor-pointer rounded-lg bg-orange-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="quickSaveStall">{{ __('Add') }}</span>
                                    <span wire:loading wire:target="quickSaveStall" class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        {{ __('Adding...') }}
                                    </span>
                                </button>
                                <button wire:click="cancelInlineAdd" wire:loading.attr="disabled" wire:target="quickSaveStall" class="cursor-pointer rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-600 shadow-sm transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(empty($this->stallMap) && !$addingStallSection)
                <div class="flex flex-col items-center justify-center gap-6 py-16 text-center">
                    <div class="flex size-20 items-center justify-center rounded-2xl bg-orange-50 dark:bg-orange-900/20">
                        <svg class="size-10 text-orange-300 dark:text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    </div>
                    <div>
                        <p class="text-base font-semibold text-zinc-700 dark:text-zinc-200">{{ __('No stall map yet') }}</p>
                        <p class="mt-1 text-sm text-zinc-400">{{ __('Add your first section to start building the map, or generate a sample layout.') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <button wire:click="openAddSection" class="cursor-pointer flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-orange-600">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Add a Section') }}
                        </button>
                        <button wire:click="generateSampleMap" wire:loading.attr="disabled" wire:target="generateSampleMap" class="cursor-pointer flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-600 shadow-sm transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span wire:loading.remove wire:target="generateSampleMap">{{ __('Generate Sample Map') }}</span>
                            <span wire:loading wire:target="generateSampleMap" class="inline-flex items-center gap-1.5">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                {{ __('Generating...') }}
                            </span>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Add Section bar (only when map has sections already) --}}
                @if(!empty($this->stallMap))
                <div class="mt-4 border-t border-orange-100 pt-4 dark:border-zinc-700">
                    @if($showAddSection)
                    <div class="rounded-xl border-2 border-orange-300 bg-orange-50 p-5 dark:border-orange-700/60 dark:bg-orange-900/20">
                        <p class="mb-4 text-sm font-semibold text-orange-700 dark:text-orange-400">{{ __('New Section') }}</p>
                        <div class="flex flex-wrap items-end gap-4">
                            <div class="flex flex-col gap-1.5 flex-1 max-w-xs">
                                <label class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Section letter') }}</label>
                                <input wire:model="newSectionLetter" wire:keydown.enter="createSection" type="text" maxlength="5" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm uppercase tracking-widest shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200" placeholder="E" />
                                @error('newSectionLetter') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div class="flex items-end gap-2">
                                <button wire:click="createSection" wire:loading.attr="disabled" wire:target="createSection" class="cursor-pointer rounded-lg bg-orange-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-70">
                                    <span wire:loading.remove wire:target="createSection">{{ __('Create') }}</span>
                                    <span wire:loading wire:target="createSection" class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                        </svg>
                                        {{ __('Creating...') }}
                                    </span>
                                </button>
                                <button wire:click="cancelInlineAdd" wire:loading.attr="disabled" wire:target="createSection" class="cursor-pointer rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm text-zinc-600 shadow-sm transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </div>
                    @else
                    <button wire:click="openAddSection" class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-zinc-300 py-3 text-sm font-medium text-zinc-400 transition-colors hover:border-orange-400 hover:text-orange-500 dark:border-zinc-600 dark:hover:border-orange-600">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add Section') }}
                    </button>
                    @endif
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
                        <div wire:loading.inline-flex class="hidden items-center gap-1.5 rounded-md border border-orange-200 bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 dark:border-orange-700/60 dark:bg-orange-900/30 dark:text-orange-300">
                            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ __('Loading...') }}
                        </div>
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
            <div class="relative overflow-x-auto">
                <div wire:loading.flex class="pointer-events-none absolute inset-0 z-20 hidden items-center justify-center bg-white/60 dark:bg-zinc-900/60">
                    <div class="inline-flex items-center gap-2 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm font-medium text-orange-700 shadow-sm dark:border-orange-700/60 dark:bg-zinc-800 dark:text-orange-300">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        {{ __('Loading stalls...') }}
                    </div>
                </div>
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
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $stall->id }})" wire:loading.attr="disabled" wire:target="openEditModal">
                                            <span wire:loading.remove wire:target="openEditModal">{{ __('Edit') }}</span>
                                            <span wire:loading wire:target="openEditModal" class="inline-flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                                </svg>
                                                {{ __('Opening...') }}
                                            </span>
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" x-on:click="$dispatch('open-confirm', { title: 'Delete Stall', message: 'Are you sure you want to delete stall {{ $stall->stall_number }}? This cannot be undone.', confirm: 'Delete', variant: 'danger', onConfirm: () => $wire.deleteStall({{ $stall->id }}) })">{{ __('Delete') }}</flux:menu.item>
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

    {{-- Create/Edit Modal — two-panel layout --}}
    <flux:modal wire:model="showModal" class="w-full max-w-4xl !p-0 overflow-hidden">
        <form wire:submit="save" class="flex min-h-[520px]">

            {{-- LEFT PANEL — Stall details --}}
            <div class="flex flex-col w-full sm:w-5/12 border-r border-zinc-100 dark:border-zinc-700 p-6 gap-5">
                <div>
                    <flux:heading size="lg">{{ $editingStallId ? __('Edit Stall') : __('Add Stall') }}</flux:heading>
                    <flux:subheading class="mt-0.5">{{ __('Stall details') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-2 gap-3 flex-1">
                    <flux:input wire:model="formStallNumber" :label="__('Stall No.')" type="text" required placeholder="A-01" />
                    <flux:input wire:model="formSection" :label="__('Section')" type="text" required placeholder="A" />
                    <flux:input wire:model="formSize" :label="__('Size')" type="text" required placeholder="3x3m" />
                    <flux:input wire:model="formMonthlyRate" :label="__('Rate (₱)')" type="number" required step="0.01" />
                </div>

                <flux:select wire:model.live="formStatus" :label="__('Status')">
                    <flux:select.option value="available">{{ __('Available') }}</flux:select.option>
                    <flux:select.option value="occupied">{{ __('Occupied') }}</flux:select.option>
                    <flux:select.option value="maintenance">{{ __('Maintenance') }}</flux:select.option>
                </flux:select>

                {{-- Currently assigned preview --}}
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-3 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 mb-2">Assigned Vendor</p>
                    @if($formVendorId)
                        @php $sv = $this->availableVendors->firstWhere('id', $formVendorId); @endphp
                        @if($sv)
                            <p class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $sv->contact_name }}</p>
                            <p class="text-zinc-500 dark:text-zinc-400 text-xs">{{ $sv->business_name }} · {{ $sv->product_type }}</p>
                            <p class="text-zinc-500 dark:text-zinc-400 text-xs mt-0.5">{{ $sv->contact_phone }}</p>
                            <div class="mt-1.5">
                                <flux:badge :color="$sv->permit_status->color()" size="sm">{{ $sv->permit_status->label() }}</flux:badge>
                            </div>
                        @endif
                    @else
                        <p class="text-zinc-400 italic text-xs">No vendor assigned</p>
                    @endif
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-700 mt-auto">
                    <flux:button variant="ghost" type="button" wire:click="$set('showModal', false)" wire:loading.attr="disabled" wire:target="save">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ $editingStallId ? __('Update') : __('Create') }}</span>
                        <span wire:loading wire:target="save" class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ $editingStallId ? __('Updating...') : __('Creating...') }}
                        </span>
                    </flux:button>
                </div>
            </div>

            {{-- RIGHT PANEL — Vendor picker --}}
            <div class="flex flex-col w-full sm:w-7/12 bg-zinc-50 dark:bg-zinc-900/50"
                 x-data="{ search: '' }">

                <div class="px-5 pt-5 pb-3 border-b border-zinc-100 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-2">{{ __('Assign Vendor') }}</p>
                    <input
                        x-model="search"
                        type="text"
                        placeholder="Search by name, business, or product…"
                        class="w-full rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-orange-500/30"
                    />
                </div>

                <ul class="flex-1 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-700/60" style="max-height: 420px;">

                    {{-- Unassigned row --}}
                    <li
                        x-show="search === '' || 'unassigned'.includes(search.toLowerCase())"
                        wire:click="$set('formVendorId', null)"
                        class="flex items-center gap-3 px-5 py-3 cursor-pointer hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors {{ $formVendorId === null ? 'bg-orange-50 dark:bg-zinc-800' : '' }}"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-700 shrink-0">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">— Unassigned —</p>
                        </div>
                        @if($formVendorId === null)
                        <svg class="h-4 w-4 text-orange-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </li>

                    @foreach($this->availableVendors as $vendor)
                    <li
                        data-vendor="{{ strtolower($vendor->contact_name . ' ' . $vendor->business_name . ' ' . $vendor->product_type) }}"
                        x-show="search === '' || $el.dataset.vendor.includes(search.toLowerCase())"
                        wire:click="$set('formVendorId', {{ $vendor->id }})"
                        class="flex items-center gap-3 px-5 py-3 cursor-pointer hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors {{ $formVendorId == $vendor->id ? 'bg-orange-50 dark:bg-zinc-800' : '' }}"
                    >
                        {{-- Avatar initials --}}
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-bold shrink-0">
                            {{ strtoupper(substr($vendor->contact_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">{{ $vendor->contact_name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ $vendor->business_name }} · {{ $vendor->product_type }}</p>
                        </div>
                        <flux:badge :color="$vendor->permit_status->color()" size="sm" class="shrink-0">{{ $vendor->permit_status->label() }}</flux:badge>
                        @if($formVendorId == $vendor->id)
                        <svg class="h-4 w-4 text-orange-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </li>
                    @endforeach

                    <li x-show="search !== ''" class="px-5 py-3 text-sm text-zinc-400 italic hidden"
                        x-init="$watch('search', v => { const visible = $el.closest('ul').querySelectorAll('li[data-vendor]'); const any = Array.from(visible).some(el => el.style.display !== 'none'); $el.style.display = (v !== '' && !any) ? 'block' : 'none'; })">
                        No vendors match your search.
                    </li>
                </ul>
            </div>

        </form>
    </flux:modal>


</div>
