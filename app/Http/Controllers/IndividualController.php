<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use Illuminate\Http\Request;

class IndividualController extends Controller
{
    public function index()
    {
        $individuals = Individual::orderBy('first_name')->orderBy('last_name')->get();

        return view('individuals.index', compact('individuals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required','string','max:100'],
            'last_name'  => ['nullable','string','max:100'],
            'email'      => ['required','email','max:255','unique:individuals,email'],
            'phone'      => ['nullable','string','max:50'],
        ]);

        Individual::create($data);

        return redirect()->route('individuals.index')->with('ok', 'Individual saved.');
    }
}

