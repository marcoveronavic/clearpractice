<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:255'],
            'last_name'  => ['required','string','max:255'],
            'email'      => ['required','string','email','max:255','unique:users,email'],
        ]);

        $user = new User();
        $user->name  = trim($data['first_name'].' '.$data['last_name']);
        $user->email = $data['email'];

        // Auto-generate a secure password (no typing needed)
        $temp = \Illuminate\Support\Str::random(16);
        $user->password = \Illuminate\Support\Facades\Hash::make($temp);

        $user->save();

        // (Optional) You could email a reset link here if mail is configured.

        return redirect()->route('users.index')->with('status', 'User created.');
    }
}
