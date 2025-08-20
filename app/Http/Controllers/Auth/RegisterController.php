<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration attempt for the Admin user.
     * After creating the user, redirect to Step 2 (/setup/practice).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],      // first name
            'surname'               => ['required', 'string', 'max:255'],      // surname
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Combine first name + surname into the single 'name' column
        $fullName = trim($data['name'] . ' ' . $data['surname']);

        $user = User::create([
            'name'     => $fullName,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        // STEP 2: choose the practice name
        return redirect()->route('setup.practice');
    }
}
