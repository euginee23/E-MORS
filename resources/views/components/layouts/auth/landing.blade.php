<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'E-MORS - E-Palengke Market Operations and Revenue System' }}</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-orange-50 dark:bg-zinc-950 min-h-screen">
        
        <!-- Decorative Background Pattern -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-64 sm:w-96 h-64 sm:h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-64 sm:w-96 h-64 sm:h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative min-h-screen flex flex-col">
            <!-- Top Bar -->
            <header class="border-b border-orange-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 lg:h-20">
                        <!-- Logo -->
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

                        <!-- Navigation -->
                        <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                            <a href="{{ url('/') }}#about" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">About</a>
                            <a href="{{ url('/') }}#modules" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Modules</a>
                            <a href="{{ url('/') }}#benefits" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Benefits</a>
                        </nav>

                        <!-- Auth -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="px-3 py-2 sm:px-6 sm:py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm sm:text-base font-semibold rounded-lg sm:rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40">
                                        Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2.5 text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">
                                        Sign In
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="hidden sm:inline-flex px-4 lg:px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm lg:text-base font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5">
                                            Register
                                        </a>
                                    @endif

                                    <!-- Mobile Auth Links -->
                                    <a href="{{ route('login') }}" class="sm:hidden px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:text-orange-600 font-medium transition-colors">
                                        Sign In
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 py-8 lg:py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>

            <x-footer />
        </div>
    </body>
</html>
