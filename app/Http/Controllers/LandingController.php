<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        // If you prefer logged-in users to skip the landing page, keep this redirect.
        // If you want everyone to see the landing page always, delete these two lines.
        if (Auth::check()) {
            return redirect()->route('dashboard'); // make sure this route exists
        }

        return view('setup.landing');
    }
}
