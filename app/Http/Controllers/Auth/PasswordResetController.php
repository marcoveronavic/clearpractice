<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    // GET /password/forgot
    public function showRequestForm(Request $request)
    {
        return view('auth.passwords.email', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    // POST /password/email -> send reset link (generic message either way)
    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => ['required','email','max:255']]);

        // Laravel sends the email if the user exists; we always return generic status
        Password::broker()->sendResetLink(['email' => $data['email']]);

        return back()->with('status', 'If the email exists, a password reset link has been sent.');
    }

    // GET /password/reset/{token}
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    // POST /password/reset
    public function handleReset(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required','string'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset($data, function ($user) use ($data) {
            $user->password = Hash::make($data['password']);
            $user->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password updated. You can sign in now.')
            : back()->withErrors(['email' => 'Password reset failed. Please request a new link.']);
    }
}
