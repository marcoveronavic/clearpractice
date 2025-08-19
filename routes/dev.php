<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

if (! app()->environment('local')) {
    return; // only available in local env
}

Route::get('/dev/mail-test', function () {
    $to = request('to', env('MAIL_FROM_ADDRESS'));
    Mail::raw('ClearCash mail test via Twilio SendGrid SMTP ✅', function ($m) use ($to) {
        $m->to($to)->subject('ClearCash • Mail test');
    });
    return "Sent test email to {$to}. Check your inbox (or SendGrid activity).";
});
