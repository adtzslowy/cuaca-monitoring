<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Dashboard;
use App\Livewire\Monitoring;
use App\Livewire\UserManajemen;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth/sign-in', [AuthController::class, 'showForm'])->name('login');
    Route::post('/auth/sign-in', [AuthController::class, 'process'])->name('login.post');
});

Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('dashboard')->middleware('auth')->group(function() {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/monitoring', Monitoring::class)->name('monitoring');

    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/users', UserManajemen::class)->name('users.index');
    });
});
