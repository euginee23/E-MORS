<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Management
    Route::view('vendors', 'pages.vendors.index')->name('vendors.index');
    Route::view('stalls', 'pages.stalls.index')->name('stalls.index');

    // Operations
    Route::view('collections', 'pages.collections.index')->name('collections.index');
    Route::view('reports', 'pages.reports.index')->name('reports.index');

    // Administration (admin only)
    Route::view('users', 'pages.users.index')
        ->middleware('role:admin')
        ->name('users.index');
});

require __DIR__.'/settings.php';
