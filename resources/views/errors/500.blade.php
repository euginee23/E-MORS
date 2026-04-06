<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Server Error &mdash; E-MORS</title>
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
                        <a href="{{ url('/') }}" class="flex items-center gap-2 sm:gap-4 group">
                            <div class="relative">
                                <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-xl sm:rounded-2xl rotate-3 shadow-lg group-hover:rotate-6 transition-transform"></div>
                                <div class="absolute inset-0 w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-600 via-amber-600 to-yellow-600 rounded-xl sm:rounded-2xl -rotate-3 flex items-center justify-center group-hover:-rotate-6 transition-transform">
                                    <span class="text-white font-black text-sm sm:text-lg">EP</span>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-lg sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">E-MORS</h1>
                                <p class="text-[10px] sm:text-xs text-orange-600 dark:text-orange-400 font-medium tracking-widest uppercase">E-Palengke System</p>
                            </div>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Error Content -->
            <main class="flex-1 flex items-center justify-center px-4 py-16 sm:py-24">
                <div class="text-center max-w-lg mx-auto">
                    <!-- Error Code -->
                    <div class="relative inline-block mb-6">
                        <span class="text-[8rem] sm:text-[10rem] font-black leading-none text-orange-100 dark:text-zinc-800 select-none">500</span>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-red-500 to-orange-600 rounded-2xl sm:rounded-3xl rotate-6 shadow-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mb-3">Something Went Wrong</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm sm:text-base leading-relaxed mb-8">
                        An unexpected error occurred on our server. Our team has been notified. Please try again in a few moments.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button onclick="window.location.reload()"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 rounded-xl font-semibold text-sm hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Try Again
                        </button>
                        <a href="{{ url('/') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl font-semibold text-sm hover:from-orange-600 hover:to-amber-600 transition-colors shadow-md shadow-orange-200 dark:shadow-orange-900/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Go Home
                        </a>
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
