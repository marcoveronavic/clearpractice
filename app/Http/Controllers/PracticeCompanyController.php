<?php

namespace App\Http\Controllers;

use App\Models\Practice;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeCompanyController extends Controller
{
    /**
     * Detach the authenticated user from a company within a practice context.
     * Route model binding: {practice:slug}, {companyParam} can be id, company_number, or name.
     */
    public function detach(Request $request, Practice $practice, $companyParam)
    {
        // Make sure the user is logged in
        $user = Auth::user();

        // Resolve the company by id OR company_number OR (lower) name — same logic your app already uses
        $company = Company::where('id', $companyParam)
            ->orWhere('company_number', $companyParam)
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($companyParam)])
            ->firstOrFail();

        // Detach the pivot between the current user and this company if present
        // Pivot is "company_user" as per your queries
        if (method_exists($user, 'companies')) {
            $user->companies()->detach($company->id);
        } else {
            // Fallback in case the relation is not defined for some reason
            \DB::table('company_user')
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->delete();
        }

        // Redirect back to the practice companies list if it exists, otherwise back() with a flash message
        try {
            return redirect()
                ->route('practice.companies.index', $practice->slug)
                ->with('status', 'Company detached.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('status', 'Company detached.');
        }
    }
}
