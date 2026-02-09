<x-layouts::app :title="__('My Payments')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Payments') }}</flux:heading>
                <flux:subheading>{{ __('View your payment history and upcoming dues.') }}</flux:subheading>
            </div>
        </div>

        {{-- Payment Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Next Due') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.calendar class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 3,500</flux:heading>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">Due: {{ now()->endOfMonth()->format('M j, Y') }}</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Payment Status') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:badge color="lime" size="sm">Up to Date</flux:badge>
                    <flux:text class="mt-1 text-xs text-zinc-500">No overdue balance</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Paid (Year)') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.banknotes class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 38,500</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">11 payments this year</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Outstanding') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <flux:icon.exclamation-circle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 0</flux:heading>
                    <flux:text class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">Fully settled</flux:text>
                </div>
            </div>
        </div>

        {{-- Upcoming Payment Card --}}
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm shadow-sm dark:border-amber-900/50 dark:bg-amber-900/10">
            <div class="p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex size-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
                            <flux:icon.clock class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <p class="font-semibold text-amber-900 dark:text-amber-200">{{ __('Upcoming Payment — February 2026') }}</p>
                            <p class="text-sm text-amber-700 dark:text-amber-400">Monthly stall rental fee for Stall A-12</p>
                            <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">Due date: {{ now()->endOfMonth()->format('F j, Y') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-amber-900 dark:text-amber-200">₱ 3,500.00</p>
                        <flux:badge color="yellow" size="sm" class="mt-1">Pending</flux:badge>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment History Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Payment History') }}</flux:heading>
                    <flux:badge color="zinc" size="sm">11 payments</flux:badge>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Receipt #') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Period') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $payments = [
                                ['receipt' => 'RCP-2026-0211', 'date' => 'Jan 28, 2026', 'period' => 'January 2026', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-1228', 'date' => 'Dec 26, 2025', 'period' => 'December 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-1127', 'date' => 'Nov 25, 2025', 'period' => 'November 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-1029', 'date' => 'Oct 28, 2025', 'period' => 'October 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-0930', 'date' => 'Sep 27, 2025', 'period' => 'September 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-0829', 'date' => 'Aug 26, 2025', 'period' => 'August 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                                ['receipt' => 'RCP-2025-0728', 'date' => 'Jul 25, 2025', 'period' => 'July 2025', 'amount' => '₱ 3,500', 'collector' => 'Rosa Collector', 'status' => 'Paid'],
                            ];
                        @endphp
                        @foreach($payments as $p)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $p['receipt'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $p['date'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $p['period'] }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $p['amount'] }}</td>
                            <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ $p['collector'] }}</td>
                            <td class="px-6 py-3">
                                <flux:badge color="lime" size="sm">{{ $p['status'] }}</flux:badge>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
