<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    private function storePath(): string
    {
        // Stored at storage/app/clients.json
        return 'clients.json';
    }

    private function readAll(): array
    {
        if (!Storage::exists($this->storePath())) {
            return [];
        }
        $json = Storage::get($this->storePath());
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    private function writeAll(array $items): void
    {
        Storage::put($this->storePath(), json_encode(array_values($items), JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $clients = $this->readAll();
        return view('clients', ['clients' => $clients]);
    }

    public function store(Request $request)
    {
        // Accept either "number" or "company_number" from forms
        $number = $request->input('number', $request->input('company_number'));
        $name   = trim((string) $request->input('name', ''));

        if ($name === '') {
            // Keep it simple: flash an error and show the list
            return redirect()->route('clients.index')->with('error', 'Invalid payload: name is required.');
        }

        $clients   = $this->readAll();
        $clients[] = [
            'id'            => uniqid('', true),
            'number'        => $number ?? '',
            'name'          => $name,
            'status'        => $request->input('status', 'prospect'),
            'address'       => $request->input('address'),
            'company_name'  => $request->input('company_name'),
            'notes'         => $request->input('notes'),
            'added_at'      => now()->toDateTimeString(),
        ];

        $this->writeAll($clients);

        return redirect()->route('clients.index')->with('success', 'Client added.');
    }

    public function destroy(string $id)
    {
        $clients = $this->readAll();
        $clients = array_values(array_filter($clients, fn ($c) => ($c['id'] ?? '') !== $id));
        $this->writeAll($clients);

        return redirect()->route('clients.index')->with('success', 'Client removed.');
    }
}
