<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientsController extends Controller
{
    public function index()
    {
        $disk = Storage::disk('local');
        $raw = $disk->exists('clients.json') ? $disk->get('clients.json') : '[]';
        $clients = json_decode($raw, true) ?: [];

        return view('clients', compact('clients'));
    }

    public function store(Request $request)
    {
        $payload = $request->input('payload');
        if (!$payload) {
            return back()->withErrors(['payload' => 'Missing payload']);
        }

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            return back()->withErrors(['payload' => 'Invalid payload JSON']);
        }

        $record = [
            'id'         => Str::uuid()->toString(),
            'number'     => $data['number'] ?? $data['company_number'] ?? null,
            'name'       => $data['name'] ?? $data['company_name'] ?? '(unknown)',
            'status'     => $data['status'] ?? $data['company_status'] ?? null,
            'registered_office' => $data['registered_office'] ?? $data['registered_office_address'] ?? null,
            'accounts'   => $data['accounts'] ?? [],
            'confirmation_statement' => $data['confirmation_statement'] ?? [],
            'directors'  => $data['directors'] ?? [],
            'pscs'       => $data['pscs'] ?? [],
            'added_at'   => now()->toDateTimeString(),
        ];

        $disk = Storage::disk('local');
        if (!$disk->exists('clients.json')) {
            $disk->put('clients.json', '[]');
        }
        $list = json_decode($disk->get('clients.json'), true) ?: [];

        $replaced = false;
        foreach ($list as $i => $c) {
            if (($c['number'] ?? null) && $c['number'] === $record['number']) {
                $list[$i] = $record;
                $replaced = true;
                break;
            }
        }
        if (!$replaced) {
            $list[] = $record;
        }

        $disk->put('clients.json', json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('clients.index')->with('success', $record['name'].' added to Clients.');
    }
}
