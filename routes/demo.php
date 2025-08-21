<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
| Demo aliases: serve existing pages under /demo/* WITHOUT changing old routes.
| We internally forward the request so the browser URL stays /demo/*.
*/

Route::prefix('demo')->group(function () {
    Route::get('/ch', function (Request $req) {
        return app()->handle(Request::create('/ch', 'GET', $req->query()));
    })->name('demo.ch');

    Route::get('/companies', function (Request $req) {
        return app()->handle(Request::create('/companies', 'GET', $req->query()));
    })->name('demo.companies');

    Route::get('/clients', function (Request $req) {
        return app()->handle(Request::create('/clients', 'GET', $req->query()));
    })->name('demo.clients');

    Route::get('/tasks', function (Request $req) {
        return app()->handle(Request::create('/tasks', 'GET', $req->query()));
    })->name('demo.tasks');

    Route::get('/users', function (Request $req) {
        return app()->handle(Request::create('/users', 'GET', $req->query()));
    })->name('demo.users');

    Route::get('/deadlines', function (Request $req) {
        return app()->handle(Request::create('/deadlines', 'GET', $req->query()));
    })->name('demo.deadlines');
});
