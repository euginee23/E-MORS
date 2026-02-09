<x-layouts::app :title="__('Reports & Analytics')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Reports & Analytics') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Real-time dashboards and comprehensive reports for data-driven decisions.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:select class="sm:w-40" placeholder="{{ __('Period') }}">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
                    <flux:select.option value="month" selected>{{ __('This Month') }}</flux:select.option>
                    <flux:select.option value="quarter">{{ __('This Quarter') }}</flux:select.option>
                    <flux:select.option value="year">{{ __('This Year') }}</flux:select.option>
                </flux:select>
                <flux:button icon="arrow-down-tray" variant="outline">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </div>

        {{-- Revenue Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Revenue') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱ 1,247,500</flux:heading>
                <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">+15.3% vs last month</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Avg Daily Collection') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱ 41,583</flux:heading>
                <flux:text class="mt-1 text-xs text-green-600 dark:text-green-400">+3.2% vs avg</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collection Efficiency') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">94.2%</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">Target: 95%</flux:text>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Outstanding Balance') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">₱ 72,500</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">18 vendors with balance</flux:text>
            </div>
        </div>

        {{-- Charts Area --}}
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Revenue Trend --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Monthly Revenue Trend') }}</flux:heading>
                </div>
                <div class="p-6">
                    <div class="flex h-48 gap-2">
                        @php
                            $months = [
                                ['month' => 'Aug', 'amount' => 980000, 'max' => 1300000],
                                ['month' => 'Sep', 'amount' => 1050000, 'max' => 1300000],
                                ['month' => 'Oct', 'amount' => 1120000, 'max' => 1300000],
                                ['month' => 'Nov', 'amount' => 1080000, 'max' => 1300000],
                                ['month' => 'Dec', 'amount' => 1200000, 'max' => 1300000],
                                ['month' => 'Jan', 'amount' => 1150000, 'max' => 1300000],
                                ['month' => 'Feb', 'amount' => 1247500, 'max' => 1300000],
                            ];
                        @endphp
                        @foreach($months as $m)
                        <div class="flex flex-1 flex-col items-center">
                            <div class="w-full flex-1 flex items-end">
                                <div class="w-full rounded-t bg-blue-500/80 transition-all hover:bg-blue-500" style="height: {{ round($m['amount'] / $m['max'] * 100) }}%"></div>
                            </div>
                            <flux:text class="mt-1 shrink-0 text-xs text-zinc-500">{{ $m['month'] }}</flux:text>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Collection by Section --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Collection by Section') }}</flux:heading>
                </div>
                <div class="p-6">
                    @php
                        $sections = [
                            ['name' => 'Section A', 'amount' => 380000, 'percentage' => 30.5, 'color' => 'emerald'],
                            ['name' => 'Section B', 'amount' => 312000, 'percentage' => 25.0, 'color' => 'blue'],
                            ['name' => 'Section C', 'amount' => 298500, 'percentage' => 23.9, 'color' => 'purple'],
                            ['name' => 'Section D', 'amount' => 257000, 'percentage' => 20.6, 'color' => 'amber'],
                        ];
                    @endphp
                    <div class="space-y-4">
                        @foreach($sections as $section)
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <flux:text class="text-sm font-medium">{{ $section['name'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">₱ {{ number_format($section['amount']) }} ({{ $section['percentage'] }}%)</flux:text>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-{{ $section['color'] }}-500" style="width: {{ $section['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Vendors & Overdue --}}
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Top Performing Vendors --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Top Performing Vendors') }}</flux:heading>
                </div>
                <div class="divide-y divide-orange-100 dark:divide-zinc-700">
                    @php
                        $topVendors = [
                            ['rank' => 1, 'name' => 'Maria Santos', 'stall' => 'A-12', 'paid' => '₱ 42,000', 'streak' => '12 months'],
                            ['rank' => 2, 'name' => 'Carlos Tan', 'stall' => 'B-09', 'paid' => '₱ 36,000', 'streak' => '10 months'],
                            ['rank' => 3, 'name' => 'Elena Dela Cruz', 'stall' => 'C-15', 'paid' => '₱ 31,500', 'streak' => '9 months'],
                            ['rank' => 4, 'name' => 'Juan Cruz', 'stall' => 'B-05', 'paid' => '₱ 28,800', 'streak' => '8 months'],
                            ['rank' => 5, 'name' => 'Pedro Lim', 'stall' => 'A-03', 'paid' => '₱ 27,000', 'streak' => '12 months'],
                        ];
                    @endphp
                    @foreach($topVendors as $vendor)
                    <div class="flex items-center gap-4 px-6 py-3">
                        <span class="flex size-8 items-center justify-center rounded-full bg-zinc-100 text-sm font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $vendor['rank'] }}</span>
                        <div class="flex-1">
                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor['name'] }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $vendor['stall'] }} · {{ $vendor['streak'] }} on-time</flux:text>
                        </div>
                        <flux:text class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $vendor['paid'] }}</flux:text>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Overdue Payments --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Overdue Payments') }}</flux:heading>
                </div>
                <div class="divide-y divide-orange-100 dark:divide-zinc-700">
                    @php
                        $overdue = [
                            ['name' => 'Rosa Garcia', 'stall' => 'D-11', 'amount' => '₱ 7,000', 'days' => '15 days'],
                            ['name' => 'Roberto Villanueva', 'stall' => 'A-07', 'amount' => '₱ 3,500', 'days' => '8 days'],
                            ['name' => 'Linda Fernandez', 'stall' => 'C-02', 'amount' => '₱ 5,000', 'days' => '5 days'],
                            ['name' => 'Ramon Aquino', 'stall' => 'B-14', 'amount' => '₱ 3,500', 'days' => '3 days'],
                        ];
                    @endphp
                    @foreach($overdue as $item)
                    <div class="flex items-center gap-4 px-6 py-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-500" />
                        <div class="flex-1">
                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $item['stall'] }} · Overdue by {{ $item['days'] }}</flux:text>
                        </div>
                        <flux:text class="font-semibold text-red-600 dark:text-red-400">{{ $item['amount'] }}</flux:text>
                    </div>
                    @endforeach
                    @if(empty($overdue))
                    <div class="px-6 py-8 text-center">
                        <flux:icon.check-circle class="mx-auto size-8 text-emerald-500" />
                        <flux:text class="mt-2 text-sm text-zinc-500">{{ __('No overdue payments') }}</flux:text>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
