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
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative min-h-screen flex flex-col">
            <!-- Top Bar -->
            <header class="border-b border-orange-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-20">
                        <!-- Logo -->
                        <a href="{{ url('/') }}" class="flex items-center gap-4 group">
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-2xl rotate-3 shadow-lg group-hover:rotate-6 transition-transform"></div>
                                <div class="absolute inset-0 w-12 h-12 bg-gradient-to-br from-orange-600 via-amber-600 to-yellow-600 rounded-2xl -rotate-3 flex items-center justify-center group-hover:-rotate-6 transition-transform">
                                    <span class="text-white font-black text-lg">EP</span>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tight">E-MORS</h1>
                                <p class="text-xs text-orange-600 dark:text-orange-400 font-medium tracking-widest uppercase">E-Palengke System</p>
                            </div>
                        </a>

                        <!-- Navigation -->
                        <nav class="hidden md:flex items-center gap-8">
                            <a href="{{ url('/') }}#about" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">About</a>
                            <a href="{{ url('/') }}#modules" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Modules</a>
                            <a href="{{ url('/') }}#benefits" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Benefits</a>
                        </nav>

                        <!-- Auth -->
                        <div class="flex items-center gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40">
                                        Go to Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">
                                        Sign In
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5">
                                            Register
                                        </a>
                                    @endif
                                @endauth
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 py-12 lg:py-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-zinc-950 text-white py-12 px-4 sm:px-6 lg:px-8 mt-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="grid md:grid-cols-4 gap-8 mb-8">
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="relative">
                                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-xl rotate-3"></div>
                                    <div class="absolute inset-0 w-10 h-10 bg-gradient-to-br from-orange-600 to-amber-600 rounded-xl -rotate-3 flex items-center justify-center">
                                        <span class="text-white font-black text-sm">EP</span>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-black text-xl">E-MORS</h3>
                                    <p class="text-xs text-orange-400 font-medium">E-Palengke System</p>
                                </div>
                            </div>
                            <p class="text-zinc-400 max-w-md leading-relaxed text-sm">
                                E-Palengke Market Operations and Revenue System — A comprehensive Operations Management System (OMS) empowering public markets with modern technology.
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="font-bold mb-3 text-sm">Quick Links</h4>
                            <ul class="space-y-2 text-zinc-400 text-sm">
                                <li><a href="{{ url('/') }}#about" class="hover:text-orange-400 transition-colors">About</a></li>
                                <li><a href="{{ url('/') }}#modules" class="hover:text-orange-400 transition-colors">Modules</a></li>
                                <li><a href="{{ url('/') }}#benefits" class="hover:text-orange-400 transition-colors">Benefits</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-bold mb-3 text-sm">Account</h4>
                            <ul class="space-y-2 text-zinc-400 text-sm">
                                <li><a href="{{ route('login') }}" class="hover:text-orange-400 transition-colors">Sign In</a></li>
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" class="hover:text-orange-400 transition-colors">Register</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    
                    <div class="border-t border-zinc-800 pt-6 flex flex-col md:flex-row justify-between items-center gap-4">
                        <p class="text-zinc-500 text-sm">
                            &copy; {{ date('Y') }} E-MORS. All rights reserved.
                        </p>
                        <p class="text-zinc-600 text-sm">
                            Made with 🧡 for Public Markets
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
