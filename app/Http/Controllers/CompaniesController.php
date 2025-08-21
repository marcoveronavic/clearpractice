<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompaniesController extends Controller
{
    /* ---------------------- storage helpers ---------------------- */
    private function path(): string { return 'companies.json'; }

    private function readAll(): array
    {
        if (!Storage::exists($this->path())) return [];
        $j = json_decode(Storage::get($this->path()), true);
        return is_array($j) ? $j : [];
    }

    private function writeAll(array $rows): void
    {
        Storage::put($this->path(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    /* --------------------------- pages --------------------------- */
    public function index()
    {
        $companies = $this->readAll();
        return view('companies', ['companies' => $companies]);
    }

    public function store(Request $req)
    {
        $rows = $this->readAll();

        // Accept both manual form & CH payloads
        $number        = trim((string)($req->input('number') ?? $req->input('company_number') ?? ''));
        $name          = trim((string)($req->input('name') ?? $req->input('company_name') ?? ''));
        $status        = trim((string)($req->input('status') ?? $req->input('company_status') ?? ''));
        $type          = trim((string)($req->input('type') ?? ''));
        $jurisdiction  = trim((string)($req->input('jurisdiction') ?? ''));
        $address       = trim((string)($req->input('address') ?? $req->input('address_snippet') ?? ''));
        $created       = trim((string)($req->input('date_of_creation') ?? $req->input('created') ?? ''));

        // SIC codes may be string or array
        $sicIn = $req->input('sic_codes');
        if (is_string($sicIn) && $sicIn !== '') {
            $sic_codes = array_values(array_filter(array_map('trim', explode(',', $sicIn)), fn($v)=>$v!==''));
        } elseif (is_array($sicIn)) {
            $sic_codes = array_values(array_filter(array_map('trim', $sicIn), fn($v)=>$v!==''));
        } else {
            $sic_codes = [];
        }

        $now = now()->toDateTimeString();
        $idx = null;
        foreach ($rows as $i => $r) {
            if (($r['number'] ?? '') === $number && $number !== '') { $idx = $i; break; }
        }

        $data = [
            'number'           => $number,
            'name'             => $name,
            'status'           => $status,
            'type'             => $type,
            'jurisdiction'     => $jurisdiction,
            'address'          => $address,
            'date_of_creation' => $created,
        ];
        if (!empty($sic_codes)) $data['sic_codes'] = $sic_codes;

        if ($idx !== null) {
            // update existing (keep id/meta/added_at)
            $existing = $rows[$idx];
            $rows[$idx] = array_merge($existing, $data);
            if (!isset($rows[$idx]['id']))       $rows[$idx]['id'] = uniqid('co_', true);
            if (!isset($rows[$idx]['added_at'])) $rows[$idx]['added_at'] = $now;
            $rows[$idx]['updated_at'] = $now;
        } else {
            // create new
            $data['id']       = uniqid('co_', true);
            $data['added_at'] = $now;
            $rows[] = $data;
        }

        $this->writeAll($rows);
        return redirect()->route('companies.index')->with('success', 'Company saved.');
    }

    public function destroy(string $id)
    {
        $rows = $this->readAll();
        $new  = [];
        foreach ($rows as $i => $r) {
            $match = false;
            if ((string)($r['id'] ?? '')      === (string)$id) $match = true;
            if (!$match && (string)$i         === (string)$id) $match = true;
            if (!$match && (string)($r['number'] ?? '') === (string)$id) $match = true;
            if (!$match) $new[] = $r;
        }
        $this->writeAll($new);
        return redirect()->route('companies.index')->with('success', 'Company deleted.');
    }

    /* ----------------------- meta/extras update ----------------------- */
    public function updateMeta(Request $req, string $number)
    {
        $number = trim($number);
        $rows   = $this->readAll();
        $found  = false;

        // related companies: accept JSON array or comma-separated
        $relatedRaw = (string) $req->input('related', '');
        $related = [];
        if ($relatedRaw !== '') {
            $try = json_decode($relatedRaw, true);
            if (is_array($try)) {
                $related = array_values(array_filter(array_map('trim', $try), fn($v)=>$v!==''));
            } else {
                $related = array_values(array_filter(array_map('trim', explode(',', $relatedRaw)), fn($v)=>$v!==''));
            }
        }

        foreach ($rows as &$c) {
            if (($c['number'] ?? '') === $number) {
                $meta = $c['meta'] ?? [];

                $meta['authentication_code'] = trim((string)$req->input('authentication_code', $meta['authentication_code'] ?? ''));
                $meta['utr']                 = trim((string)$req->input('utr',                 $meta['utr'] ?? ''));
                $meta['registered_office']   = trim((string)$req->input('registered_office',   $meta['registered_office'] ?? ''));
                $meta['vat_number']          = trim((string)$req->input('vat_number',          $meta['vat_number'] ?? ''));
                $meta['vat_quarter']         = trim((string)$req->input('vat_quarter',         $meta['vat_quarter'] ?? ''));
                $meta['gov_id']              = trim((string)$req->input('gov_id',              $meta['gov_id'] ?? ''));
                $meta['gov_password']        = trim((string)$req->input('gov_password',        $meta['gov_password'] ?? ''));
                $meta['paye_office_ref']     = trim((string)$req->input('paye_office_ref',     $meta['paye_office_ref'] ?? ''));
                $meta['employer_ref']        = trim((string)$req->input('employer_ref',        $meta['employer_ref'] ?? ''));
                $meta['related_companies']   = $related;
                $meta['updated_at']          = now()->toDateTimeString();

                $c['meta'] = $meta;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return redirect()->route('companies.index')->with('error', "Company $number not found.");
        }

        $this->writeAll($rows);
        return redirect()->route('companies.index')->with('success', 'Company details updated.');
    }
}
