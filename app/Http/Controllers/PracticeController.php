<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PracticeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function create()
    {
        // If the user already owns a practice, go to Users
        $existing = Practice::where('owner_id', Auth::id())->first();
        if ($existing) {
            return redirect()->route('users.index')
                ->with('status', 'You already have a practice.');
        }
        return view('practices.create');
    }

    public function store(Request $request)
    {
        $existing = Practice::where('owner_id', Auth::id())->first();
        if ($existing) {
            return redirect()->route('users.index')->with('status','You already have a practice.');
        }

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','alpha_dash','unique:practices,slug'],
        ]);

        $slug = $data['slug'] ?? Str::slug($data['name']);
        $base = $slug; $i = 1;
        while (Practice::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $practice = Practice::create([
            'name'     => $data['name'],
            'slug'     => $slug,
            'owner_id' => Auth::id(),
        ]);

        // Owner is a member with admin role
        $practice->members()->syncWithoutDetaching([Auth::id() => ['role' => 'admin']]);

        return redirect()->route('users.index')->with('status', 'Practice created. You are the admin.');
    }
}
