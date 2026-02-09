<x-layouts::app :title="__('My Collections')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Collections') }}</flux:heading>
                <flux:subheading>{{ __('History of all payments you\'ve collected.') }}</flux:subheading>
            </div>
            <flux:button variant="primary" size="sm" icon="plus" :href="route('collector.collect')" wire:navigate>
                {{ __('Record New') }}
            </flux:button>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('This Week') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.banknotes class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 67,800</flux:heading>
                    <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">
                        <flux:icon.arrow-trending-up class="mr-1 inline size-3" /> +12% vs last week
                    </flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('This Month') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.calendar class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 245,600</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Feb 1 – {{ now()->format('M j') }}</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Receipts') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.document-text class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">156</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">this month</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Avg. Daily') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.chart-bar class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 13,600</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">per working day</flux:text>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input icon="magnifying-glass" placeholder="Search by vendor name or receipt number..." />
            </div>
            <flux:select class="sm:w-40">
                <flux:select.option value="">All Types</flux:select.option>
                <flux:select.option value="daily">Daily</flux:select.option>
                <flux:select.option value="monthly">Monthly</flux:select.option>
                <flux:select.option value="penalty">Penalty</flux:select.option>
            </flux:select>
            <flux:select class="sm:w-40">
                <flux:select.option value="">This Month</flux:select.option>
                <flux:select.option value="last_week">Last Week</flux:select.option>
                <flux:select.option value="last_month">Last Month</flux:select.option>
                <flux:select.option value="last_3_months">Last 3 Months</flux:select.option>
            </flux:select>
        </div>

        {{-- Collections Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Receipt #') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $collections = [
                                ['receipt' => 'RCP-2026-0209-018', 'date' => 'Feb 9, 2026', 'vendor' => 'Miguel Rivera', 'stall' => 'D-06', 'amount' => '₱ 200', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0209-017', 'date' => 'Feb 9, 2026', 'vendor' => 'Luisa Bautista', 'stall' => 'C-03', 'amount' => '₱ 175', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0209-016', 'date' => 'Feb 9, 2026', 'vendor' => 'Carlos Mendoza', 'stall' => 'B-12', 'amount' => '₱ 3,500', 'type' => 'Monthly', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0209-015', 'date' => 'Feb 9, 2026', 'vendor' => 'Elena Tan', 'stall' => 'A-07', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0208-014', 'date' => 'Feb 8, 2026', 'vendor' => 'Maria Santos', 'stall' => 'A-12', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0208-013', 'date' => 'Feb 8, 2026', 'vendor' => 'Pedro Lim', 'stall' => 'A-03', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0208-012', 'date' => 'Feb 8, 2026', 'vendor' => 'Rosa Garcia', 'stall' => 'D-11', 'amount' => '₱ 250', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0208-011', 'date' => 'Feb 8, 2026', 'vendor' => 'Juan Cruz', 'stall' => 'B-05', 'amount' => '₱ 200', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0207-010', 'date' => 'Feb 7, 2026', 'vendor' => 'Ana Reyes', 'stall' => 'C-08', 'amount' => '₱ 175', 'type' => 'Daily', 'status' => 'Confirmed'],
                                ['receipt' => 'RCP-2026-0207-009', 'date' => 'Feb 7, 2026', 'vendor' => 'Roberto Aquino', 'stall' => 'A-15', 'amount' => '₱ 150', 'type' => 'Daily', 'status' => 'Confirmed'],
                            ];
                        @endphp
                        @foreach($collections as $c)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $c['receipt'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $c['date'] }}</td>
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
    </div>
</x-layouts::app>
