<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health
Route::get('/health', fn () => response()->json(['ok' => true]));

// Authenticated user (if you use Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user())
        ->name('api.user.me');
});

// --- Companies House (API) ---
// Alias the import to avoid "name already in use" collisions.
use App\Http\Controllers\Api\CompaniesHouseController as CHApi;

Route::get('/companies-house/search', [CHApi::class, 'search'])
    ->name('api.companies-house.search');

Route::get('/companies-house/{companyNumber}', [CHApi::class, 'show'])
    ->name('api.companies-house.show');

// (Optional) Backwards-compat alias if your view still calls /api/ch?q=...
Route::get('/ch', [CHApi::class, 'search']);
