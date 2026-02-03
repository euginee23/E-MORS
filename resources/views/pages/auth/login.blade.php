<x-layouts.auth.landing title="Sign In - E-MORS">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
        <!-- Left Side - Info -->
        <div class="lg:sticky lg:top-32 order-2 lg:order-1">
            <div class="space-y-6 lg:space-y-8">
                <div>
                    <span class="inline-block px-4 py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-sm font-bold rounded-full mb-4 lg:mb-6">
                        👋 Welcome Back
                    </span>
                    <h1 class="text-3xl lg:text-4xl xl:text-5xl font-black text-zinc-900 dark:text-white leading-tight mb-3 lg:mb-4">
                        Sign In to Your
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500">E-MORS Account</span>
                    </h1>
                    <p class="text-base lg:text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Access your market dashboard to manage vendors, track revenue, and monitor your public market operations in real-time.
                    </p>
                </div>

                <!-- Quick Access Features -->
                <div class="space-y-3 lg:space-y-4">
                    <div class="flex items-start gap-3 lg:gap-4 p-3 lg:p-4 bg-white dark:bg-zinc-900 rounded-xl lg:rounded-2xl border border-orange-100 dark:border-zinc-800">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/30 dark:to-amber-900/30 rounded-lg lg:rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white mb-0.5 lg:mb-1 text-sm lg:text-base">Dashboard Overview</h3>
                            <p class="text-xs lg:text-sm text-zinc-600 dark:text-zinc-400">Real-time stats on vendors, collections, and occupancy</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 lg:gap-4 p-3 lg:p-4 bg-white dark:bg-zinc-900 rounded-xl lg:rounded-2xl border border-orange-100 dark:border-zinc-800">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-lg lg:rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white mb-0.5 lg:mb-1 text-sm lg:text-base">Collection Tracking</h3>
                            <p class="text-xs lg:text-sm text-zinc-600 dark:text-zinc-400">Monitor daily fee collection with digital receipts</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 lg:gap-4 p-3 lg:p-4 bg-white dark:bg-zinc-900 rounded-xl lg:rounded-2xl border border-orange-100 dark:border-zinc-800">
                        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-lg lg:rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-zinc-900 dark:text-white mb-0.5 lg:mb-1 text-sm lg:text-base">Stall Management</h3>
                            <p class="text-xs lg:text-sm text-zinc-600 dark:text-zinc-400">Visual market mapping with allocation management</p>
                        </div>
                    </div>
                </div>

                <!-- Security Note -->
                <div class="hidden lg:flex items-center gap-3 p-4 bg-zinc-100 dark:bg-zinc-900 rounded-2xl">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        <span class="font-semibold text-zinc-900 dark:text-white">Secure & Protected</span> — Your data is encrypted and stored securely
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl lg:rounded-3xl shadow-xl border border-orange-100 dark:border-zinc-800 p-6 lg:p-8 xl:p-10 order-1 lg:order-2">
            <div class="mb-6 lg:mb-8">
                <h2 class="text-xl lg:text-2xl font-bold text-zinc-900 dark:text-white mb-1 lg:mb-2">Sign In</h2>
                <p class="text-sm lg:text-base text-zinc-600 dark:text-zinc-400">Enter your credentials to access your dashboard</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="text-center mb-6" :status="session('status')" />

            <form method="POST" action="{{ route('login.store') }}" class="space-y-5 lg:space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                        {{ __('Email address') }}
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email"
                        value="{{ old('email') }}"
                        required 
                        autofocus 
                        autocomplete="email"
                        placeholder="admin@market.gov.ph"
                        class="w-full px-4 py-3 lg:py-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all text-base"
                    />
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Password') }}
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition-colors" wire:navigate>
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        required 
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full px-4 py-3 lg:py-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all text-base"
                    />
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        {{ old('remember') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-orange-600 focus:ring-orange-500 dark:bg-zinc-700"
                    />
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Remember me') }}</span>
                </label>

                <button 
                    type="submit" 
                    class="w-full py-3.5 lg:py-4 px-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-base lg:text-lg rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5 mt-2"
                    data-test="login-button"
                >
                    {{ __('Sign In') }}
                </button>
            </form>

            @if (Route::has('register'))
                <div class="text-center text-sm text-zinc-600 dark:text-zinc-400 pt-5 lg:pt-6 mt-5 lg:mt-6 border-t border-zinc-100 dark:border-zinc-800">
                    <span>{{ __('Don\'t have an account?') }}</span>
                    <a href="{{ route('register') }}" class="font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 ml-1 transition-colors" wire:navigate>
                        {{ __('Register your market') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.auth.landing>
