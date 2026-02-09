<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Welcome Banner --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl">{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</flux:heading>
                    <flux:subheading class="mt-1">
                        @if(auth()->user()->isAdmin())
                            {{ __('Here\'s an overview of your market operations today.') }}
                        @elseif(auth()->user()->isCollector())
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
            @if(auth()->user()->isAdmin() || auth()->user()->isCollector())
            {{-- Total Vendors --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Vendors') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">248</flux:heading>
                    <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <flux:icon.arrow-trending-up class="mr-1 inline size-3" /> +12 this month
                    </flux:text>
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
                    <flux:heading size="xl" class="text-2xl font-bold">320</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">
                        92% occupied
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
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 45,280</flux:heading>
                    <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <flux:icon.arrow-trending-up class="mr-1 inline size-3" /> +8.2% vs yesterday
                    </flux:text>
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
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 1.2M</flux:heading>
                    <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <flux:icon.arrow-trending-up class="mr-1 inline size-3" /> +15% vs last month
                    </flux:text>
                </div>
            </div>
            @endif

            @if(auth()->user()->isVendor())
            {{-- My Stall --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('My Stall') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.building-storefront class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">A-12</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Section A, Ground Floor</flux:text>
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
                    <flux:badge color="lime" size="sm">Paid</flux:badge>
                    <flux:text class="mt-1 text-xs text-zinc-500">Last payment: {{ now()->subDays(3)->format('M j, Y') }}</flux:text>
                </div>
            </div>

            {{-- Monthly Fees --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Monthly Fee') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.banknotes class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 3,500</flux:heading>
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
                    <flux:badge color="lime" size="sm">Active</flux:badge>
                    <flux:text class="mt-1 text-xs text-zinc-500">Expires: Dec 31, 2026</flux:text>
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
                        @if(auth()->user()->isVendor())
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
                                @if(!auth()->user()->isVendor())
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                                @endif
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                                <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                            @php
                                $sampleData = [
                                    ['date' => now()->format('M j'), 'vendor' => 'Maria Santos', 'stall' => 'A-12', 'amount' => '₱ 150', 'status' => 'Paid'],
                                    ['date' => now()->format('M j'), 'vendor' => 'Juan Cruz', 'stall' => 'B-05', 'amount' => '₱ 200', 'status' => 'Paid'],
                                    ['date' => now()->format('M j'), 'vendor' => 'Ana Reyes', 'stall' => 'C-08', 'amount' => '₱ 175', 'status' => 'Pending'],
                                    ['date' => now()->subDay()->format('M j'), 'vendor' => 'Pedro Lim', 'stall' => 'A-03', 'amount' => '₱ 150', 'status' => 'Paid'],
                                    ['date' => now()->subDay()->format('M j'), 'vendor' => 'Rosa Garcia', 'stall' => 'D-11', 'amount' => '₱ 250', 'status' => 'Paid'],
                                ];
                            @endphp
                            @foreach($sampleData as $row)
                            <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row['date'] }}</td>
                                @if(!auth()->user()->isVendor())
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row['vendor'] }}</td>
                                @endif
                                <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $row['stall'] }}</td>
                                <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $row['amount'] }}</td>
                                <td class="px-6 py-3">
                                    <flux:badge :color="$row['status'] === 'Paid' ? 'lime' : 'yellow'" size="sm">{{ $row['status'] }}</flux:badge>
                                </td>
                            </tr>
                            @endforeach
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
                    @if(auth()->user()->isAdmin())
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
                    @elseif(auth()->user()->isCollector())
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
        @if(auth()->user()->isAdmin())
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
                    @php
                        $sections = [
                            ['name' => 'Section A', 'total' => 80, 'occupied' => 76, 'color' => 'emerald'],
                            ['name' => 'Section B', 'total' => 80, 'occupied' => 72, 'color' => 'blue'],
                            ['name' => 'Section C', 'total' => 80, 'occupied' => 74, 'color' => 'purple'],
                            ['name' => 'Section D', 'total' => 80, 'occupied' => 73, 'color' => 'amber'],
                        ];
                    @endphp
                    @foreach($sections as $section)
                    <div class="rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <flux:text class="text-sm font-medium">{{ $section['name'] }}</flux:text>
                        <div class="mt-2">
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $section['occupied'] }}</span>
                                <span class="text-sm text-zinc-500">/ {{ $section['total'] }}</span>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-{{ $section['color'] }}-500" style="width: {{ round($section['occupied'] / $section['total'] * 100) }}%"></div>
                            </div>
                            <flux:text class="mt-1 text-xs text-zinc-500">{{ round($section['occupied'] / $section['total'] * 100) }}% occupied</flux:text>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</x-layouts::app>
