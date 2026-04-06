<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    public string $formContactPhone = '';

    public function mount(): void
    {
        $vendor = Auth::user()->vendor;
        $this->formContactPhone = $vendor?->contact_phone ?? '';
    }

    #[Computed]
    public function vendor()
    {
        return Auth::user()->vendor?->load('stall');
    }

    #[Computed]
    public function market()
    {
        return Auth::user()->market;
    }

    #[Computed]
    public function marketAdmin()
    {
        return User::where('market_id', Auth::user()->market_id)
            ->where('role', 'admin')
            ->first();
    }

    #[Computed]
    public function permitExpiringWarning(): bool
    {
        if (! $this->vendor?->permit_expiry) return false;
        return $this->vendor->permit_expiry->lte(now()->addDays(30));
    }

    public function updateContact(): void
    {
        $this->validate([
            'formContactPhone' => ['nullable', 'string', 'max:20'],
        ]);

        $vendor = Auth::user()->vendor;
        if ($vendor) {
            $vendor->update(['contact_phone' => $this->formContactPhone ?: null]);
            $this->dispatch('toast', message: 'Contact information updated.', type: 'success');
        }
    }

    public function render()
    {
        return $this->view()->title(__('Vendor Profile'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('message') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vendor Profile') }}</flux:heading>
                <flux:subheading>{{ __('Your business details and permit information.') }}</flux:subheading>
            </div>
            @if($this->vendor)
            <flux:badge :color="$this->vendor->permit_status->color()" size="lg" icon="document-check">
                Permit {{ $this->vendor->permit_status->label() }}
            </flux:badge>
            @endif
        </div>

        {{-- Permit Expiry Warning --}}
        @if($this->permitExpiringWarning)
        <div class="rounded-2xl border border-red-200 bg-red-50/80 p-4 dark:border-red-900/50 dark:bg-red-900/10">
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                <div>
                    <p class="font-medium text-red-800 dark:text-red-200">{{ __('Permit Expiring Soon') }}</p>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Your permit expires on {{ $this->vendor->permit_expiry->format('F j, Y') }}.
                        Please contact the market administration for renewal.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if($this->vendor)
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
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->business_name }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Owner / Contact') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->contact_name }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Contact Number') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->contact_phone ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->email }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->stall?->stall_number ?? 'Unassigned' }}</dd>
                        </div>
                        <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Market') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->market?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Update Contact --}}
                <div class="border-t border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <form wire:submit="updateContact" class="flex items-end gap-3">
                        <div class="flex-1">
                            <flux:input wire:model="formContactPhone" :label="__('Update Contact Number')" placeholder="09XX-XXX-XXXX" />
                        </div>
                        <flux:button variant="primary" type="submit" size="sm">{{ __('Save') }}</flux:button>
                    </form>
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
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->permit_number ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                        <dd class="mt-2">
                            <flux:badge :color="$this->vendor->permit_status->color()" size="sm">{{ $this->vendor->permit_status->label() }}</flux:badge>
                        </dd>
                    </div>
                    @if($this->vendor->permit_expiry)
                    <div class="rounded-xl border border-orange-50 bg-orange-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Expiry Date') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->vendor->permit_expiry->format('F j, Y') }}</dd>
                    </div>
                    @endif
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
                <div class="grid gap-4 sm:grid-cols-2">
                    @if($this->marketAdmin)
                    <div class="flex items-center gap-4 rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm">{{ collect(explode(' ', $this->marketAdmin->name))->map(fn($w) => $w[0])->take(2)->implode('') }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Administrator') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->marketAdmin->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->marketAdmin->email }}</p>
                        </div>
                    </div>
                    @endif
                    @if($this->market)
                    <div class="flex items-center gap-4 rounded-xl border border-orange-100 p-4 dark:border-zinc-700">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-md">
                            <flux:icon.map-pin class="size-5 text-white" />
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Market') }}</p>
                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->market->name }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $this->market->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-12 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 text-center">
            <flux:icon.user-circle class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto" />
            <flux:heading size="lg" class="mt-4">{{ __('No Vendor Profile') }}</flux:heading>
            <flux:subheading class="mt-2">{{ __('Your vendor profile hasn\'t been set up yet. Please contact the market administrator.') }}</flux:subheading>
        </div>
        @endif
    </div>
</div>
