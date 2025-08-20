<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PracticeController extends Controller
{
    public function create()
    {
        return view('practices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'address_line1'  => ['nullable', 'string', 'max:255'],
            'address_line2'  => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:120'],
            'postcode'       => ['nullable', 'string', 'max:40'],
            'country'        => ['nullable', 'string', 'max:120'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:60'],
        ]);

        // Make a unique slug from the name
        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Practice::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $practice = Practice::create([
            'name'          => $data['name'],
            'slug'          => $slug,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city'          => $data['city'] ?? null,
            'postcode'      => $data['postcode'] ?? null,
            'country'       => $data['country'] ?? null,
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        return redirect()->route('practices.show', $practice);
    }

    public function show(Practice $practice)
    {
        return view('practices.show', compact('practice'));
    }
}
