<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

/* Lead onboarding (minimal, keeps your old left menu as-is) */
Route::get('/lead/confirm/{token}', [LeadController::class, 'confirm'])->name('lead.confirm');
Route::get('/lead/add-users',      [LeadController::class, 'showAddUsers'])->name('lead.users.show');
Route::post('/lead/add-users',     [LeadController::class, 'storeUsers'])->name('lead.users.store');
Route::post('/lead/resend',        [LeadController::class, 'resend'])->name('lead.resend');
Route::get('/invite/{token}',      [LeadController::class, 'acceptInvite'])->name('invite.accept');

/* Practice workspace (new empty pages) */
Route::get('/practice/{slug}',            [LeadController::class, 'practiceHome'])->name('practice.home');
Route::get('/practice/{slug}/companies',  [LeadController::class, 'practiceCompanies'])->name('practice.companies');
