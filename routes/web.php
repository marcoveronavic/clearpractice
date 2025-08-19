<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ChController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ClientsQuickController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\DeadlinesController;

require __DIR__.'/leads.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('ch.page'));

/* ------------------- Companies House search ------------------- */
Route::get('/ch',      [ChController::class, 'page'])->name('ch.page');
Route::get('/api/ch',  [ChController::class, 'search'])->name('ch.search');
// (optional) single company details from CH if your controller supports it
Route::get('/api/ch/company/{number}', [ChController::class, 'company'])
    ->where('number', '[A-Za-z0-9]+')
    ->name('ch.company');

/* --------------------------- Companies --------------------------- */
Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/',   [CompaniesController::class, 'index'])->name('index');
    Route::post('/',  [CompaniesController::class, 'store'])->name('store');           // Add / Update from CH
    Route::delete('/{number}', [CompaniesController::class, 'destroy'])->name('destroy');

    // Edit/View modal save for extra meta (auth code, UTR, VAT, GOV ID, etc.)
    Route::post('/{number}/meta', [CompaniesController::class, 'updateMeta'])->name('updateMeta');
});

/* ---------------------------- Clients ---------------------------- */
Route::prefix('clients')->name('clients.')->group(function () {
    Route::get('/',   [ClientsQuickController::class, 'index'])->name('index');
    Route::post('/',  [ClientsQuickController::class, 'store'])->name('store');
    Route::delete('/{id}', [ClientsQuickController::class, 'destroy'])->name('destroy');
});

/* ----------------------------- Users ----------------------------- */
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/',   [UsersController::class, 'index'])->name('index');
    Route::post('/',  [UsersController::class, 'store'])->name('store');
    Route::post('/{id}',    [UsersController::class, 'update'])->name('update');
    Route::delete('/{id}',  [UsersController::class, 'destroy'])->name('destroy');
});

/* ----------------------------- Tasks ----------------------------- */
Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/',   [TasksController::class, 'index'])->name('index');
    Route::post('/',  [TasksController::class, 'store'])->name('store');
    Route::post('/{id}',    [TasksController::class, 'update'])->name('update');
    Route::delete('/{id}',  [TasksController::class, 'destroy'])->name('destroy');
});

/* --------------------------- Deadlines --------------------------- */
Route::prefix('deadlines')->name('deadlines.')->group(function () {
    // Page with auto (CH) + manual deadlines
    Route::get('/', [DeadlinesController::class, 'index'])->name('index');

    // Add a manual deadline row (form at bottom of the page)
    Route::post('/', [DeadlinesController::class, 'store'])->name('store');

    // Delete a manual deadline row
    Route::delete('/{id}', [DeadlinesController::class, 'destroy'])->name('destroy');

    // Refresh CH data for a single company number
    Route::post('/company/{number}', [DeadlinesController::class, 'storeCompany'])
        ->where('number', '[A-Za-z0-9]+')
        ->name('company.store');

    // Refresh CH data for all companies (button: “Aggiorna da CH”)
    Route::post('/refresh-all', [DeadlinesController::class, 'refreshAll'])->name('refreshAll');
});

require __DIR__.'/leads.php';

if (app()->environment('local')) {
    require __DIR__.'/dev.php';
}

