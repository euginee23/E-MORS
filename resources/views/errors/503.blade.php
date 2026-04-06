<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Under Maintenance &mdash; E-MORS</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,900" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-orange-50 dark:bg-zinc-950 min-h-screen" style="font-family: 'Instrument Sans', sans-serif;">

        <!-- Decorative Background Pattern -->
        <div style="position:fixed;inset:0;overflow:hidden;pointer-events:none;">
            <div style="position:absolute;top:0;right:0;width:24rem;height:24rem;background:rgba(254,215,170,0.3);border-radius:9999px;filter:blur(64px);transform:translate(50%,-50%);"></div>
            <div style="position:absolute;bottom:0;left:0;width:24rem;height:24rem;background:rgba(253,230,138,0.3);border-radius:9999px;filter:blur(64px);transform:translate(-50%,50%);"></div>
        </div>

        <div class="relative min-h-screen flex flex-col">
            <!-- Minimal Navbar -->
            <header class="border-b border-orange-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center h-16 lg:h-20">
                        <div class="flex items-center gap-2 sm:gap-4">
                            <div class="relative">
                                <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-xl sm:rounded-2xl rotate-3 shadow-lg"></div>
                                <div class="absolute inset-0 w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-600 via-amber-600 to-yellow-600 rounded-xl sm:rounded-2xl -rotate-3 flex items-center justify-center">
                                    <span class="text-white font-black text-sm sm:text-lg">EP</span>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-lg sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">E-MORS</h1>
                                <p class="text-[10px] sm:text-xs text-orange-600 dark:text-orange-400 font-medium tracking-widest uppercase">E-Palengke System</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Error Content -->
            <main class="flex-1 flex items-center justify-center px-4 py-16 sm:py-24">
                <div class="text-center max-w-lg mx-auto">
                    <!-- Error Code -->
                    <div class="relative inline-block mb-6">
                        <span class="text-[8rem] sm:text-[10rem] font-black leading-none text-orange-100 dark:text-zinc-800 select-none">503</span>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl sm:rounded-3xl rotate-6 shadow-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mb-3">Under Maintenance</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm sm:text-base leading-relaxed mb-4">
                        E-MORS is currently undergoing scheduled maintenance to improve your experience. We'll be back shortly.
                    </p>

                    @if(isset($exception) && $exception->getMessage())
                        <p class="text-xs text-orange-600 dark:text-orange-400 font-medium mb-8 px-4 py-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg inline-block">
                            {{ $exception->getMessage() }}
                        </p>
                    @else
                        <div class="mb-8"></div>
                    @endif

                    <!-- Animated dots -->
                    <div class="flex items-center justify-center gap-2 mb-8">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-400 animate-bounce" style="animation-delay: 0ms;"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-bounce" style="animation-delay: 150ms;"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 animate-bounce" style="animation-delay: 300ms;"></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button onclick="window.location.reload()"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl font-semibold text-sm hover:from-orange-600 hover:to-amber-600 transition-colors shadow-md shadow-orange-200 dark:shadow-orange-900/30 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Check Again
                        </button>
                    </div>
                </div>
            </main>

            <!-- Minimal Footer -->
            <footer class="border-t border-orange-200 dark:border-zinc-800 py-6 text-center text-xs text-zinc-400 dark:text-zinc-600">
                &copy; {{ date('Y') }} E-MORS &mdash; E-Palengke Market Operations and Revenue System
            </footer>
        </div>

    </body>
</html>
