<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;

/* ===== Opening page (create a new practice) ===== */
Route::get('/landing/get-started', [LeadController::class, 'showStart'])->name('lead.start.show');
Route::get('/get-started', fn () => redirect()->route('lead.start.show')); // GET -> form
Route::post('/get-started', [LeadController::class, 'start'])->name('lead.start'); // POST -> create lead

/* ===== Minimal onboarding flow so existing links work ===== */
Route::get('/lead/confirm/{token}', [LeadController::class, 'confirm'])->name('lead.confirm');
Route::get('/lead/add-users',  [LeadController::class, 'showAddUsers'])->name('lead.users.show');
Route::post('/lead/add-users', [LeadController::class, 'storeUsers'])->name('lead.users.store');
Route::post('/lead/resend',    [LeadController::class, 'resend'])->name('lead.resend');
Route::get('/invite/{token}',  [LeadController::class, 'acceptInvite'])->name('invite.accept');

/* ===== Practice workspace pages ===== */
Route::get('/practice/{slug}',           [LeadController::class, 'practiceHome'])->name('practice.home');
Route::get('/practice/{slug}/companies', [LeadController::class, 'practiceCompanies'])->name('practice.companies');
Route::get('/practice/{slug}/ch',        [LeadController::class, 'practiceCh'])->name('practice.ch');
