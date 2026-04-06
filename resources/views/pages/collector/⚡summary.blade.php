<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function todayTotal(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');
        return number_format($total, 0);
    }

    #[Computed]
    public function totalVendors(): int
    {
        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->count();
    }

    #[Computed]
    public function vendorsVisitedToday(): int
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->distinct('vendor_id')
            ->count('vendor_id');
    }

    #[Computed]
    public function pendingToday(): int
    {
        $paidVendorIds = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->pluck('vendor_id');

        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->whereNotIn('id', $paidVendorIds)
            ->count();
    }

    #[Computed]
    public function collectionRate(): int
    {
        $total = $this->totalVendors;
        if ($total === 0) return 0;
        return (int) round(($this->vendorsVisitedToday / $total) * 100);
    }

    #[Computed]
    public function todayCollections()
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->whereDate('payment_date', today())
            ->with(['vendor', 'stall'])
            ->orderByDesc('created_at')
            ->get();
    }

    #[Computed]
    public function pendingVendors()
    {
        $paidVendorIds = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->pluck('vendor_id');

        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->whereNotIn('id', $paidVendorIds)
            ->with('stall')
            ->orderBy('contact_name')
            ->get();
    }

    public function render()
    {
        return $this->view()->title(__('Daily Summary'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Daily Summary') }}</flux:heading>
                <flux:subheading>{{ __('Your collection overview for today.') }}</flux:subheading>
            </div>
            <flux:badge color="lime" size="lg" icon="calendar">{{ now()->format('F j, Y') }}</flux:badge>
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Collected') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.banknotes class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->todayTotal }}</flux:heading>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Vendors Visited') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->vendorsVisitedToday }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">of {{ $this->totalVendors }} vendors</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Pending') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->pendingToday }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">Vendors remaining</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Collection Rate') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.chart-bar class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->collectionRate }}%</flux:heading>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-purple-500" style="width: {{ $this->collectionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Collections Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Today\'s Collections') }}</flux:heading>
                    <flux:button variant="primary" size="sm" icon="plus" :href="route('collector.collect')" wire:navigate>
                        {{ __('Record Payment') }}
                    </flux:button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Time') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->todayCollections as $collection)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="tc-{{ $collection->id }}">
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400">{{ $collection->created_at->format('g:i A') }}</td>
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
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No collections recorded today.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending Vendors --}}
        @if($this->pendingVendors->isNotEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm shadow-sm dark:border-amber-900/50 dark:bg-amber-900/10">
            <div class="border-b border-amber-200 px-6 py-4 dark:border-amber-900/50">
                <flux:heading size="lg" class="text-amber-900 dark:text-amber-200">{{ __('Pending Vendors (:count remaining)', ['count' => $this->pendingVendors->count()]) }}</flux:heading>
            </div>
            <div class="p-4">
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->pendingVendors as $vendor)
                    <div class="flex items-center justify-between rounded-xl bg-white/60 dark:bg-zinc-900/40 p-3 border border-amber-100 dark:border-amber-900/30" wire:key="pv-{{ $vendor->id }}">
                        <div>
                            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ $vendor->contact_name }}</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400">Stall {{ $vendor->stall?->stall_number }} · ₱ {{ number_format($vendor->stall?->monthly_rate, 0) }}</p>
                        </div>
                        <flux:badge color="yellow" size="sm">Pending</flux:badge>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
