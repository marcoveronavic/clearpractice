<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Http\Controllers\CompanyEditController;

/*
|----------------------------------------------------------------------
| Company edit routes (mounted outside web.php so we don't touch it)
|----------------------------------------------------------------------
| Matches your existing practice group shape:
|   /p/{practice:slug}/companies/{company}/edit   (GET)
|   /p/{practice:slug}/companies/{company}        (PATCH)
*/
Route::prefix('/p/{practice:slug}')
    ->middleware([
        SubstituteBindings::class,
        'auth',
        'verified',
        \App\Http\Middleware\EnsurePracticeAccess::class,
    ])
    ->group(function () {
        Route::get('/companies/{company}/edit', [CompanyEditController::class, 'edit'])
            ->name('practice.companies.edit');

        Route::patch('/companies/{company}', [CompanyEditController::class, 'update'])
            ->name('practice.companies.update');
    });
