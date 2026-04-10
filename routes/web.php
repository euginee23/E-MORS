<?php

use App\Http\Controllers\Auth\VerifyEmailCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public vendor application route
Route::livewire('register/vendor/{market}', 'pages::vendor-application.register')
    ->middleware('guest')
    ->name('vendor.apply');

Route::post('email/verify/code', VerifyEmailCodeController::class)
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.code');

Route::middleware(['auth', 'verified'])->group(function () {
    // Vendor waiting/pending screen — accessible before approval, no vendor-approved check
    Route::view('vendor/pending', 'vendor-pending')->name('vendor.pending');

    Route::middleware('vendor-approved')->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');

        // Admin & management routes (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::livewire('vendors', 'pages::vendors.index')->name('vendors.index');
            Route::livewire('stalls', 'pages::stalls.index')->name('stalls.index');
            Route::livewire('collections', 'pages::collections.index')->name('collections.index');
            Route::livewire('collectors', 'pages::collectors.index')->name('collectors.index');
            Route::livewire('reports', 'pages::reports.index')->name('reports.index');
            Route::livewire('users', 'pages::users.index')->name('users.index');
            Route::livewire('announcements', 'pages::announcements.index')->name('announcements.index');
            Route::livewire('announcements/create', 'pages::announcements.create')->name('announcements.create');
            Route::livewire('applications', 'pages::applications.index')->name('applications.index');
        });

        // Collector routes
        Route::middleware('role:collector')->prefix('collector')->name('collector.')->group(function () {
            Route::livewire('summary', 'pages::collector.summary')->name('summary');
            Route::livewire('collect', 'pages::collector.collect')->name('collect');
            Route::livewire('collections', 'pages::collector.collections')->name('collections');
            Route::livewire('vendors', 'pages::collector.vendors')->name('vendors');
        });

        // Vendor routes
        Route::middleware('role:vendor')->prefix('vendor')->name('vendor.')->group(function () {
            Route::livewire('stall', 'pages::vendor.stall')->name('stall');
            Route::livewire('payments', 'pages::vendor.payments')->name('payments');
            Route::livewire('profile', 'pages::vendor.profile')->name('profile');
            Route::livewire('announcements', 'pages::vendor.announcements')->name('announcements');
        });
    });
});

require __DIR__.'/settings.php';
