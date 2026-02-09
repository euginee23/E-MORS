<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Admin & management routes (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::view('vendors', 'pages.vendors.index')->name('vendors.index');
        Route::view('stalls', 'pages.stalls.index')->name('stalls.index');
        Route::view('collections', 'pages.collections.index')->name('collections.index');
        Route::view('reports', 'pages.reports.index')->name('reports.index');
        Route::view('users', 'pages.users.index')->name('users.index');
    });

    // Collector routes
    Route::middleware('role:collector')->prefix('collector')->name('collector.')->group(function () {
        Route::view('summary', 'pages.collector.summary')->name('summary');
        Route::view('collect', 'pages.collector.collect')->name('collect');
        Route::view('collections', 'pages.collector.collections')->name('collections');
        Route::view('vendors', 'pages.collector.vendors')->name('vendors');
    });

    // Vendor routes
    Route::middleware('role:vendor')->prefix('vendor')->name('vendor.')->group(function () {
        Route::view('stall', 'pages.vendor.stall')->name('stall');
        Route::view('payments', 'pages.vendor.payments')->name('payments');
        Route::view('profile', 'pages.vendor.profile')->name('profile');
        Route::view('announcements', 'pages.vendor.announcements')->name('announcements');
    });
});

require __DIR__.'/settings.php';
