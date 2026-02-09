<x-layouts::app :title="__('Record Collection')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Record Collection') }}</flux:heading>
                <flux:subheading>{{ __('Record a new payment collection from a vendor.') }}</flux:subheading>
            </div>
            <flux:button variant="subtle" icon="arrow-left" :href="route('collector.summary')" wire:navigate>
                {{ __('Back to Summary') }}
            </flux:button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Collection Form --}}
            <div class="lg:col-span-2 rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Collection Details') }}</flux:heading>
                </div>
                <div class="p-6">
                    <form class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <flux:label class="mb-2">{{ __('Vendor') }}</flux:label>
                                <flux:select placeholder="Select vendor...">
                                    <flux:select.option value="1">Maria Santos — A-12</flux:select.option>
                                    <flux:select.option value="2">Pedro Lim — A-03</flux:select.option>
                                    <flux:select.option value="3">Rosa Garcia — D-11</flux:select.option>
                                    <flux:select.option value="4">Juan Cruz — B-05</flux:select.option>
                                    <flux:select.option value="5">Ana Reyes — C-08</flux:select.option>
                                    <flux:select.option value="6">Roberto Aquino — A-15</flux:select.option>
                                    <flux:select.option value="7">Grace Villanueva — B-08</flux:select.option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label class="mb-2">{{ __('Stall') }}</flux:label>
                                <flux:input placeholder="Auto-filled from vendor" disabled />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <flux:label class="mb-2">{{ __('Amount (₱)') }}</flux:label>
                                <flux:input type="number" placeholder="0.00" min="0" step="0.01" />
                            </div>
                            <div>
                                <flux:label class="mb-2">{{ __('Payment Type') }}</flux:label>
                                <flux:select>
                                    <flux:select.option value="daily">Daily Collection</flux:select.option>
                                    <flux:select.option value="monthly">Monthly Rental</flux:select.option>
                                    <flux:select.option value="penalty">Penalty / Surcharge</flux:select.option>
                                    <flux:select.option value="other">Other</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <flux:label class="mb-2">{{ __('Payment Method') }}</flux:label>
                                <flux:select>
                                    <flux:select.option value="cash">Cash</flux:select.option>
                                    <flux:select.option value="gcash">GCash</flux:select.option>
                                    <flux:select.option value="bank">Bank Transfer</flux:select.option>
                                </flux:select>
                            </div>
                            <div>
                                <flux:label class="mb-2">{{ __('Collection Date') }}</flux:label>
                                <flux:input type="date" :value="now()->format('Y-m-d')" />
                            </div>
                        </div>

                        <div>
                            <flux:label class="mb-2">{{ __('Notes (Optional)') }}</flux:label>
                            <flux:textarea placeholder="Any additional notes about this collection..." rows="3" />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <flux:button variant="subtle">{{ __('Clear Form') }}</flux:button>
                            <flux:button variant="primary" icon="check">{{ __('Record Collection') }}</flux:button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Quick Info Sidebar --}}
            <div class="space-y-4">
                {{-- Today's Progress --}}
                <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                    <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Today\'s Progress') }}</flux:heading>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="text-center">
                            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">₱ 12,350</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">collected today</p>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-zinc-600 dark:text-zinc-400">18 of 24 vendors</span>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">75%</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-500" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Collections --}}
                <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                    <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Last 5 Collections') }}</flux:heading>
                    </div>
                    <div class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $recent = [
                                ['vendor' => 'Miguel Rivera', 'stall' => 'D-06', 'amount' => '₱ 200', 'time' => '8:00 AM'],
                                ['vendor' => 'Luisa Bautista', 'stall' => 'C-03', 'amount' => '₱ 175', 'time' => '7:45 AM'],
                                ['vendor' => 'Carlos Mendoza', 'stall' => 'B-12', 'amount' => '₱ 3,500', 'time' => '7:30 AM'],
                                ['vendor' => 'Elena Tan', 'stall' => 'A-07', 'amount' => '₱ 150', 'time' => '7:15 AM'],
                                ['vendor' => 'Ana Reyes', 'stall' => 'C-08', 'amount' => '₱ 175', 'time' => '7:02 AM'],
                            ];
                        @endphp
                        @foreach($recent as $r)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $r['vendor'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $r['stall'] }} · {{ $r['time'] }}</p>
                            </div>
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $r['amount'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
