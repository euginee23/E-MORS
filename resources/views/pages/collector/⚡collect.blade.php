<?php

use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Collection;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    public ?int $formVendorId = null;
    public ?int $formStallId = null;
    public string $formStall = '';
    public string $formAmount = '';
    public string $formPaymentMethod = 'cash';
    public string $formPaymentDate = '';
    public string $formNotes = '';

    /**
     * Vendors already collected from today drop out of the picker so two collectors
     * working the same market cannot double-collect. Ticking this brings them back,
     * marked Paid, for the rare same-day second payment.
     */
    public bool $showCollectedToday = false;

    // Receipt shown after a successful collection.
    public ?int $lastCollectionId = null;
    public ?string $lastReceiptNumber = null;
    public ?string $lastEmailedTo = null;

    public function mount(): void
    {
        $this->formPaymentDate = now()->toDateString();
    }

    public function updatedFormVendorId(): void
    {
        $this->formStallId = null;
        $this->formStall = '';
        $this->formAmount = '';

        // A vendor renting exactly one stall needs no choice — preselect it.
        if ($this->formVendorId && $this->vendorStalls->count() === 1) {
            $this->formStallId = $this->vendorStalls->first()->id;
            $this->applySelectedStall();
        }
    }

    public function updatedFormStallId(): void
    {
        $this->applySelectedStall();
    }

    private function applySelectedStall(): void
    {
        $stall = $this->vendorStalls->firstWhere('id', (int) $this->formStallId);

        if (! $stall) {
            $this->formStall = '';
            $this->formAmount = '';

            return;
        }

        $this->formStall = $stall->stall_number;
        $this->formAmount = (string) $stall->monthly_rate;
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    /**
     * Vendor ids with a paid collection recorded today, by any collector.
     */
    #[Computed]
    public function collectedTodayVendorIds(): \Illuminate\Support\Collection
    {
        return Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('payment_date', today())
            ->pluck('vendor_id')
            ->unique()
            ->values();
    }

    /**
     * Every vendor holding a stall, regardless of whether they have paid today.
     */
    #[Computed]
    public function vendorsWithStalls(): \Illuminate\Support\Collection
    {
        return Vendor::where('market_id', $this->marketId)
            ->has('stalls')
            ->withCount('stalls')
            ->with('stalls')
            ->orderBy('contact_name')
            ->get();
    }

    /**
     * What the "Select a Vendor" dropdown actually offers.
     */
    #[Computed]
    public function selectableVendors(): \Illuminate\Support\Collection
    {
        if ($this->showCollectedToday) {
            return $this->vendorsWithStalls;
        }

        $collected = $this->collectedTodayVendorIds;

        return $this->vendorsWithStalls
            // Keep the current selection visible so the form does not blank out
            // the moment the payment being recorded marks them as collected.
            ->reject(fn ($vendor) => $collected->contains($vendor->id) && $vendor->id !== $this->formVendorId)
            ->values();
    }

    #[Computed]
    public function collectedTodayCount(): int
    {
        return $this->collectedTodayVendorIds->count();
    }

    public function updatedShowCollectedToday(): void
    {
        unset($this->selectableVendors);

        // A hidden vendor must not stay selected when the list shrinks again.
        if (! $this->showCollectedToday
            && $this->formVendorId
            && $this->collectedTodayVendorIds->contains($this->formVendorId)) {
            $this->formVendorId = null;
            $this->updatedFormVendorId();
        }
    }

    /**
     * Stalls belonging to the vendor currently selected in the form.
     */
    #[Computed]
    public function vendorStalls(): \Illuminate\Support\Collection
    {
        if (! $this->formVendorId) {
            return collect();
        }

        return $this->vendorsWithStalls->firstWhere('id', (int) $this->formVendorId)?->stalls
            ?? collect();
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
        return Vendor::where('market_id', $this->marketId)->has('stalls')->count();
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
            'formStallId' => ['required', 'integer'],
            'formAmount' => ['required', 'numeric', 'min:0.01'],
            'formPaymentDate' => ['required', 'date'],
            'formPaymentMethod' => ['required', 'string', 'max:50'],
            'formNotes' => ['nullable', 'string', 'max:500'],
        ], [
            'formStallId.required' => 'Please select which stall this payment is for.',
        ]);

        // Guard against a stall id that does not belong to the chosen vendor.
        $stall = $this->vendorStalls->firstWhere('id', (int) $this->formStallId);
        abort_unless($stall !== null, 422);

        $receiptNumber = Collection::generateReceiptNumber($this->marketId);

        $collection = Collection::create([
            'market_id' => $this->marketId,
            'vendor_id' => $this->formVendorId,
            'stall_id' => $stall->id,
            'collector_id' => Auth::id(),
            'receipt_number' => $receiptNumber,
            'amount' => $this->formAmount,
            'payment_date' => $this->formPaymentDate,
            'payment_method' => $this->formPaymentMethod,
            'status' => PaymentStatus::Paid,
            'notes' => $this->formNotes ?: null,
        ]);

        $this->lastCollectionId = $collection->id;
        $this->lastReceiptNumber = $receiptNumber;
        $this->lastEmailedTo = $this->sendReceiptEmail($collection);

        $this->dispatch('toast', message: 'Payment recorded successfully. Receipt: '.$receiptNumber, type: 'success');
        $this->resetForm();
        $this->clearCache();
    }

    /**
     * Email the receipt to the vendor. Returns the address it reached, or null when
     * the vendor has no linked account — a missing email must never fail the payment.
     */
    private function sendReceiptEmail(Collection $collection): ?string
    {
        $email = $collection->vendor?->user?->email;

        if (! $email) {
            return null;
        }

        try {
            $collection->load(['vendor', 'stall', 'collector', 'market']);
            Mail::to($email)->send(new PaymentReceipt($collection));

            return $email;
        } catch (\Throwable $e) {
            Log::error('Failed to email payment receipt.', [
                'collection_id' => $collection->id,
                'receipt_number' => $collection->receipt_number,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function resendReceiptEmail(): void
    {
        $collection = $this->lastCollection;

        if (! $collection) {
            return;
        }

        if (! $collection->vendor?->user?->email) {
            $this->dispatch('toast', message: 'This vendor has no email address on file.', type: 'error');

            return;
        }

        $sent = $this->sendReceiptEmail($collection);

        $this->lastEmailedTo = $sent;

        $this->dispatch(
            'toast',
            message: $sent ? "Receipt re-sent to {$sent}." : 'Could not send the receipt email. Please try again.',
            type: $sent ? 'success' : 'error',
        );
    }

    /**
     * The collection behind the receipt panel, re-fetched so printing always
     * reflects what is actually stored.
     */
    #[Computed]
    public function lastCollection(): ?Collection
    {
        if (! $this->lastCollectionId) {
            return null;
        }

        return Collection::where('market_id', $this->marketId)
            ->with(['vendor.user', 'stall', 'collector', 'market'])
            ->find($this->lastCollectionId);
    }

    public function dismissReceipt(): void
    {
        $this->lastCollectionId = null;
        $this->lastReceiptNumber = null;
        $this->lastEmailedTo = null;
        unset($this->lastCollection);
    }

    public function resetForm(): void
    {
        $this->formVendorId = null;
        $this->formStallId = null;
        $this->formStall = '';
        $this->formAmount = '';
        $this->formPaymentDate = now()->toDateString();
        $this->formPaymentMethod = 'cash';
        $this->formNotes = '';
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset(
            $this->todayTotal,
            $this->todayCount,
            $this->progressPercent,
            $this->recentCollections,
            $this->collectedTodayVendorIds,
            $this->collectedTodayCount,
            $this->selectableVendors,
            $this->vendorStalls,
            $this->lastCollection,
        );
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

        {{-- Receipt for the payment just recorded. Only the collector sees this;
             the admin's collections page is view-only with no print action. --}}
        @if($this->lastCollection)
        @php $receipt = $this->lastCollection; @endphp
        <div id="collector-receipt" class="rounded-2xl border-2 border-emerald-200 bg-emerald-50/60 p-6 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-900/20">
            <div class="receipt-sheet">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <flux:icon.check-circle class="mt-0.5 size-6 shrink-0 text-emerald-600 dark:text-emerald-400 print:hidden" />
                        <div class="min-w-0">
                            <flux:heading size="lg" class="text-emerald-900 dark:text-emerald-100">{{ __('Payment Recorded') }}</flux:heading>
                            <p class="mt-0.5 font-mono text-sm font-bold wrap-break-word text-emerald-800 dark:text-emerald-200">{{ $receipt->receipt_number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300">₱ {{ number_format($receipt->amount, 2) }}</p>
                        <p class="text-xs text-emerald-700/70 dark:text-emerald-400/70">{{ $receipt->payment_date?->format('M j, Y') }}</p>
                    </div>
                </div>

                <dl class="mt-5 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</dt>
                        <dd class="text-right font-medium wrap-break-word text-zinc-900 dark:text-zinc-100">{{ $receipt->vendor?->contact_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Business') }}</dt>
                        <dd class="text-right font-medium wrap-break-word text-zinc-900 dark:text-zinc-100">{{ $receipt->vendor?->business_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</dt>
                        <dd class="text-right font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $receipt->stall?->stall_number ?? '—' }}
                            @if($receipt->stall?->section) ({{ __('Sec') }} {{ $receipt->stall->section }}) @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</dt>
                        <dd class="text-right font-medium text-zinc-900 dark:text-zinc-100">{{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</dt>
                        <dd class="text-right font-bold text-zinc-900 dark:text-zinc-100">₱ {{ number_format($receipt->amount, 2) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-b border-emerald-100 pb-2 dark:border-emerald-900/40">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Received By') }}</dt>
                        <dd class="text-right font-medium wrap-break-word text-zinc-900 dark:text-zinc-100">{{ $receipt->collector?->name ?? '—' }}</dd>
                    </div>
                </dl>

                {{-- Only meaningful on paper. --}}
                <div class="mt-8 hidden print:block">
                    <div class="w-64 border-t border-zinc-400 pt-1 text-xs text-zinc-600">{{ __('Vendor Signature') }}</div>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
                <p class="text-xs">
                    @if($this->lastEmailedTo)
                    <span class="text-emerald-700 dark:text-emerald-400">✉ {{ __('Emailed to') }} <span class="font-medium wrap-break-word">{{ $this->lastEmailedTo }}</span></span>
                    @elseif($receipt->vendor?->user?->email)
                    <span class="text-amber-600 dark:text-amber-400">{{ __('Email could not be sent.') }}</span>
                    @else
                    <span class="text-zinc-500 dark:text-zinc-400">{{ __('This vendor has no email address on file.') }}</span>
                    @endif
                </p>
                <div class="flex flex-wrap gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="dismissReceipt">{{ __('Dismiss') }}</flux:button>
                    @if($receipt->vendor?->user?->email)
                    <flux:button size="sm" variant="outline" icon="envelope" wire:click="resendReceiptEmail" wire:loading.attr="disabled" wire:target="resendReceiptEmail">
                        <span wire:loading.remove wire:target="resendReceiptEmail">{{ $this->lastEmailedTo ? __('Resend Email') : __('Send Email') }}</span>
                        <span wire:loading wire:target="resendReceiptEmail">{{ __('Sending…') }}</span>
                    </flux:button>
                    @endif
                    <flux:button size="sm" variant="primary" icon="printer" x-on:click="window.print()">{{ __('Print Receipt') }}</flux:button>
                </div>
            </div>
        </div>

        {{-- Printing this page yields the receipt alone, on a clean sheet. --}}
        <style>
            @media print {
                body * { visibility: hidden !important; }
                #collector-receipt, #collector-receipt * { visibility: visible !important; }
                #collector-receipt {
                    position: absolute; inset: 0 auto auto 0; width: 100%;
                    border: 0 !important; background: #fff !important; padding: 0 !important; box-shadow: none !important;
                }
                #collector-receipt .receipt-sheet { color: #000 !important; }
            }
        </style>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Collection Form --}}
            <div class="lg:col-span-2 rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Collection Details') }}</flux:heading>
                </div>
                <div class="p-6">
                    <form wire:submit="save" class="space-y-5">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <flux:select wire:model.live="formVendorId" :label="__('Select a Vendor')" required>
                                    <flux:select.option :value="null">{{ __('— Select Vendor —') }}</flux:select.option>
                                    @foreach($this->selectableVendors as $vendor)
                                    @php $paidToday = $this->collectedTodayVendorIds->contains($vendor->id); @endphp
                                    <flux:select.option :value="$vendor->id">
                                        {{ $vendor->contact_name }}
                                        @if($vendor->stalls_count > 1)
                                            — {{ trans_choice(':count stall|:count stalls', $vendor->stalls_count, ['count' => $vendor->stalls_count]) }}
                                        @else
                                            — {{ $vendor->stalls->first()?->stall_number }}
                                        @endif
                                        @if($paidToday) · {{ __('PAID') }} @endif
                                    </flux:select.option>
                                    @endforeach
                                </flux:select>

                                @if($this->collectedTodayCount > 0)
                                <label class="mt-2 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    <input type="checkbox" wire:model.live="showCollectedToday"
                                           class="rounded border-zinc-300 text-orange-500 focus:ring-orange-400/30 dark:border-zinc-600 dark:bg-zinc-800" />
                                    {{ __('Show vendors already collected today') }}
                                    <span class="font-medium text-zinc-600 dark:text-zinc-300">({{ $this->collectedTodayCount }})</span>
                                </label>
                                @endif

                                @if(! $this->showCollectedToday && $this->collectedTodayCount > 0)
                                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">
                                    {{ trans_choice(
                                        ':count vendor already collected today is hidden.|:count vendors already collected today are hidden.',
                                        $this->collectedTodayCount,
                                        ['count' => $this->collectedTodayCount]
                                    ) }}
                                </p>
                                @endif
                            </div>
                            <div>
                                @if($this->vendorStalls->count() > 1)
                                {{-- This vendor rents several stalls, so the payment must name one. --}}
                                <flux:select wire:model.live="formStallId" :label="__('Stall')" required>
                                    <flux:select.option :value="null">{{ __('— Select Stall —') }}</flux:select.option>
                                    @foreach($this->vendorStalls as $vendorStall)
                                    <flux:select.option :value="$vendorStall->id">
                                        {{ $vendorStall->stall_number }} — {{ $vendorStall->section }} (₱{{ number_format($vendorStall->monthly_rate, 2) }}/mo)
                                    </flux:select.option>
                                    @endforeach
                                </flux:select>
                                @error('formStallId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                @else
                                <flux:input wire:model="formStall" :label="__('Stall')" placeholder="Auto-filled from vendor" disabled />
                                @endif
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
