<?php

use App\Models\Market;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

new class extends Component {

    public Market $market;

    // Personal info
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $contact_phone = '';
    public string $address = '';

    // Business info
    public string $business_name = '';
    public string $product_type = '';

    public bool $submitted = false;

    public function mount(Market $market): void
    {
        $this->market = $market;
    }

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'contact_phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'business_name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', 'max:100'],
        ], [
            'email.unique' => 'This email already has an account. Please sign in to check your application status.',
        ]);

        $user = DB::transaction(function () {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => 'vendor',
                'market_id' => $this->market->id,
            ]);

            Vendor::create([
                'market_id' => $this->market->id,
                'user_id' => $user->id,
                'business_name' => $this->business_name,
                'contact_name' => $this->name,
                'contact_phone' => $this->contact_phone,
                'address' => $this->address,
                'product_type' => $this->product_type,
                'permit_status' => 'pending',
            ]);

            return $user;
        });

        // Send OTP verification email
        $user->sendEmailVerificationNotification();

        // Log the user in and redirect to OTP verification
        Auth::login($user);

        $this->redirect(route('verification.notice'), navigate: false);
    }

    public function render()
    {
        return $this->view()
            ->layout('layouts.public')
            ->title('Vendor Application — ' . $this->market->name . ' — E-MORS');
    }
}; ?>

<div class="min-h-screen bg-orange-50 dark:bg-zinc-950 antialiased">
    <!-- Background decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-0 w-96 h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-40 -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-40 translate-y-1/2 -translate-x-1/2"></div>
    </div>

    <div class="relative mx-auto max-w-3xl px-4 py-10 sm:py-16">
        <!-- Back link -->
        <a href="{{ route('home') }}#markets" class="mb-6 inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-orange-600 dark:text-zinc-400 dark:hover:text-orange-400 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to markets
        </a>

        <!-- Header card -->
        <div class="mb-6 rounded-2xl border border-orange-200 bg-gradient-to-r from-orange-500 to-amber-500 p-6 text-white dark:border-orange-800">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-white/70">Vendor Application</div>
                    <h1 class="text-xl font-black">{{ $market->name }}</h1>
                    <p class="mt-0.5 flex items-center gap-1 text-sm text-white/80">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $market->address }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Form card -->
        <div class="rounded-2xl border border-orange-100 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <form wire:submit="submit" class="divide-y divide-orange-100 dark:divide-zinc-800">

                <!-- Section: Account Information -->
                <div class="p-6 sm:p-8">
                    <h2 class="mb-1 text-base font-bold text-zinc-900 dark:text-zinc-100">Account Information</h2>
                    <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">Create your login credentials for the vendor portal.</p>

                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Full Name <span class="text-red-500">*</span></label>
                                <input wire:model="name" type="text" placeholder="Juan dela Cruz"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email Address <span class="text-red-500">*</span></label>
                                <input wire:model="email" type="email" placeholder="you@example.com"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password <span class="text-red-500">*</span></label>
                                <input wire:model="password" type="password" placeholder="Min. 8 characters"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                                @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm Password <span class="text-red-500">*</span></label>
                                <input wire:model="password_confirmation" type="password" placeholder="Repeat password"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Contact & Location -->
                <div class="p-6 sm:p-8">
                    <h2 class="mb-1 text-base font-bold text-zinc-900 dark:text-zinc-100">Contact & Location</h2>
                    <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">Your contact details and residential address.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Phone <span class="text-red-500">*</span></label>
                            <input wire:model="contact_phone" type="tel" placeholder="09XX-XXX-XXXX"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                            @error('contact_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Home Address <span class="text-red-500">*</span></label>
                            <textarea wire:model="address" rows="2" placeholder="House/Unit No., Street, Barangay, City/Municipality, Province"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500 resize-none"></textarea>
                            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Business Information -->
                <div class="p-6 sm:p-8">
                    <h2 class="mb-1 text-base font-bold text-zinc-900 dark:text-zinc-100">Business Information</h2>
                    <p class="mb-5 text-sm text-zinc-500 dark:text-zinc-400">Tell us about what you sell at the market.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Business / Trade Name <span class="text-red-500">*</span></label>
                            <input wire:model="business_name" type="text" placeholder="e.g. Santos Fish Stall"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                            @error('business_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Product Type / Goods Sold <span class="text-red-500">*</span></label>
                            <select wire:model="product_type"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                                <option value="">Select a category…</option>
                                <option value="Dry Goods">Dry Goods</option>
                                <option value="Seafood">Seafood</option>
                                <option value="Meat">Meat</option>
                                <option value="Fruits & Vegetables">Fruits & Vegetables</option>
                                <option value="Food & Beverages">Food & Beverages</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('product_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        By submitting, you agree that the market admin will review your application and assign a stall. You will receive an email notification when approved.
                    </p>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="flex-shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 px-8 py-3 font-bold text-white shadow-lg shadow-orange-500/25 transition-all hover:from-orange-600 hover:to-amber-600 hover:-translate-y-0.5 hover:shadow-orange-500/40 disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submit">Submit Application</span>
                        <span wire:loading wire:target="submit">Submitting…</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Info note -->
        <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/40 dark:bg-blue-900/20">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Already have an account?</strong>
                <a href="{{ route('login') }}" class="ml-1 font-semibold underline hover:text-blue-900 dark:hover:text-blue-100">Sign in here</a>
            </p>
        </div>
    </div>
</div>
