<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OneDriveController;

// Start Microsoft sign-in (Graph OAuth)
Route::get('/msgraph/oauth', [OneDriveController::class, 'connect'])->name('msgraph.oauth');

// OneDrive UI (binds a OneDrive folder to the current user's practice)
Route::middleware(['web','auth'])->group(function () {
    Route::get('/integrations/onedrive', [OneDriveController::class, 'landing'])->name('onedrive.landing');
    Route::post('/integrations/onedrive/create-folder', [OneDriveController::class, 'createFolder'])->name('onedrive.createFolder');
    Route::post('/integrations/onedrive/save', [OneDriveController::class, 'save'])->name('onedrive.save');
    Route::post('/integrations/onedrive/upload-test', [OneDriveController::class, 'uploadTest'])->name('onedrive.uploadTest');
});

