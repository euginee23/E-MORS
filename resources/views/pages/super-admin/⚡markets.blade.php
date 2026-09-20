<?php

/**
 * Super admin market overview. Every other screen in the app is scoped to a single
 * market_id; this is the one place the whole network is visible side by side.
 */

use App\Enums\PaymentStatus;
use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Models\Collection;
use App\Models\Market;
use App\Models\Stall;
use App\Models\Vendor;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public bool $showDetailModal = false;

    public ?int $viewingMarketId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function markets()
    {
        return Market::withCount(['stalls', 'vendors', 'collections'])
            ->with('admins')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('address', 'like', '%' . $this->search . '%')
            ))
            ->orderBy('name')
            ->paginate(10);
    }

    #[Computed]
    public function totalMarkets(): int
    {
        return Market::count();
    }

    #[Computed]
    public function totalStalls(): int
    {
        return Stall::count();
    }

    #[Computed]
    public function totalVendors(): int
    {
        return Vendor::count();
    }

    #[Computed]
    public function totalCollected(): string
    {
        $total = Collection::where('status', PaymentStatus::Paid)->sum('amount');

        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function viewingMarket(): ?Market
    {
        return $this->viewingMarketId
            ? Market::withCount(['stalls', 'vendors', 'collections'])->with('admins')->find($this->viewingMarketId)
            : null;
    }

    /**
     * The extra figures for the detail modal. Kept off the list query so the
     * table does not pay for breakdowns nobody is looking at.
     */
    #[Computed]
    public function viewingDetail(): ?array
    {
        $market = $this->viewingMarket;

        if (! $market) {
            return null;
        }

        return [
            'occupied' => Stall::where('market_id', $market->id)->where('status', StallStatus::Occupied)->count(),
            'available' => Stall::where('market_id', $market->id)->where('status', StallStatus::Available)->count(),
            'maintenance' => Stall::where('market_id', $market->id)->where('status', StallStatus::Maintenance)->count(),
            'activeVendors' => Vendor::where('market_id', $market->id)->where('permit_status', PermitStatus::Active)->count(),
            'pendingVendors' => Vendor::where('market_id', $market->id)->where('permit_status', PermitStatus::Pending)->count(),
            'collected' => Collection::where('market_id', $market->id)->where('status', PaymentStatus::Paid)->sum('amount'),
        ];
    }

    public function viewMarket(int $marketId): void
    {
        $this->viewingMarketId = $marketId;
        $this->showDetailModal = true;
    }

    public function render()
    {
        return $this->view()->title(__('Markets'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div>
            <flux:heading size="xl">{{ __('Markets') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Every registered market, with its stall and vendor counts at a glance.') }}</flux:subheading>
        </div>

        {{-- Network Totals --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Registered Markets') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalMarkets }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Stalls') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ number_format($this->totalStalls) }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Vendors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ number_format($this->totalVendors) }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Collected') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->totalCollected }}</flux:heading>
            </div>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by market name or address...') }}" />
            </div>
        </div>

        {{-- Markets Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Market') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Address') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Administrator') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stalls') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendors') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->markets as $market)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="market-{{ $market->id }}">
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $market->name }}</td>
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400 max-w-64">
                                <span title="{{ $market->address }}">{{ Str::limit($market->address, 50) }}</span>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $market->admins->first()?->name ?? '—' }}</td>
                            <td class="px-6 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($market->stalls_count) }}</td>
                            <td class="px-6 py-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($market->vendors_count) }}</td>
                            <td class="px-6 py-3">
                                <flux:button size="sm" variant="ghost" icon="eye" wire:click="viewMarket({{ $market->id }})">
                                    {{ __('View') }}
                                </flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-zinc-400">
                                    <flux:icon.building-library class="size-10" />
                                    <p class="text-sm">{{ __('No markets match your search.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-zinc-700">
                {{ $this->markets->links() }}
            </div>
        </div>
    </div>

    {{-- Market Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-2xl">
        @if($this->viewingMarket)
        @php($detail = $this->viewingDetail)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $this->viewingMarket->name }}</flux:heading>
                <flux:subheading class="mt-1">{{ $this->viewingMarket->address }}</flux:subheading>
            </div>

            {{-- Headline summary --}}
            <div class="rounded-xl border border-orange-100 bg-orange-50/50 px-5 py-4 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($this->viewingMarket->stalls_count) }} {{ trans_choice('Stall|Stalls', $this->viewingMarket->stalls_count) }}
                    <span class="mx-2 text-zinc-300 dark:text-zinc-600">|</span>
                    {{ number_format($this->viewingMarket->vendors_count) }} {{ trans_choice('Vendor|Vendors', $this->viewingMarket->vendors_count) }}
                </p>
            </div>

            {{-- Breakdown --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Occupied Stalls') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($detail['occupied']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Available Stalls') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($detail['available']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Under Maintenance') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($detail['maintenance']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Active Permits') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($detail['activeVendors']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pending Applications') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($detail['pendingVendors']) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collection Transactions') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($this->viewingMarket->collections_count) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total Collected') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-emerald-600">₱ {{ number_format($detail['collected'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Registered') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->viewingMarket->created_at->format('M j, Y') }}</p>
                </div>
            </div>

            {{-- Administrators --}}
            <div>
                <flux:text class="text-sm font-semibold mb-3">{{ __('Administrators') }}</flux:text>
                @forelse($this->viewingMarket->admins as $admin)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3.5 py-3 dark:border-zinc-700" wire:key="market-admin-{{ $admin->id }}">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $admin->name }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $admin->email }}</p>
                    </div>
                    <flux:badge :color="$admin->status?->color() ?? 'zinc'" size="sm">
                        {{ $admin->status?->label() ?? __('Unknown') }}
                    </flux:badge>
                </div>
                @empty
                <p class="text-sm text-zinc-400">{{ __('No administrator is assigned to this market.') }}</p>
                @endforelse
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" wire:click="$set('showDetailModal', false)">{{ __('Close') }}</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>
