<x-layouts::app :title="__('Fee Collection')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Fee Collection') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Track payments, issue digital receipts, and monitor collection status.') }}</flux:subheading>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isCollector())
            <flux:button icon="plus" variant="primary">
                {{ __('Record Payment') }}
            </flux:button>
            @endif
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __("Today's Collections") }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱ 45,280</flux:heading>
                <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">+8.2% vs yesterday</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('This Week') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱ 287,500</flux:heading>
                <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">+5.1% vs last week</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Payments') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">18</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">₱ 63,000 outstanding</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collection Rate') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">94.2%</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">Target: 95%</flux:text>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input icon="magnifying-glass" placeholder="{{ __('Search by vendor, stall, or receipt number...') }}" />
            </div>
            <flux:select class="sm:w-40" placeholder="{{ __('Status') }}">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
            <flux:select class="sm:w-40" placeholder="{{ __('Period') }}">
                <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
                <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
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
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $collections = [
                                ['receipt' => 'RCP-2026-0142', 'date' => now()->format('M j, Y'), 'vendor' => 'Maria Santos', 'stall' => 'A-12', 'amount' => '₱ 150', 'collector' => 'Collector User', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2026-0141', 'date' => now()->format('M j, Y'), 'vendor' => 'Juan Cruz', 'stall' => 'B-05', 'amount' => '₱ 200', 'collector' => 'Collector User', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2026-0140', 'date' => now()->format('M j, Y'), 'vendor' => 'Ana Reyes', 'stall' => 'C-08', 'amount' => '₱ 175', 'collector' => '—', 'status' => 'Pending'],
                                ['receipt' => 'RCP-2026-0139', 'date' => now()->subDay()->format('M j, Y'), 'vendor' => 'Pedro Lim', 'stall' => 'A-03', 'amount' => '₱ 150', 'collector' => 'Collector User', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2026-0138', 'date' => now()->subDay()->format('M j, Y'), 'vendor' => 'Rosa Garcia', 'stall' => 'D-11', 'amount' => '₱ 250', 'collector' => 'Collector User', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2026-0137', 'date' => now()->subDays(2)->format('M j, Y'), 'vendor' => 'Carlos Tan', 'stall' => 'B-09', 'amount' => '₱ 200', 'collector' => '—', 'status' => 'Overdue'],
                                ['receipt' => 'RCP-2026-0136', 'date' => now()->subDays(2)->format('M j, Y'), 'vendor' => 'Elena Dela Cruz', 'stall' => 'C-15', 'amount' => '₱ 175', 'collector' => 'Collector User', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2026-0135', 'date' => now()->subDays(3)->format('M j, Y'), 'vendor' => 'Roberto Villanueva', 'stall' => 'A-07', 'amount' => '₱ 150', 'collector' => 'Collector User', 'status' => 'Paid'],
                            ];
                        @endphp
                        @foreach($collections as $collection)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 font-mono text-xs font-medium text-zinc-900 dark:text-zinc-100">{{ $collection['receipt'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection['date'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection['vendor'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection['stall'] }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $collection['amount'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $collection['collector'] }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $color = match($collection['status']) {
                                        'Paid' => 'lime',
                                        'Pending' => 'yellow',
                                        'Overdue' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ $collection['status'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye">{{ __('View Receipt') }}</flux:menu.item>
                                        <flux:menu.item icon="printer">{{ __('Print') }}</flux:menu.item>
                                        @if(auth()->user()->isAdmin())
                                        <flux:menu.separator />
                                        <flux:menu.item icon="pencil-square">{{ __('Edit') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                <flux:text class="text-sm text-zinc-500">{{ __('Showing 1-8 of 142 collections') }}</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
