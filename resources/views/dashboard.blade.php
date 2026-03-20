@php
    use App\Enums\PaymentStatus;
    use App\Models\Collection;
    use App\Models\Vendor;
    use App\Models\Stall;

    $user = auth()->user();
    $marketId = $user->market_id;

    if ($user->isAdmin() || $user->isCollector()) {
        $totalVendors = Vendor::where('market_id', $marketId)->count();
        $totalStalls = Stall::where('market_id', $marketId)->count();
        $occupiedStalls = Stall::where('market_id', $marketId)->where('status', 'occupied')->count();
        $occupancyRate = $totalStalls > 0 ? round(($occupiedStalls / $totalStalls) * 100) : 0;

        $todayCollections = Collection::where('market_id', $marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');

        $monthlyRevenue = Collection::where('market_id', $marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $recentCollections = Collection::where('market_id', $marketId)
            ->with(['vendor', 'stall', 'collector'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $sectionStats = Stall::where('market_id', $marketId)
            ->selectRaw("section, count(*) as total, sum(case when status = 'occupied' then 1 else 0 end) as occupied")
            ->groupBy('section')
            ->orderBy('section')
            ->get();
    }

    if ($user->isVendor()) {
        $myVendor = $user->vendor;
        $myStall = $myVendor?->stall;
        $lastPayment = $myVendor ? Collection::where('vendor_id', $myVendor->id)
            ->where('status', PaymentStatus::Paid)->latest('payment_date')->first() : null;
        $myCollections = $myVendor ? Collection::where('vendor_id', $myVendor->id)
            ->with(['stall'])->orderBy('created_at', 'desc')->limit(5)->get() : collect();
    }
@endphp
<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Welcome Banner --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">{{ __('Welcome back, :name', ['name' => $user->name]) }}</flux:heading>
                    <flux:subheading class="mt-1">
                        @if($user->isAdmin())
                            {{ __('Here\'s an overview of your market operations today.') }}
                        @elseif($user->isCollector())
                            {{ __('Here\'s your collection summary for today.') }}
                        @else
                            {{ __('View your stall information and payment status.') }}
                        @endif
                    </flux:subheading>
                </div>
                <div class="hidden sm:block">
                    <flux:badge color="lime" size="lg" icon="calendar">{{ now()->format('F j, Y') }}</flux:badge>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if($user->isAdmin() || $user->isCollector())
            {{-- Total Vendors --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Vendors') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $totalVendors }}</flux:heading>
                </div>
            </div>

            {{-- Total Stalls --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Stalls') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.building-storefront class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $totalStalls }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">
                        {{ $occupancyRate }}% occupied
                    </flux:text>
                </div>
            </div>

            {{-- Today's Collections --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Today\'s Collections') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.banknotes class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ number_format($todayCollections, 0) }}</flux:heading>
                </div>
            </div>

            {{-- Monthly Revenue --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Monthly Revenue') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.chart-bar class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ number_format($monthlyRevenue, 0) }}</flux:heading>
                </div>
            </div>
            @endif

            @if($user->isVendor())
            {{-- My Stall --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('My Stall') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.building-storefront class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $myStall?->stall_number ?? __('Unassigned') }}</flux:heading>
                    @if($myStall)
                    <flux:text class="mt-1 text-xs text-zinc-500">Section {{ $myStall->section }}</flux:text>
                    @endif
                </div>
            </div>

            {{-- Payment Status --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Payment Status') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    @if($lastPayment)
                    <flux:badge color="{{ $lastPayment->status->color() }}" size="sm">{{ $lastPayment->status->label() }}</flux:badge>
                    <flux:text class="mt-1 text-xs text-zinc-500">Last payment: {{ $lastPayment->payment_date->format('M j, Y') }}</flux:text>
                    @else
                    <flux:badge color="yellow" size="sm">{{ __('No payments') }}</flux:badge>
                    @endif
                </div>
            </div>

            {{-- Monthly Fee --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Monthly Fee') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.banknotes class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ number_format($myStall?->monthly_rate ?? 0, 0) }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Due: {{ now()->endOfMonth()->format('M j, Y') }}</flux:text>
                </div>
            </div>

            {{-- Permit --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Permit Status') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.document-check class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    @if($myVendor)
                    <flux:badge color="{{ $myVendor->permit_status->color() }}" size="sm">{{ $myVendor->permit_status->label() }}</flux:badge>
                    @if($myVendor->permit_expiry)
                    <flux:text class="mt-1 text-xs text-zinc-500">Expires: {{ $myVendor->permit_expiry->format('M j, Y') }}</flux:text>
                    @endif
                    @else
                    <flux:badge color="zinc" size="sm">{{ __('N/A') }}</flux:badge>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Main Content Area --}}
        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Recent Activity / Collections --}}
            <div class="lg:col-span-2 rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">
                        @if($user->isVendor())
                            {{ __('Payment History') }}
                        @else
                            {{ __('Recent Collections') }}
                        @endif
                    </flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                                @if(!$user->isVendor())
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                                @endif
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                            @php
                                $rows = $user->isVendor() ? $myCollections : $recentCollections;
                            @endphp
                            @forelse($rows as $row)
                            <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row->payment_date->format('M j') }}</td>
                                @if(!$user->isVendor())
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row->vendor?->contact_name ?? '—' }}</td>
                                @endif
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row->stall?->stall_number ?? '—' }}</td>
                                <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($row->amount, 0) }}</td>
                                <td class="px-6 py-3">
                                    <flux:badge :color="$row->status->color()" size="sm">{{ $row->status->label() }}</flux:badge>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $user->isVendor() ? 4 : 5 }}" class="px-6 py-8 text-center text-zinc-500">{{ __('No recent collections.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Quick Actions') }}</flux:heading>
                </div>
                <div class="flex flex-col gap-2 p-4">
                    @if($user->isAdmin())
                    <flux:button variant="subtle" class="w-full justify-start" icon="user-plus" :href="route('vendors.index')" wire:navigate>
                        {{ __('Add New Vendor') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="building-storefront" :href="route('stalls.index')" wire:navigate>
                        {{ __('Manage Stalls') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="banknotes" :href="route('collections.index')" wire:navigate>
                        {{ __('Record Collection') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="chart-bar" :href="route('reports.index')" wire:navigate>
                        {{ __('View Reports') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="shield-check" :href="route('users.index')" wire:navigate>
                        {{ __('Manage Users') }}
                    </flux:button>
                    @elseif($user->isCollector())
                    <flux:button variant="subtle" class="w-full justify-start" icon="plus-circle" :href="route('collector.collect')" wire:navigate>
                        {{ __('Record Collection') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="clipboard-document-list" :href="route('collector.summary')" wire:navigate>
                        {{ __('Daily Summary') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="banknotes" :href="route('collector.collections')" wire:navigate>
                        {{ __('My Collections') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="users" :href="route('collector.vendors')" wire:navigate>
                        {{ __('Assigned Vendors') }}
                    </flux:button>
                    @else
                    <flux:button variant="subtle" class="w-full justify-start" icon="building-storefront" :href="route('vendor.stall')" wire:navigate>
                        {{ __('My Stall') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="banknotes" :href="route('vendor.payments')" wire:navigate>
                        {{ __('My Payments') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="bell" :href="route('vendor.announcements')" wire:navigate>
                        {{ __('Announcements') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="user-circle" :href="route('vendor.profile')" wire:navigate>
                        {{ __('Vendor Profile') }}
                    </flux:button>
                    <flux:button variant="subtle" class="w-full justify-start" icon="cog" :href="route('profile.edit')" wire:navigate>
                        {{ __('Account Settings') }}
                    </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stall Occupancy Overview (Admin only) --}}
        @if($user->isAdmin())
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stall Occupancy Overview') }}</flux:heading>
                    <flux:button variant="subtle" size="sm" icon="arrow-top-right-on-square" :href="route('stalls.index')" wire:navigate>
                        {{ __('View All') }}
                    </flux:button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach($sectionStats as $section)
                    <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <flux:text class="text-sm font-medium">Section {{ $section->section }}</flux:text>
                        <div class="mt-2">
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $section->occupied }}</span>
                                <span class="text-sm text-zinc-500">/ {{ $section->total }}</span>
                            </div>
                            @php $pct = $section->total > 0 ? round($section->occupied / $section->total * 100) : 0; @endphp
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <flux:text class="mt-1 text-xs text-zinc-500">{{ $pct }}% occupied</flux:text>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</x-layouts::app>
