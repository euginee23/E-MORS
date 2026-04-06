<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased bg-orange-50 dark:bg-zinc-950 min-h-screen">

        <!-- Decorative Background Pattern -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 right-0 w-64 sm:w-96 h-64 sm:h-96 bg-orange-200 dark:bg-orange-900/20 rounded-full blur-3xl opacity-30 -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-64 sm:w-96 h-64 sm:h-96 bg-amber-200 dark:bg-amber-900/20 rounded-full blur-3xl opacity-30 translate-y-1/2 -translate-x-1/2"></div>
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
                        <span class="text-[8rem] sm:text-[10rem] font-black leading-none text-orange-100 dark:text-zinc-800 select-none">404</span>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl sm:rounded-3xl rotate-6 shadow-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mb-3">Page Not Found</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm sm:text-base leading-relaxed mb-8">
                        The page you're looking for doesn't exist or has been moved. Please check the URL or navigate back to the dashboard.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 rounded-xl font-semibold text-sm hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Go Back
                        </a>
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

        @fluxScripts
    </body>
</html>
