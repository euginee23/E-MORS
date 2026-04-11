<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased bg-orange-50 dark:bg-zinc-950 min-h-screen">

        <!-- Background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-30 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-30 translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12">
            <div class="w-full max-w-lg">

                <!-- Logo -->
                <div class="flex justify-center mb-8">
                    <x-app-logo />
                </div>

                <!-- Card -->
                <div class="rounded-2xl border border-orange-200 dark:border-zinc-700 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm shadow-xl p-8 text-center">

                    <!-- Hourglass icon -->
                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                        <svg class="h-10 w-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h1 class="text-2xl font-black text-zinc-900 dark:text-white">Application Under Review</h1>
                    <p class="mt-2 text-zinc-500 dark:text-zinc-400 text-sm leading-relaxed">
                        Your vendor application has been received. Our team is currently reviewing it.
                        You will receive an email notification once you are approved and assigned to a stall.
                    </p>

                    <div class="mt-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-left">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">What unlocks your dashboard</p>
                        <ul class="mt-2 space-y-1 text-xs text-amber-700 dark:text-amber-200">
                            <li>1. Your stall is assigned by the market admin.</li>
                            <li>2. Your permit status is marked active.</li>
                            <li>3. You can then access your vendor dashboard.</li>
                        </ul>
                    </div>

                    <!-- Application details -->
                    @php $vendor = auth()->user()->vendor; @endphp
                    @if ($vendor)
                    <div class="mt-6 rounded-xl border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-4 text-left space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Business</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $vendor->business_name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Market</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $vendor->market->name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Product Type</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $vendor->product_type }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Status</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-0.5 text-xs font-semibold text-yellow-700 dark:text-yellow-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                                Pending Review
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="mt-6">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors">
                            Sign Out
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-zinc-400">
                    Questions? Contact the market administrator.
                </p>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
