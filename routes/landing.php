<?php

use Illuminate\Support\Facades\Route;

Route::view('/landing', 'landing')->name('landing');
Route::redirect('/', '/landing'); // make / the landing page
