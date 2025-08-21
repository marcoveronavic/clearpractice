<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PracticeSetupController extends Controller
{
    public function showPracticeForm(Request $request)
    {
        $request->user() ?? abort(403);
        return view('setup.practice');
    }

    public function savePractice(Request $request)
    {
        $request->user() ?? abort(403);

        $data = $request->validate([
            'practice_name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($data['practice_name']);
        $slug = Str::slug($name);

        // For now, store practice context in session (DB comes next step)
        session([
            'practice.current' => [
                'name' => $name,
                'slug' => $slug,
                'is_owner' => true,
            ],
        ]);

        // Redirect to the practice-scoped CH Search page
        return redirect("/{$slug}/ch");
    }
}
