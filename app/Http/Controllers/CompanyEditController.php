<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Practice;
use Illuminate\Http\Request;

class CompanyEditController extends Controller
{
    // GET /p/{practice:slug}/companies/{company}/edit
    public function edit(Practice $practice, Company $company)
    {
        return view('companies.edit', [
            'practice' => $practice,   // pass the model
            'company'  => $company,
        ]);
    }

    // PATCH /p/{practice:slug}/companies/{company}
    public function update(Request $request, Practice $practice, Company $company)
    {
        $data = $request->validate([
            'vat_number'          => ['nullable','string','max:20'],   // VRN
            'utr'                 => ['nullable','string','max:20'],
            'authentication_code' => ['nullable','string','max:50'],
            'vat_period'          => ['nullable','string','max:20'],   // Monthly / Quarterly
            // MATCHES YOUR DB COLUMN NAME:
            'vat_quarter_group'   => ['nullable','string','max:20'],   // e.g. Mar/Jun/Sep/Dec
            'telephone'           => ['nullable','string','max:50'],
            'email'               => ['nullable','email','max:255'],
        ]);

        // Save (use forceFill in case model fillable isn't updated yet)
        $company->forceFill($data)->save();

        return redirect()
            ->route('practice.companies.show', [$practice->slug, $company->id])
            ->with('status', 'Company details updated.');
    }
}
