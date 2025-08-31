<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class IndividualsController extends Controller
{
    public function index()
    {
        $people = [
            ['id' => 1, 'first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.com'],
            ['id' => 2, 'first_name' => 'Bob',   'last_name' => 'Jones', 'email' => 'bob@example.com'],
        ];

        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Individuals/Index', [
                'individuals' => $people,
            ]);
        }

        // Blade fallback (if you’re not on Inertia)
        return view('individuals.index', [
            'individuals' => collect($people), // make it a Collection so ->isEmpty() works
        ]);
    }
}
