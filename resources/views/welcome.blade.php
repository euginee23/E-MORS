<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E-MORS - E-Palengke Market Operations and Revenue System</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-orange-50 dark:bg-zinc-950 min-h-screen">
        
        <!-- Decorative Background Pattern -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-96 h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-50 translate-y-1/2 -translate-x-1/2"></div>
        </div>

        <div class="relative">
            <!-- Top Bar -->
            <header class="border-b border-orange-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 lg:h-20">
                        <!-- Logo -->
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

                        <!-- Navigation -->
                        <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                            <a href="#about" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">About</a>
                            <a href="#modules" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Modules</a>
                            <a href="#benefits" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Benefits</a>
                        </nav>

                        <!-- Auth + Mobile Menu -->
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
                                @endauth
                            @endif

                            <!-- Mobile Menu Button -->
                            <button 
                                id="mobileMenuBtn"
                                class="md:hidden p-2 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors"
                                aria-label="Toggle menu"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile Menu -->
                    <div id="mobileMenu" class="hidden md:hidden pb-4 border-t border-orange-100 dark:border-zinc-800 mt-2">
                        <nav class="flex flex-col gap-1 pt-4">
                            <a href="#about" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">About</a>
                            <a href="#modules" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">Modules</a>
                            <a href="#benefits" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">Benefits</a>
                        </nav>
                        @guest
                            <div class="flex flex-col gap-2 pt-4 mt-4 border-t border-orange-100 dark:border-zinc-800">
                                <a href="{{ route('login') }}" class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 font-medium border border-zinc-200 dark:border-zinc-700 rounded-xl transition-colors">
                                    Sign In
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-3 text-center bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25">
                                        Register Your Market
                                    </a>
                                @endif
                            </div>
                        @endguest
                    </div>
                </div>
            </header>

            <!-- Hero Section - Split Design -->
            <section class="py-10 sm:py-16 lg:py-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 items-center">
                        <!-- Left Content - Takes 3 columns -->
                        <div class="lg:col-span-3 space-y-5 sm:space-y-8">
                            <div class="inline-block">
                                <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-xs sm:text-sm font-bold rounded-full border border-orange-200 dark:border-orange-800">
                                    🏪 Operations Management System (OMS)
                                </span>
                            </div>

                            <h1 class="text-3xl sm:text-5xl lg:text-7xl font-black text-zinc-900 dark:text-white leading-[1.1]">
                                Modern
                                <span class="relative inline-block">
                                    <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-500 to-yellow-500">Palengke</span>
                                    <span class="absolute bottom-1 sm:bottom-2 left-0 right-0 h-2 sm:h-4 bg-orange-200 dark:bg-orange-900/50 -z-0 -rotate-1"></span>
                                </span>
                                <br>Management
                            </h1>

                            <p class="text-base sm:text-xl text-zinc-600 dark:text-zinc-400 max-w-xl leading-relaxed">
                                Transform your public market with our comprehensive Operations Management System. 
                                Streamline vendor management, stall allocation, and real-time fee collection — all in one powerful platform.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-2 sm:pt-4">
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 sm:gap-3 px-6 py-3 sm:px-8 sm:py-4 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 font-bold text-base sm:text-lg rounded-xl sm:rounded-2xl transition-all hover:scale-105 shadow-xl">
                                        Start Free Trial
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                        </svg>
                                    </a>
                                @endif
                                <a href="#modules" class="inline-flex items-center justify-center gap-2 sm:gap-3 px-6 py-3 sm:px-8 sm:py-4 border-2 border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-bold text-base sm:text-lg rounded-xl sm:rounded-2xl transition-all hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    See How It Works
                                </a>
                            </div>
                        </div>

                        <!-- Right Content - Takes 2 columns - Market Illustration -->
                        <div class="lg:col-span-2 relative mt-4 lg:mt-0">
                            <div class="relative">
                                <!-- Main Card -->
                                <div class="bg-white dark:bg-zinc-900 rounded-2xl sm:rounded-3xl shadow-2xl p-5 sm:p-8 border border-orange-100 dark:border-zinc-800">
                                    <!-- Market Map Preview -->
                                    <div class="mb-4 sm:mb-6">
                                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                                            <h3 class="font-bold text-zinc-900 dark:text-white text-sm sm:text-base">Market Layout</h3>
                                            <span class="text-[10px] sm:text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-1 rounded-full font-medium">Live</span>
                                        </div>
                                        <!-- Stall Grid -->
                                        <div class="grid grid-cols-6 gap-1 sm:gap-1.5">
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-orange-400 dark:bg-orange-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-zinc-200 dark:bg-zinc-700 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-orange-400 dark:bg-orange-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-zinc-200 dark:bg-zinc-700 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-orange-400 dark:bg-orange-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-zinc-200 dark:bg-zinc-700 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-zinc-200 dark:bg-zinc-700 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-orange-400 dark:bg-orange-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-green-400 dark:bg-green-500 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                            <div class="aspect-square bg-zinc-200 dark:bg-zinc-700 rounded-md transition-transform hover:scale-110 cursor-pointer"></div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 sm:gap-4 mt-2 sm:mt-3 text-[10px] sm:text-xs text-zinc-600 dark:text-zinc-400">
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 bg-green-400 rounded"></span> Occupied</span>
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 bg-orange-400 rounded"></span> Reserved</span>
                                            <span class="flex items-center gap-1"><span class="w-2 h-2 bg-zinc-200 dark:bg-zinc-700 rounded"></span> Available</span>
                                        </div>
                                    </div>

                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg sm:rounded-xl p-2 sm:p-3 text-center">
                                            <div class="text-lg sm:text-2xl font-black text-orange-600 dark:text-orange-400">248</div>
                                            <div class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400">Vendors</div>
                                        </div>
                                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg sm:rounded-xl p-2 sm:p-3 text-center">
                                            <div class="text-lg sm:text-2xl font-black text-green-600 dark:text-green-400">92%</div>
                                            <div class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400">Occupancy</div>
                                        </div>
                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg sm:rounded-xl p-2 sm:p-3 text-center">
                                            <div class="text-lg sm:text-2xl font-black text-blue-600 dark:text-blue-400">₱1.2M</div>
                                            <div class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400">Revenue</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Floating Cards -->
                                <div class="hidden sm:block absolute -top-4 -right-4 bg-white dark:bg-zinc-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-4 border border-orange-100 dark:border-zinc-700 animate-bounce" style="animation-duration: 3s;">
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-white">Payment Received</div>
                                            <div class="text-[10px] sm:text-xs text-zinc-500">Stall A-15 • ₱500</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hidden sm:block absolute -bottom-4 -left-4 bg-white dark:bg-zinc-800 rounded-xl sm:rounded-2xl shadow-lg p-3 sm:p-4 border border-orange-100 dark:border-zinc-700">
                                    <div class="flex items-center gap-2 sm:gap-3">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-white">New Vendor</div>
                                            <div class="text-[10px] sm:text-xs text-zinc-500">Maria Santos registered</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- About Section -->
            <section id="about" class="py-12 sm:py-20 bg-white dark:bg-zinc-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl mx-auto text-center mb-10 sm:mb-16">
                        <h2 class="text-2xl sm:text-4xl font-black text-zinc-900 dark:text-white mb-4 sm:mb-6">
                            What is <span class="text-orange-600">E-MORS</span>?
                        </h2>
                        <p class="text-base sm:text-lg text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            The <strong>E-Palengke Market Operations and Revenue System</strong> functions as an <strong>Operations Management System (OMS)</strong> for public markets — 
                            a comprehensive web-based platform designed to streamline market operations and enhance revenue management. 
                            It addresses the challenges of traditional paper-based methods by providing digital solutions for vendor management, stall allocation, and real-time fee collection tracking.
                        </p>
                    </div>

                    <!-- Problem / Solution Cards -->
                    <div class="grid md:grid-cols-2 gap-4 sm:gap-8">
                        <!-- Problem Card -->
                        <div class="bg-red-50 dark:bg-red-900/10 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-red-100 dark:border-red-900/30">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-red-100 dark:bg-red-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-red-800 dark:text-red-300 mb-3 sm:mb-4">The Problem</h3>
                            <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base text-red-700 dark:text-red-300/80">
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                    <span>Sluggish manual vendor registration processes</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                    <span>Ambiguous stall assignments causing disputes</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                    <span>Imprecise fee and revenue tracking</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                    <span>Revenue leakage and potential losses</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Solution Card -->
                        <div class="bg-green-50 dark:bg-green-900/10 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-green-100 dark:border-green-900/30">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-green-100 dark:bg-green-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-bold text-green-800 dark:text-green-300 mb-3 sm:mb-4">The Solution</h3>
                            <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base text-green-700 dark:text-green-300/80">
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Fast digital vendor registration & management</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Clear visual stall mapping & allocation</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Automated fee collection with receipts</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    <span>Real-time reports & decision-making tools</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Modules Section - Bento Grid Style -->
            <section id="modules" class="py-12 sm:py-20 bg-gradient-to-b from-orange-50 to-amber-50 dark:from-zinc-950 dark:to-zinc-900 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-8 sm:mb-12">
                        <span class="inline-block px-3 py-1.5 sm:px-4 sm:py-1.5 bg-orange-200 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300 text-xs sm:text-sm font-bold rounded-full mb-3 sm:mb-4">
                            SYSTEM MODULES
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-zinc-900 dark:text-white">
                            Everything You Need
                        </h2>
                    </div>

                    <!-- Module Cards - Bento Grid Style -->
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Module 1 - Vendor Management (Large) -->
                        <div class="lg:col-span-2 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl sm:rounded-3xl p-5 sm:p-8 text-white relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-48 sm:w-64 h-48 sm:h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="relative">
                                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 backdrop-blur rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-2xl sm:text-3xl font-bold mb-2 sm:mb-3">Vendor Management</h3>
                                <p class="text-white/80 text-base sm:text-lg mb-4 sm:mb-6 max-w-md">
                                    Complete digital records of all vendors including permits, renewals, compliance status, and vendor history.
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2.5 py-1 sm:px-3 sm:py-1 bg-white/20 rounded-full text-xs sm:text-sm">Registration</span>
                                    <span class="px-2.5 py-1 sm:px-3 sm:py-1 bg-white/20 rounded-full text-xs sm:text-sm">Permits</span>
                                    <span class="px-2.5 py-1 sm:px-3 sm:py-1 bg-white/20 rounded-full text-xs sm:text-sm">Renewals</span>
                                    <span class="px-2.5 py-1 sm:px-3 sm:py-1 bg-white/20 rounded-full text-xs sm:text-sm">History</span>
                                </div>
                            </div>
                        </div>

                        <!-- Module 2 - Stall Allocation -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-zinc-200 dark:border-zinc-700 group hover:border-orange-300 dark:hover:border-orange-700 transition-colors">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-blue-100 dark:bg-blue-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-5 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-zinc-900 dark:text-white mb-1 sm:mb-2">Stall Allocation</h3>
                            <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                                Dynamic visual mapping with real-time availability tracking and automated assignment.
                            </p>
                        </div>

                        <!-- Module 3 - Fee Collection -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-zinc-200 dark:border-zinc-700 group hover:border-orange-300 dark:hover:border-orange-700 transition-colors">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-green-100 dark:bg-green-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-5 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-zinc-900 dark:text-white mb-1 sm:mb-2">Fee Collection</h3>
                            <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                                Automated payment tracking with digital receipts and collection monitoring.
                            </p>
                        </div>

                        <!-- Module 4 - Reports & Analytics -->
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 border border-zinc-200 dark:border-zinc-700 group hover:border-orange-300 dark:hover:border-orange-700 transition-colors">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-purple-100 dark:bg-purple-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-5 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-zinc-900 dark:text-white mb-1 sm:mb-2">Reports & Analytics</h3>
                            <p class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                                Real-time dashboards and comprehensive reports for informed decisions.
                            </p>
                        </div>

                        <!-- Module 5 - Wide Card -->
                        <div class="lg:col-span-2 bg-zinc-900 dark:bg-zinc-800 rounded-2xl sm:rounded-3xl p-5 sm:p-8 text-white relative overflow-hidden">
                            <div class="grid md:grid-cols-2 gap-5 sm:gap-8 items-center">
                                <div>
                                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-amber-500/20 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-5">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl sm:text-2xl font-bold mb-2 sm:mb-3">Security & Access Control</h3>
                                    <p class="text-sm sm:text-base text-zinc-400">
                                        Role-based permissions ensure data integrity while maintaining transparency across all operations.
                                    </p>
                                </div>
                                <div class="space-y-2 sm:space-y-3">
                                    <div class="flex items-center gap-2 sm:gap-3 bg-white/5 rounded-lg sm:rounded-xl p-2.5 sm:p-3">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-500/20 rounded-md sm:rounded-lg flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-xs sm:text-sm">Admin Dashboard</span>
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 bg-white/5 rounded-lg sm:rounded-xl p-2.5 sm:p-3">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-500/20 rounded-md sm:rounded-lg flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-xs sm:text-sm">Collector Portal</span>
                                    </div>
                                    <div class="flex items-center gap-2 sm:gap-3 bg-white/5 rounded-lg sm:rounded-xl p-2.5 sm:p-3">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-green-500/20 rounded-md sm:rounded-lg flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-xs sm:text-sm">Vendor Self-Service</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Benefits Section - Numbers -->
            <section id="benefits" class="py-12 sm:py-20 bg-white dark:bg-zinc-900">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-10 sm:mb-16">
                        <h2 class="text-2xl sm:text-4xl font-black text-zinc-900 dark:text-white mb-3 sm:mb-4">
                            Why Market Administrators Love Us
                        </h2>
                        <p class="text-base sm:text-lg text-zinc-600 dark:text-zinc-400">
                            Real results from real markets using E-MORS
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-8">
                        <div class="text-center group">
                            <div class="text-4xl sm:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 mb-2 sm:mb-3 group-hover:scale-110 transition-transform">
                                95%
                            </div>
                            <div class="text-xs sm:text-base text-zinc-600 dark:text-zinc-400 font-medium">Faster Registration</div>
                        </div>
                        <div class="text-center group">
                            <div class="text-4xl sm:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-emerald-500 mb-2 sm:mb-3 group-hover:scale-110 transition-transform">
                                0%
                            </div>
                            <div class="text-xs sm:text-base text-zinc-600 dark:text-zinc-400 font-medium">Revenue Leakage</div>
                        </div>
                        <div class="text-center group">
                            <div class="text-4xl sm:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 mb-2 sm:mb-3 group-hover:scale-110 transition-transform">
                                100%
                            </div>
                            <div class="text-xs sm:text-base text-zinc-600 dark:text-zinc-400 font-medium">Digital Records</div>
                        </div>
                        <div class="text-center group">
                            <div class="text-4xl sm:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-500 mb-2 sm:mb-3 group-hover:scale-110 transition-transform">
                                24/7
                            </div>
                            <div class="text-xs sm:text-base text-zinc-600 dark:text-zinc-400 font-medium">System Access</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="py-12 sm:py-20 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 dark:from-black dark:via-zinc-900 dark:to-black relative overflow-hidden">
                <div class="absolute inset-0">
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 sm:w-[600px] h-72 sm:h-[600px] bg-orange-500/20 rounded-full blur-3xl"></div>
                </div>
                <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 class="text-2xl sm:text-4xl md:text-5xl font-black text-white mb-4 sm:mb-6">
                        Ready to Transform Your
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-400">Public Market</span>?
                    </h2>
                    <p class="text-base sm:text-xl text-zinc-400 mb-6 sm:mb-10 max-w-2xl mx-auto">
                        Join the growing number of public markets modernizing their operations with E-MORS
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 sm:gap-3 px-6 py-4 sm:px-10 sm:py-5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-lg sm:text-xl rounded-xl sm:rounded-2xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:scale-105">
                                Get Started Free
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 px-6 py-4 sm:px-10 sm:py-5 border-2 border-zinc-700 text-white font-bold text-lg sm:text-xl rounded-xl sm:rounded-2xl transition-all hover:border-orange-500 hover:bg-orange-500/10">
                            Sign In
                        </a>
                    </div>
                </div>
            </section>

            <x-footer />
        </div>

        <!-- Scroll to Top Button -->
        <button 
            id="scrollToTop"
            class="fixed bottom-6 right-6 sm:bottom-8 sm:right-8 w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white rounded-xl sm:rounded-2xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 transition-all duration-300 flex items-center justify-center z-50 opacity-0 invisible translate-y-4 hover:-translate-y-0 hover:scale-105"
            aria-label="Scroll to top"
        >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>

        <script>
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                    // Toggle icon between hamburger and X
                    const svg = mobileMenuBtn.querySelector('svg');
                    if (mobileMenu.classList.contains('hidden')) {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    } else {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                    }
                });

                // Close mobile menu when clicking on a link
                mobileMenu.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        const svg = mobileMenuBtn.querySelector('svg');
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    });
                });
            }

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const navHeight = document.querySelector('header').offsetHeight;
                        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;
                        window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                    }
                });
            });

            // Scroll to top button
            const scrollToTopBtn = document.getElementById('scrollToTop');
            
            window.addEventListener('scroll', () => {
                if (window.scrollY > 400) {
                    scrollToTopBtn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                    scrollToTopBtn.classList.add('opacity-100', 'visible', 'translate-y-0');
                } else {
                    scrollToTopBtn.classList.add('opacity-0', 'invisible', 'translate-y-4');
                    scrollToTopBtn.classList.remove('opacity-100', 'visible', 'translate-y-0');
                }
            });

            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    </body>
</html>
