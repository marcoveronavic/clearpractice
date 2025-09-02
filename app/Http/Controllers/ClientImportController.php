<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClientImportController extends Controller
{
    /**
     * Add a director/person from the CH modal to the user's Clients.
     * Expects: name (required), company_number (nullable), company_name (nullable), payload (array)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'company_number' => ['nullable', 'string', 'max:20'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'payload'        => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // ---- Detect columns that actually exist in your clients table ----
        $nameCol    = $this->pickColumn('clients', [
            'name', 'display_name', 'full_name', 'client_name', 'contact_name', 'legal_name', 'primary_name', 'title',
        ]);
        $firstCol   = $this->pickColumn('clients', ['first_name', 'firstname', 'given_name', 'forename', 'first']);
        $lastCol    = $this->pickColumn('clients', ['last_name', 'lastname', 'surname', 'family_name', 'last']);
        $noCol      = $this->pickColumn('clients', ['company_number', 'number', 'company_no', 'co_number', 'comp_no']);
        $coNameCol  = $this->pickColumn('clients', ['company_name', 'company', 'organisation_name', 'org_name', 'business_name', 'trading_name']);
        $sourceCol  = $this->pickColumn('clients', ['source', 'origin', 'source_system', 'source_of_truth']);
        $rawCol     = $this->pickColumn('clients', ['raw_json', 'raw', 'data', 'payload', 'json']);

        $hasCreated = Schema::hasColumn('clients', 'created_at');
        $hasUpdated = Schema::hasColumn('clients', 'updated_at');

        // ---- Decide how to persist the human name ----
        // Preferred: single name column; or split first/last.
        // Fallback: if neither exists, write the name into company_name (display field) so we can still create a row.
        $useFallbackCompanyNameForPerson = false;
        if (! $nameCol && ! $firstCol && ! $lastCol) {
            if ($coNameCol) {
                $useFallbackCompanyNameForPerson = true; // store person name in company_name
            } else {
                return response()->json([
                    'ok'      => false,
                    'message' => "Your 'clients' table does not have any name columns. ".
                        "Expected one of [name, display_name, full_name, client_name, contact_name, legal_name, primary_name, title] ".
                        "or split columns like [first_name/lastname/surname], or at least a company_name column to use as a display field.",
                ], 422);
            }
        }

        // Split the person name if we need first/last
        [$first, $last] = $this->splitName($data['name']);

        // ---- Build a match key to avoid duplicates ----
        $match = [];
        if ($noCol && !empty($data['company_number'])) {
            $match[$noCol] = $data['company_number'];
        }

        if ($nameCol) {
            $match[$nameCol] = $data['name'];
        } elseif ($firstCol || $lastCol) {
            if ($firstCol && $first) $match[$firstCol] = $first;
            if ($lastCol  && $last)  $match[$lastCol]  = $last;
        } elseif ($useFallbackCompanyNameForPerson && $coNameCol) {
            // Fallback: match on company_name using the person's name
            $match[$coNameCol] = $data['name'];
        }

        if (empty($match)) {
            // last resort: at least include the visible name in match
            if ($nameCol) {
                $match[$nameCol] = $data['name'];
            } elseif ($useFallbackCompanyNameForPerson && $coNameCol) {
                $match[$coNameCol] = $data['name'];
            }
        }

        // ---- Values to write; only include columns that exist ----
        $values = [];

        if ($nameCol) {
            $values[$nameCol] = $data['name'];
        }
        if ($firstCol && $first) {
            $values[$firstCol] = $first;
        }
        if ($lastCol && $last) {
            $values[$lastCol] = $last;
        }

        if ($useFallbackCompanyNameForPerson && $coNameCol) {
            // Use the person’s name for the display column (company_name)
            $values[$coNameCol] = $data['name'];
        } elseif ($coNameCol && array_key_exists('company_name', $data)) {
            // Normal case: if a company_name was provided, persist it
            $values[$coNameCol] = $data['company_name'];
        }

        if ($noCol && array_key_exists('company_number', $data)) {
            $values[$noCol] = $data['company_number'];
        }
        if ($sourceCol) {
            $values[$sourceCol] = 'companies_house';
        }

        // Tuck the original payload (and original company_name) into JSON if the column exists
        if ($rawCol) {
            $payload = $data['payload'] ?? [];
            if (!empty($data['company_name'])) {
                $payload['_origin_company_name'] = $data['company_name'];
            }
            $values[$rawCol] = json_encode($payload);
        }

        if ($hasCreated) $values['created_at'] = now();
        if ($hasUpdated) $values['updated_at'] = now();

        // ---- Upsert client + attach to the user via pivot ----
        DB::transaction(function () use ($match, $values, $user, &$clientId) {
            DB::table('clients')->updateOrInsert($match, $values);
            $clientId = DB::table('clients')->where($match)->value('id');

            // Pivot timestamps only if present
            $pivotValues = [];
            if (Schema::hasColumn('client_user', 'created_at')) $pivotValues['created_at'] = now();
            if (Schema::hasColumn('client_user', 'updated_at')) $pivotValues['updated_at'] = now();

            DB::table('client_user')->updateOrInsert(
                ['client_id' => $clientId, 'user_id' => $user->id],
                $pivotValues
            );
        });

        return response()->json([
            'ok'      => true,
            'message' => $data['name'].' added to your clients.',
        ]);
    }

    /**
     * Return the first existing column in $candidates for $table, or null.
     */
    private function pickColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }
        return null;
    }

    /**
     * Naive full-name split: "First Middle Last" -> ["First Middle", "Last"]
     */
    private function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($parts) <= 1) {
            return [$full, null];
        }
        $last  = array_pop($parts);
        $first = implode(' ', $parts);
        return [$first, $last];
    }
}
