<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InviteController extends Controller
{
    /**
     * Show the "Finish setup" form for a valid invitation token.
     */
    public function show(string $token)
    {
        $inv = Invitation::with('practice')->where('token', $token)->firstOrFail();

        if ($inv->accepted_at) {
            return redirect()->route('login')->withErrors(['This invitation has already been accepted.']);
        }

        if ($inv->isExpired()) {
            return redirect()->route('login')->withErrors(['This invitation has expired.']);
        }

        return view('invites.accept', compact('inv'));
    }

    /**
     * Accept the invitation, create/update user, verify email, attach to practice,
     * log them in, and redirect to Users page.
     */
    public function accept(Request $request, string $token)
    {
        $inv = Invitation::with('practice')->where('token', $token)->firstOrFail();

        if ($inv->accepted_at || $inv->isExpired()) {
            return redirect()->route('login')->withErrors(['Invalid or expired invitation token.']);
        }

        $data = $request->validate([
            'first_name'            => ['required', 'string', 'max:255'],
            'surname'               => ['required', 'string', 'max:255'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::firstOrCreate(
            ['email' => $inv->email],
            [
                'name'              => trim($data['first_name'].' '.$data['surname']),
                'password'          => Hash::make($data['password']),
                'email_verified_at' => now(), // invitation proves email ownership
            ],
        );

        if (! $user->wasRecentlyCreated) {
            $user->name              = trim($data['first_name'].' '.$data['surname']);
            $user->password          = Hash::make($data['password']);
            $user->email_verified_at = $user->email_verified_at ?: now();
            $user->save();
        }

        // Attach to the invited practice
        $inv->practice->members()->syncWithoutDetaching([$user->id => ['role' => $inv->role ?? 'member']]);

        // Mark invitation as accepted and clean up
        $inv->accepted_at = now();
        $inv->save();
        $inv->delete();

        // Log in the user and go to Users page
        Auth::login($user, true);

        return redirect()->route('users.index')->with('status', 'Welcome! Please review your details.');
    }
}
