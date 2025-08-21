<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CompanyController extends Controller
{
    private function callApi(string $path, array $query = []): array
    {
        $base    = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $timeout = (int) env('CH_TIMEOUT', 20);
        $key     = env('CH_API_KEY');

        if (!$key) throw new \RuntimeException('Missing CH_API_KEY in .env');

        $resp = Http::withBasicAuth($key, '')->timeout($timeout)->acceptJson()->get($base . $path, $query);
        if (!$resp->successful()) {
            $body = $resp->json() ?? $resp->body();
            throw new \RuntimeException("Companies House API error (HTTP {$resp->status()}): " . (is_string($body) ? substr($body, 0, 200) : json_encode($body)));
        }
        return (array) $resp->json();
    }

    public function index()
    {
        $companies = Company::orderByDesc('created_at')->get();
        return view('companies.index', ['companies' => $companies]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['number' => 'required|string']);
        $number = strtoupper(trim($validated['number']));

        try {
            $profile = $this->callApi('/company/' . urlencode($number));
            $addr = $profile['registered_office_address'] ?? [];
            $address = implode(', ', array_filter([
                $addr['premises'] ?? null, $addr['address_line_1'] ?? null, $addr['address_line_2'] ?? null,
                $addr['locality'] ?? null, $addr['region'] ?? null, $addr['postal_code'] ?? null, $addr['country'] ?? null,
            ]));

            // Only set columns that exist
            $cols = Schema::getColumnListing('companies');
            $values = [];
            $maybe = function (string $col, $val) use (&$values, $cols) { if (in_array($col, $cols, true)) $values[$col] = $val; };

            $maybe('name',    $profile['company_name'] ?? $number);
            $maybe('status',  $profile['company_status'] ?? null);
            $maybe('address', $address ?: null);
            $maybe('data',    $profile);
            $maybe('date_of_creation', $profile['date_of_creation'] ?? null);

            $company = Company::updateOrCreate(
                ['number' => $profile['company_number'] ?? $number],
                $values
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'action' => $company->wasRecentlyCreated ? 'created' : 'updated',
                    'company' => [
                        'id' => $company->id,
                        'number' => $company->number,
                        'name' => $company->name,
                        'status' => $company->status,
                    ],
                ]);
            }
            return redirect()->route('companies.index')->with('status', "Saved company {$company->number}");
        } catch (\Throwable $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
            }
            return redirect()->route('companies.index')->with('error', "Could not fetch company {$number}: " . $e->getMessage());
        }
    }

    // NEW: Update editable fields via AJAX
    public function update(Request $request, Company $company)
    {
        // Accept JSON or form
        $data = $request->validate([
            'vat_number'  => 'nullable|string|max:64',
            'utr'         => 'nullable|string|max:64',
            'auth_code'   => 'nullable|string|max:64',
            'vat_period'  => 'nullable|in:monthly,quarterly',
            'vat_quarter' => 'nullable|in:jan_apr_jul_oct,feb_may_nov,mar_jun_sep_dec',
        ]);

        // If monthly, quarter must be null
        if (($data['vat_period'] ?? null) === 'monthly') {
            $data['vat_quarter'] = null;
        }

        // Only set columns that exist
        $cols = Schema::getColumnListing('companies');
        $safe = [];
        foreach (['vat_number','utr','auth_code','vat_period','vat_quarter'] as $k) {
            if (array_key_exists($k, $data) && in_array($k, $cols, true)) $safe[$k] = $data[$k];
        }

        $company->fill($safe)->save();

        return response()->json([
            'ok' => true,
            'company' => [
                'id' => $company->id,
                'vat_number' => $company->vat_number,
                'utr' => $company->utr,
                'auth_code' => $company->auth_code,
                'vat_period' => $company->vat_period,
                'vat_quarter' => $company->vat_quarter,
            ],
        ]);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('status', "Deleted {$company->number}");
    }
}
