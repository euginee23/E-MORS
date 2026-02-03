<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Welcome back!')" :description="__('Sign in to access your market dashboard')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
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
                    placeholder="email@example.com"
                    class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
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
                    class="w-full px-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
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
                class="w-full py-3.5 px-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5 mt-2"
                data-test="login-button"
            >
                {{ __('Sign in') }}
            </button>
        </form>

        @if (Route::has('register'))
            <div class="text-center text-sm text-zinc-600 dark:text-zinc-400 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <span>{{ __('Don\'t have an account?') }}</span>
                <a href="{{ route('register') }}" class="font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 ml-1 transition-colors" wire:navigate>
                    {{ __('Create one') }}
                </a>
            </div>
        @endif
    </div>
</x-layouts::auth>
