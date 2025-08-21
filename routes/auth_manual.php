<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Setup\PracticeSetupController;

/*
|--------------------------------------------------------------------------
| Auth + Setup Routes (manual)
|--------------------------------------------------------------------------
| - Guests: register + login
| - Authenticated: step-2 practice naming + logout
*/

Route::middleware('guest')->group(function () {
    // Sign up (admin user)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    // Step 2: Name your practice
    Route::get('/setup/practice', [PracticeSetupController::class, 'showPracticeForm'])->name('setup.practice');
    Route::post('/setup/practice', [PracticeSetupController::class, 'savePractice'])->name('setup.practice.save');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
