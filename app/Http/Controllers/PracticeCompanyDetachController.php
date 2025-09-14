<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeCompanyDetachController extends Controller
{
    /**
     * DELETE /p/{practice:slug}/companies/{company}/detach
     * Name: practice.companies.detach
     */
    public function __invoke(Request $request, Practice $practice, Company $company)
    {
        $user = Auth::user();

        if (! $user || ! $user->companies()->where('companies.id', $company->id)->exists()) {
            return back()->withErrors(['You do not have this company in your practice.']);
        }

        $user->companies()->detach($company->id);

        return back()->with('status', 'Company removed from your practice.');
    }
}
