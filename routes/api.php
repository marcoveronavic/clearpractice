<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompaniesHouseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and assigned the "api"
| middleware group. They are automatically prefixed with /api.
*/

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'time'   => now()->toIso8601String(),
]));

/*
| Companies House live lookup
| GET /api/companies-house/{companyNumber}
*/
Route::get('/companies-house/{companyNumber}', [CompaniesHouseController::class, 'show'])
    ->whereNumber('companyNumber')
    ->name('api.companies-house.show');

/*
| Authenticated routes (Sanctum)
| GET /api/user
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user())
        ->name('api.user.me');
});
