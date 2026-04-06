<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    public ?int $formVendorId = null;
    public string $formStall = '';
    public string $formAmount = '';
    public string $formPaymentMethod = 'cash';
    public string $formPaymentDate = '';
    public string $formNotes = '';

    public ?string $lastReceiptNumber = null;

    public function mount(): void
    {
        $this->formPaymentDate = now()->toDateString();
    }

    public function updatedFormVendorId(): void
    {
        if ($this->formVendorId) {
            $vendor = Vendor::with('stall')->find($this->formVendorId);
            if ($vendor?->stall) {
                $this->formStall = $vendor->stall->stall_number;
                $this->formAmount = (string) $vendor->stall->monthly_rate;
            }
        } else {
            $this->formStall = '';
            $this->formAmount = '';
        }
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function vendorsWithStalls(): \Illuminate\Support\Collection
    {
        return Vendor::where('market_id', $this->marketId)
            ->has('stall')
            ->with('stall')
            ->orderBy('contact_name')
            ->get();
    }

    #[Computed]
    public function todayTotal(): string
    {
        $total = Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->sum('amount');
        return number_format($total, 0);
    }

    #[Computed]
    public function todayCount(): int
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->count();
    }

    #[Computed]
    public function totalVendors(): int
    {
        return Vendor::where('market_id', $this->marketId)->has('stall')->count();
    }

    #[Computed]
    public function progressPercent(): int
    {
        if ($this->totalVendors === 0) return 0;
        return (int) round(($this->todayCount / $this->totalVendors) * 100);
    }

    #[Computed]
    public function recentCollections()
    {
        return Collection::where('market_id', $this->marketId)
            ->where('collector_id', Auth::id())
            ->whereDate('payment_date', today())
            ->with(['vendor', 'stall'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function save(): void
    {
        $this->validate([
            'formVendorId' => ['required', 'exists:vendors,id'],
            'formAmount' => ['required', 'numeric', 'min:0.01'],
            'formPaymentDate' => ['required', 'date'],
            'formPaymentMethod' => ['required', 'string', 'max:50'],
            'formNotes' => ['nullable', 'string', 'max:500'],
        ]);

        $vendor = Vendor::with('stall')->findOrFail($this->formVendorId);

        $receiptNumber = Collection::generateReceiptNumber($this->marketId);

        Collection::create([
            'market_id' => $this->marketId,
            'vendor_id' => $this->formVendorId,
            'stall_id' => $vendor->stall?->id,
            'collector_id' => Auth::id(),
            'receipt_number' => $receiptNumber,
            'amount' => $this->formAmount,
            'payment_date' => $this->formPaymentDate,
            'payment_method' => $this->formPaymentMethod,
            'status' => PaymentStatus::Paid,
            'notes' => $this->formNotes ?: null,
        ]);

        $this->lastReceiptNumber = $receiptNumber;
        session()->flash('message', 'Payment recorded successfully. Receipt: ' . $receiptNumber);
        $this->resetForm();
        $this->clearCache();
    }

    public function resetForm(): void
    {
        $this->formVendorId = null;
        $this->formStall = '';
        $this->formAmount = '';
        $this->formPaymentDate = now()->toDateString();
        $this->formPaymentMethod = 'cash';
        $this->formNotes = '';
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->todayTotal, $this->todayCount, $this->progressPercent, $this->recentCollections);
    }

    public function render()
    {
        return $this->view()->title(__('Record Collection'));
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
                    <form wire:submit="save" class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <flux:select wire:model.live="formVendorId" :label="__('Vendor')" required>
                                <flux:select.option :value="null">{{ __('— Select Vendor —') }}</flux:select.option>
                                @foreach($this->vendorsWithStalls as $vendor)
                                <flux:select.option :value="$vendor->id">{{ $vendor->contact_name }} — {{ $vendor->stall->stall_number }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <div>
                                <flux:input wire:model="formStall" :label="__('Stall')" placeholder="Auto-filled from vendor" disabled />
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <flux:input wire:model="formAmount" :label="__('Amount (₱)')" type="number" step="0.01" min="0" required />
                            <flux:select wire:model="formPaymentMethod" :label="__('Payment Method')">
                                <flux:select.option value="cash">{{ __('Cash') }}</flux:select.option>
                                <flux:select.option value="gcash">{{ __('GCash') }}</flux:select.option>
                                <flux:select.option value="bank_transfer">{{ __('Bank Transfer') }}</flux:select.option>
                            </flux:select>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <flux:input wire:model="formPaymentDate" :label="__('Collection Date')" type="date" required />
                            <div></div>
                        </div>

                        <flux:textarea wire:model="formNotes" :label="__('Notes (Optional)')" placeholder="Any additional notes about this collection..." rows="3" />

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <flux:button variant="subtle" type="button" wire:click="resetForm">{{ __('Clear Form') }}</flux:button>
                            <flux:button variant="primary" type="submit" icon="check">{{ __('Record Collection') }}</flux:button>
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
                            <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">₱ {{ $this->todayTotal }}</p>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">collected today</p>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $this->todayCount }} of {{ $this->totalVendors }} vendors</span>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->progressPercent }}%</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-500" style="width: {{ $this->progressPercent }}%"></div>
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
                        @forelse($this->recentCollections as $rc)
                        <div class="px-6 py-3 flex items-center justify-between" wire:key="rc-{{ $rc->id }}">
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $rc->vendor?->contact_name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $rc->stall?->stall_number }} · {{ $rc->created_at->format('g:i A') }}</p>
                            </div>
                            <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">₱ {{ number_format($rc->amount, 0) }}</span>
                        </div>
                        @empty
                        <div class="px-6 py-6 text-center text-sm text-zinc-500">
                            {{ __('No collections today yet.') }}
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
