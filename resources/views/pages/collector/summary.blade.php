<x-layouts::app :title="__('Daily Summary')">
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
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 12,350</flux:heading>
                    <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <flux:icon.arrow-trending-up class="mr-1 inline size-3" /> +5.4% vs yesterday
                    </flux:text>
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
                    <flux:heading size="xl" class="text-2xl font-bold">18</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">of 24 assigned vendors</flux:text>
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
                    <flux:heading size="xl" class="text-2xl font-bold">6</flux:heading>
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
                    <flux:heading size="xl" class="text-2xl font-bold">75%</flux:heading>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-purple-500" style="width: 75%"></div>
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
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $todayCollections = [
                                ['time' => '6:15 AM', 'vendor' => 'Maria Santos', 'stall' => 'A-12', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '6:22 AM', 'vendor' => 'Pedro Lim', 'stall' => 'A-03', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '6:35 AM', 'vendor' => 'Rosa Garcia', 'stall' => 'D-11', 'amount' => '₱ 250', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '6:48 AM', 'vendor' => 'Juan Cruz', 'stall' => 'B-05', 'amount' => '₱ 200', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '7:02 AM', 'vendor' => 'Ana Reyes', 'stall' => 'C-08', 'amount' => '₱ 175', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '7:15 AM', 'vendor' => 'Elena Tan', 'stall' => 'A-07', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '7:30 AM', 'vendor' => 'Carlos Mendoza', 'stall' => 'B-12', 'amount' => '₱ 3,500', 'type' => 'Monthly', 'status' => 'Confirmed'],
                                ['time' => '7:45 AM', 'vendor' => 'Luisa Bautista', 'stall' => 'C-03', 'amount' => '₱ 175', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['time' => '8:00 AM', 'vendor' => 'Miguel Rivera', 'stall' => 'D-06', 'amount' => '₱ 200', 'type' => 'Daily', 'status' => 'Confirmed'],
                            ];
                        @endphp
                        @foreach($todayCollections as $c)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400">{{ $c['time'] }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $c['vendor'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $c['stall'] }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $c['amount'] }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$c['type'] === 'Monthly' ? 'blue' : 'zinc'" size="sm">{{ $c['type'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:badge color="lime" size="sm">{{ $c['status'] }}</flux:badge>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Remaining Vendors --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm shadow-sm dark:border-amber-900/50 dark:bg-amber-900/10">
            <div class="border-b border-amber-200 px-6 py-4 dark:border-amber-900/50">
                <flux:heading size="lg" class="text-amber-900 dark:text-amber-200">{{ __('Pending Vendors (6 remaining)') }}</flux:heading>
            </div>
            <div class="p-4">
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $pendingVendors = [
                            ['name' => 'Roberto Aquino', 'stall' => 'A-15', 'amount' => '₱ 150'],
                            ['name' => 'Grace Villanueva', 'stall' => 'B-08', 'amount' => '₱ 200'],
                            ['name' => 'Mark Fernandez', 'stall' => 'B-14', 'amount' => '₱ 200'],
                            ['name' => 'Joy Pascual', 'stall' => 'C-11', 'amount' => '₱ 175'],
                            ['name' => 'Dennis Ramos', 'stall' => 'D-02', 'amount' => '₱ 250'],
                            ['name' => 'Linda Soriano', 'stall' => 'D-09', 'amount' => '₱ 250'],
                        ];
                    @endphp
                    @foreach($pendingVendors as $v)
                    <div class="flex items-center justify-between rounded-xl bg-white/60 dark:bg-zinc-900/40 p-3 border border-amber-100 dark:border-amber-900/30">
                        <div>
                            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ $v['name'] }}</p>
                            <p class="text-xs text-amber-700 dark:text-amber-400">Stall {{ $v['stall'] }} · {{ $v['amount'] }}</p>
                        </div>
                        <flux:badge color="yellow" size="sm">Pending</flux:badge>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
