<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

/*
| Lead & onboarding routes
*/

# Show the form (GET)
Route::get('/landing/get-started', [LeadController::class, 'showStart'])
    ->name('lead.start.show');

# If anyone visits /get-started via GET, redirect to the form above
Route::get('/get-started', fn () => redirect()->route('lead.start.show'));

# Handle form submit (POST)
Route::post('/get-started', [LeadController::class, 'start'])
    ->name('lead.start');

# Email confirmation link
Route::get('/lead/confirm/{token}', [LeadController::class, 'confirm'])
    ->name('lead.confirm');

# Add users page + submit
Route::get('/lead/add-users', [LeadController::class, 'showAddUsers'])
    ->name('lead.users.show');

Route::post('/lead/add-users', [LeadController::class, 'storeUsers'])
    ->name('lead.users.store');
