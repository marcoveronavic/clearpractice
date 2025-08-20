<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PracticeController;

// ----- LANDING -----
Route::get('/landing', function () {
    return view('landing');
})->name('landing');

// ----- AUTH -----
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ----- PRACTICES -----
Route::middleware('auth')->group(function () {
    Route::get('/practices/create', [PracticeController::class, 'create'])->name('practices.create');
    Route::post('/practices', [PracticeController::class, 'store'])->name('practices.store');
    Route::get('/practices/{practice:slug}', [PracticeController::class, 'show'])->name('practices.show');
});
