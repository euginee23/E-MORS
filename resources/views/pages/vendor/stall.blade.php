<x-layouts::app :title="__('My Stall')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Stall') }}</flux:heading>
                <flux:subheading>{{ __('Your assigned stall information and details.') }}</flux:subheading>
            </div>
            <flux:badge color="lime" size="lg" icon="building-storefront">Stall A-12</flux:badge>
        </div>

        {{-- Stall Overview Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Stall Number') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/20">
                        <flux:icon.building-storefront class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">A-12</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Section A, Ground Floor</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Stall Size') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.square-3-stack-3d class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">3×4m</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">12 sqm total area</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Monthly Rate') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.banknotes class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ 3,500</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Due every end of month</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Status') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.check-circle class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:badge color="lime" size="sm">Active</flux:badge>
                    <flux:text class="mt-1 text-xs text-zinc-500">Since Jan 15, 2024</flux:text>
                </div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Stall Details --}}
            <div class="lg:col-span-2 rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Stall Details') }}</flux:heading>
                </div>
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Stall Number') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">A-12</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Section A — Ground Floor</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Stall Type') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Wet Market</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Dimensions') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">3m × 4m (12 sqm)</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Lease Start') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">January 15, 2024</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Lease Expiry') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">December 31, 2026</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Market Admin Info --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Market Administration') }}</flux:heading>
                </div>
                <div class="p-6 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">JD</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Juan Dela Cruz</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Market Administrator</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <flux:icon.envelope class="size-4 text-zinc-400" />
                            <span class="text-zinc-600 dark:text-zinc-400">admin@emors.test</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <flux:icon.phone class="size-4 text-zinc-400" />
                            <span class="text-zinc-600 dark:text-zinc-400">(02) 8123-4567</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <flux:icon.map-pin class="size-4 text-zinc-400" />
                            <span class="text-zinc-600 dark:text-zinc-400">Pamilihang Bayan ng Malolos, Bulacan</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Market Hours') }}</p>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Monday – Sunday</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">4:00 AM – 8:00 PM</p>
                    </div>

                    <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Assigned Collector') }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-[10px]">RC</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Rosa Collector</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">collector@emors.test</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
