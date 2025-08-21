<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientsQuickController extends Controller
{
    private function path(): string
    {
        return 'clients.json';
    }

    private function readAll(): array
    {
        if (!Storage::exists($this->path())) {
            return [];
        }
        $raw = Storage::get($this->path());
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    private function writeAll(array $rows): void
    {
        Storage::put($this->path(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    /** GET /clients */
    public function index()
    {
        $clients = $this->readAll();
        return view('clients', compact('clients'));
    }

    /** POST /clients  (create or update) */
    public function store(Request $request)
    {
        $rows = $this->readAll();

        $id = trim((string) $request->input('id', ''));

        if ($id !== '') {
            // update
            foreach ($rows as &$r) {
                if (($r['id'] ?? '') === $id) {
                    $r['name']    = trim((string) $request->input('name', ''));
                    $r['surname'] = trim((string) $request->input('surname', ''));
                    $r['email']   = trim((string) $request->input('email', ''));
                    $r['phone']   = trim((string) $request->input('phone', ''));
                    $r['address'] = trim((string) $request->input('address', ''));
                    $r['notes']   = trim((string) $request->input('notes', ''));
                    break;
                }
            }
            unset($r);
            $this->writeAll($rows);
            return redirect()->route('clients.index')->with('success', 'Client updated.');
        }

        // create
        $rows[] = [
            'id'         => Str::uuid()->toString(),
            'name'       => trim((string) $request->input('name', '')),
            'surname'    => trim((string) $request->input('surname', '')),
            'email'      => trim((string) $request->input('email', '')),
            'phone'      => trim((string) $request->input('phone', '')),
            'address'    => trim((string) $request->input('address', '')),
            'notes'      => trim((string) $request->input('notes', '')),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $this->writeAll($rows);
        return redirect()->route('clients.index')->with('success', 'Client added.');
    }

    /** DELETE /clients/{id} */
    public function destroy(string $id)
    {
        $rows = $this->readAll();
        $rows = array_values(array_filter($rows, fn ($r) => ($r['id'] ?? '') !== $id));
        $this->writeAll($rows);
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}

