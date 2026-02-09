<x-layouts::app :title="__('Vendor Profile')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vendor Profile') }}</flux:heading>
                <flux:subheading>{{ __('Your business details and permit information.') }}</flux:subheading>
            </div>
            <flux:badge color="lime" size="lg" icon="document-check">Permit Active</flux:badge>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            {{-- Business Information --}}
            <div class="lg:col-span-2 rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Business Information') }}</flux:heading>
                </div>
                <div class="p-6">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Business Name') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Santos Fresh Produce</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Owner Name') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Business Type') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Fresh Vegetables & Fruits</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Contact Number') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">0917-123-4567</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->email }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Address') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">123 Rizal St., Malolos, Bulacan</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Permit Information --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Permit Details') }}</flux:heading>
                </div>
                <div class="p-6 space-y-4">
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Permit Number') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">VND-2024-01234</dd>
                    </div>
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                        <dd class="mt-2">
                            <flux:badge color="lime" size="sm">Active</flux:badge>
                        </dd>
                    </div>
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Issued Date') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">January 15, 2024</dd>
                    </div>
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Expiry Date') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">December 31, 2026</dd>
                    </div>
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Permit Type') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Market Vendor Permit</dd>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Connection --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('My Market Administration') }}</flux:heading>
            </div>
            <div class="p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="flex items-center gap-4 rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">JD</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Administrator') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Juan Dela Cruz</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">admin@emors.test</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">RC</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Assigned Collector') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Rosa Collector</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">collector@emors.test</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-md">
                            <flux:icon.map-pin class="size-5 text-white" />
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Market') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">Pamilihang Bayan</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Malolos, Bulacan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
