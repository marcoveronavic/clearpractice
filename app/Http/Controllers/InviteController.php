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
     * Show the accept invite page by token.
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

        // Keep existing view name you already use
        return view('invites.accept', compact('inv'));
    }

    /**
     * Accept the invitation: create/update user, verify immediately, attach to practice,
     * log in, and redirect to that practice's Companies page.
     */
    public function accept(Request $request, string $token)
    {
        $inv = Invitation::with('practice')->where('token', $token)->firstOrFail();

        if ($inv->accepted_at || $inv->isExpired()) {
            return redirect()->route('login')->withErrors(['Invalid or expired invitation token.']);
        }

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'surname'    => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $fullName = trim($data['first_name'].' '.$data['surname']);

        // Create or fetch the user by the invited email (email ownership is proven by the token)
        $user = User::firstOrCreate(
            ['email' => $inv->email],
            ['name' => $fullName, 'password' => Hash::make($data['password'])]
        );

        // Keep profile current
        $user->name = $fullName;
        $user->password = Hash::make($data['password']);

        // ✅ Explicitly mark as verified (don't rely on mass assignment)
        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }
        $user->save();

        // Attach to the practice with the invite's role
        $inv->practice->members()->syncWithoutDetaching([
            $user->id => ['role' => $inv->role ?? 'member']
        ]);

        // Finalize the invitation
        $inv->accepted_at = now();
        $inv->save();
        $inv->delete();

        // Log them in and send to the practice workspace
        Auth::login($user, true);

        // Practice-scoped Companies page (route must exist in your routes/web.php)
        return redirect()
            ->route('practice.companies.index', $inv->practice->slug)
            ->with('status', 'Welcome! Your account is ready.');
    }
}
