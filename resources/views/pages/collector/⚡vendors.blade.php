<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    public string $search = '';
    public string $sectionFilter = 'all';
    public string $statusFilter = 'all';

    public function updatedSearch(): void {}
    public function updatedSectionFilter(): void {}
    public function updatedStatusFilter(): void {}

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function vendors()
    {
        $paidTodayVendorIds = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->pluck('vendor_id')
            ->unique();

        $vendors = Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->with(['stall', 'collections' => fn ($q) => $q->where('status', PaymentStatus::Paid)->latest('payment_date')->limit(1)])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('contact_name', 'like', '%' . $this->search . '%')
                   ->orWhere('business_name', 'like', '%' . $this->search . '%')
            ))
            ->when($this->sectionFilter !== 'all', fn ($q) =>
                $q->whereHas('stall', fn ($q2) => $q2->where('section', $this->sectionFilter))
            )
            ->orderBy('contact_name')
            ->get();

        // Add status attribute for filtering
        $vendors = $vendors->map(function ($vendor) use ($paidTodayVendorIds) {
            $vendor->today_status = $paidTodayVendorIds->contains($vendor->id) ? 'paid' : 'pending';
            $lastCollection = $vendor->collections->first();
            if ($vendor->today_status === 'pending' && $lastCollection && $lastCollection->payment_date->lt(today()->subDays(2))) {
                $vendor->today_status = 'overdue';
            }
            return $vendor;
        });

        if ($this->statusFilter !== 'all') {
            $vendors = $vendors->filter(fn ($v) => $v->today_status === $this->statusFilter);
        }

        return $vendors->values();
    }

    #[Computed]
    public function sections(): array
    {
        return \App\Models\Stall::where('market_id', $this->marketId)
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->toArray();
    }

    #[Computed]
    public function totalAssigned(): int
    {
        return Vendor::where('market_id', $this->marketId)->has('stall')->count();
    }

    #[Computed]
    public function paidTodayCount(): int
    {
        return Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->distinct('vendor_id')
            ->count('vendor_id');
    }

    #[Computed]
    public function pendingTodayCount(): int
    {
        return $this->totalAssigned - $this->paidTodayCount;
    }

    #[Computed]
    public function overdueCount(): int
    {
        $paidTodayIds = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->pluck('vendor_id');

        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->whereNotIn('id', $paidTodayIds)
            ->whereDoesntHave('collections', fn ($q) =>
                $q->where('status', PaymentStatus::Paid)
                  ->where('payment_date', '>=', today()->subDays(2))
            )
            ->count();
    }

    public function render()
    {
        return $this->view()->title(__('Assigned Vendors'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Assigned Vendors') }}</flux:heading>
                <flux:subheading>{{ __('Vendors in your market for collection.') }}</flux:subheading>
            </div>
            <flux:badge color="zinc" size="lg" icon="users">{{ $this->totalAssigned }} vendors</flux:badge>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Assigned') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->totalAssigned }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">across {{ count($this->sections) }} sections</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Paid Today') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->paidTodayCount }}</flux:heading>
                    @if($this->totalAssigned > 0)
                    <flux:text class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">{{ round(($this->paidTodayCount / $this->totalAssigned) * 100) }}% collected</flux:text>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Pending Today') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->pendingTodayCount }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">need collection</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Overdue') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->overdueCount }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-red-600 dark:text-red-400">overdue payments</flux:text>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search vendors..." />
            </div>
            <flux:select wire:model.live="sectionFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Sections') }}</flux:select.option>
                @foreach($this->sections as $section)
                <flux:select.option :value="$section">Section {{ $section }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="statusFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid Today') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Vendors Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Monthly Rate') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Today\'s Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Last Collection') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->vendors as $vendor)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="v-{{ $vendor->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-amber-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-[10px]">{{ collect(explode(' ', $vendor->contact_name))->map(fn($w) => $w[0])->implode('') }}</span>
                                    </div>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor->contact_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor->stall?->stall_number }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">Section {{ $vendor->stall?->section }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($vendor->stall?->monthly_rate, 0) }}</td>
                            <td class="px-6 py-3">
                                @if($vendor->today_status === 'paid')
                                    <flux:badge color="lime" size="sm">Paid</flux:badge>
                                @elseif($vendor->today_status === 'overdue')
                                    <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">Pending</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                @php $lastCol = $vendor->collections->first(); @endphp
                                {{ $lastCol ? $lastCol->payment_date->format('M j, Y') : '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($vendor->today_status !== 'paid')
                                <flux:button variant="primary" size="sm" icon="banknotes" :href="route('collector.collect')" wire:navigate>
                                    {{ __('Collect') }}
                                </flux:button>
                                @else
                                <flux:badge color="zinc" size="sm">Done</flux:badge>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No vendors found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
