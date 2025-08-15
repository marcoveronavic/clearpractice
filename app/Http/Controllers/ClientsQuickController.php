<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Client;

class ClientsQuickController extends Controller
{
    // POST /api/clients/quick-add  (JSON)
    // Body: { name: "Director Name", company_number?: "...", company_name?: "..." }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'company_number'  => 'nullable|string|max:32',
            'company_name'    => 'nullable|string|max:255',
        ]);

        if (!Schema::hasTable('clients') || !Schema::hasColumn('clients', 'name')) {
            return response()->json(['ok' => false, 'error' => 'Clients table is missing (run migrations).'], 422);
        }

        $cols = Schema::getColumnListing('clients');

        // Find-or-create by name (+ company_number when the column exists and was sent)
        $query = ['name' => $data['name']];
        if (in_array('company_number', $cols, true) && !empty($data['company_number'])) {
            $query['company_number'] = $data['company_number'];
        }

        $client = Client::firstOrCreate($query);

        // Fill optional columns if they exist
        $updates = [];
        if (in_array('company_name', $cols, true) && !empty($data['company_name'])) {
            $updates['company_name'] = $data['company_name'];
        }
        if (!empty($updates)) {
            $client->fill($updates)->save();
        }

        return response()->json([
            'ok' => true,
            'client' => [
                'id'   => $client->id,
                'name' => $client->name,
            ],
        ]);
    }
}

