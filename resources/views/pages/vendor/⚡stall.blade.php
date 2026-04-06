<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    #[Computed]
    public function vendor()
    {
        return Auth::user()->vendor?->load('stall');
    }

    #[Computed]
    public function stall()
    {
        return $this->vendor?->stall;
    }

    #[Computed]
    public function marketAdmin()
    {
        return User::where('market_id', Auth::user()->market_id)
            ->where('role', 'admin')
            ->first();
    }

    #[Computed]
    public function market()
    {
        return Auth::user()->market;
    }

    public function render()
    {
        return $this->view()->title(__('My Stall'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Stall') }}</flux:heading>
                <flux:subheading>{{ __('Your assigned stall information and details.') }}</flux:subheading>
            </div>
            @if($this->stall)
            <flux:badge color="lime" size="lg" icon="building-storefront">Stall {{ $this->stall->stall_number }}</flux:badge>
            @endif
        </div>

        @if($this->stall)
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
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->stall->stall_number }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">Section {{ $this->stall->section }}</flux:text>
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
                    <flux:heading size="xl" class="text-2xl font-bold">{{ $this->stall->size }}</flux:heading>
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
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ number_format($this->stall->monthly_rate, 0) }}</flux:heading>
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
                    <flux:badge :color="$this->stall->status->color()" size="sm">{{ $this->stall->status->label() }}</flux:badge>
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
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->stall->stall_number }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Section {{ $this->stall->section }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Size') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->stall->size }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Monthly Rate') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">₱ {{ number_format($this->stall->monthly_rate, 2) }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Business Name') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor?->business_name ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Market') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->market?->name ?? '—' }}</dd>
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
                    @if($this->marketAdmin)
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">{{ collect(explode(' ', $this->marketAdmin->name))->map(fn($w) => $w[0])->take(2)->implode('') }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->marketAdmin->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Market Administrator</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <flux:icon.envelope class="size-4 text-zinc-400" />
                            <span class="text-zinc-600 dark:text-zinc-400">{{ $this->marketAdmin->email }}</span>
                        </div>
                    </div>
                    @endif

                    @if($this->market)
                    <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Market Address') }}</p>
                        <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $this->market->address }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @else
        {{-- No Stall Assigned --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-12 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 text-center">
            <flux:icon.building-storefront class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto" />
            <flux:heading size="lg" class="mt-4">{{ __('No Stall Assigned') }}</flux:heading>
            <flux:subheading class="mt-2">{{ __('You don\'t have a stall assigned yet. Please contact the market administration.') }}</flux:subheading>
        </div>
        @endif
    </div>
</div>
