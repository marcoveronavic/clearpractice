<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DeadlinesController;

// -------- Home (optional) --------
Route::get('/', function () {
    return view('welcome');
});

// -------- Companies House --------
Route::view('/ch', 'ch')->name('ch.page');
Route::get('/api/ch', [ChController::class, 'search'])->name('ch.search');
Route::get('/ch/company/{number}', [ChController::class, 'company'])
    ->where('number', '[A-Za-z0-9]+')
    ->name('ch.company');

// -------- Clients --------
Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('clients.destroy');

// -------- Users --------
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::post('/users', [UsersController::class, 'store'])->name('users.store');
Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');

// -------- Tasks --------
Route::get('/tasks', [TasksController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TasksController::class, 'store'])->name('tasks.store');
Route::delete('/tasks/{id}', [TasksController::class, 'destroy'])->name('tasks.destroy');

// -------- Deadlines --------
Route::get('/deadlines', [DeadlinesController::class, 'index'])->name('deadlines.index');
Route::post('/deadlines', [DeadlinesController::class, 'store'])->name('deadlines.store');
Route::delete('/deadlines/{id}', [DeadlinesController::class, 'destroy'])->name('deadlines.destroy');
