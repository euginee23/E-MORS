<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Check your email')"
            :description="__('We sent a 6-digit code to :email', ['email' => auth()->user()->email])"
        />

        {{-- Icon --}}
        <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/30 dark:to-amber-900/30 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        {{-- Success flash --}}
        @if(session('status') == 'verification-link-sent')
            <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <p class="text-center text-sm font-medium text-green-700 dark:text-green-400">
                    {{ __('A new verification code has been sent to your email address.') }}
                </p>
            </div>
        @endif

        {{-- OTP form --}}
        <form method="POST" action="{{ route('verification.code') }}" x-data="otpInput()" x-init="init()">
            @csrf

            <div class="flex flex-col gap-5">
                {{-- 6-digit boxes --}}
                <div>
                    <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-center mb-3">
                        {{ __('Enter verification code') }}
                    </label>

                    <div class="flex justify-center gap-2 sm:gap-3">
                        @for($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            pattern="[0-9]"
                            class="w-11 h-14 sm:w-12 sm:h-16 text-center text-2xl font-bold rounded-xl border-2
                                   bg-orange-50 dark:bg-zinc-800
                                   border-orange-200 dark:border-zinc-600
                                   text-orange-700 dark:text-orange-300
                                   focus:outline-none focus:border-orange-500 dark:focus:border-orange-400
                                   focus:ring-2 focus:ring-orange-500/20 dark:focus:ring-orange-400/20
                                   transition-all"
                            x-ref="digit{{ $i }}"
                            @input="onInput($event, {{ $i }})"
                            @keydown="onKeydown($event, {{ $i }})"
                            @paste.prevent="onPaste($event)"
                            autocomplete="one-time-code"
                        />
                        @endfor
                    </div>

                    {{-- Hidden combined input --}}
                    <input type="hidden" name="code" x-bind:value="fullCode" />

                    @error('code')
                        <p class="mt-3 text-sm text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5"
                >
                    {{ __('Verify Email') }}
                </button>
            </div>
        </form>

        {{-- Resend --}}
        <div class="text-center space-y-2">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __("Didn't receive a code?") }}</p>
            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="text-sm font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition-colors"
                >
                    {{ __('Resend code') }}
                </button>
            </form>
        </div>

        {{-- Log out --}}
        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button
                type="submit"
                class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
            >
                {{ __('Use a different account') }}
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        function otpInput() {
            return {
                digits: ['', '', '', '', '', ''],
                get fullCode() {
                    return this.digits.join('');
                },
                init() {
                    this.$nextTick(() => this.$refs.digit0.focus());
                },
                onInput(e, index) {
                    const val = e.target.value.replace(/\D/g, '');
                    e.target.value = val.slice(-1);
                    this.digits[index] = e.target.value;
                    if (e.target.value && index < 5) {
                        this.$refs['digit' + (index + 1)].focus();
                    }
                },
                onKeydown(e, index) {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        this.digits[index - 1] = '';
                        this.$refs['digit' + (index - 1)].value = '';
                        this.$refs['digit' + (index - 1)].focus();
                    }
                },
                onPaste(e) {
                    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                    text.split('').forEach((char, i) => {
                        this.digits[i] = char;
                        if (this.$refs['digit' + i]) {
                            this.$refs['digit' + i].value = char;
                        }
                    });
                    const next = Math.min(text.length, 5);
                    this.$refs['digit' + next].focus();
                },
            };
        }
    </script>
    @endpush
</x-layouts::auth>
