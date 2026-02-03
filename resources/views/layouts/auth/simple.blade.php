<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-orange-50 dark:bg-zinc-950 antialiased">
        <!-- Decorative Background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                    <div class="relative">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-2xl rotate-3 shadow-lg"></div>
                        <div class="absolute inset-0 w-14 h-14 bg-gradient-to-br from-orange-600 via-amber-600 to-yellow-600 rounded-2xl -rotate-3 flex items-center justify-center">
                            <span class="text-white font-black text-xl">EP</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">E-MORS</h1>
                        <p class="text-xs text-orange-600 dark:text-orange-400 font-medium tracking-widest uppercase">E-Palengke System</p>
                    </div>
                </a>

                <!-- Auth Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl border border-orange-100 dark:border-zinc-800 p-8">
                    {{ $slot }}
                </div>

                <!-- Back to home link -->
                <div class="text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to home
                    </a>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
