<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| These routes power the Companies House search page (/ch) and the
| JSON API proxy (/api/ch) that the page calls.
|
*/

Route::get('/', fn () => redirect()->route('ch.page'));

Route::get('/ch', [ChController::class, 'page'])->name('ch.page');
Route::get('/api/ch', [ChController::class, 'search'])->name('ch.search');
