<?php

namespace App\Http\Controllers;

use App\Services\CompaniesHouseClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Minimal client import endpoint (separate from company import).
 */
class ClientImportController extends Controller
{
    public function store(Request $request, CompaniesHouseClient $ch)
    {
        $user = Auth::user();
        if (! $user) {
            throw ValidationException::withMessages(['auth' => 'You must be signed in.']);
        }

        $data = $request->validate([
            'first_name'     => ['nullable', 'string', 'max:255'],
            'surname'        => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'company_number' => ['nullable', 'string', 'max:20'],
        ]);

        // optional: fetch a bit of CH context if company_number provided
        $companyContext = null;
        if (!empty($data['company_number'])) {
            try {
                $p = $ch->getCompanyProfile($data['company_number']);
                $companyContext = [
                    'company_number' => $data['company_number'],
                    'name' => $p['company_name'] ?? $p['title'] ?? null,
                    'status' => $p['company_status'] ?? null,
                    'type' => $p['type'] ?? null,
                ];
            } catch (\Throwable $e) {
                $companyContext = ['company_number' => $data['company_number'], 'error' => 'Could not fetch profile'];
            }
        }

        // TODO: persist a Client model later
        return response()->json([
            'ok' => true,
            'message' => 'Client import stub OK.',
            'received' => $data,
            'companyContext' => $companyContext,
        ]);
    }
}
