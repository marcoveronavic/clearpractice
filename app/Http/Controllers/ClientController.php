<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    private function filePath(): string
    {
        return 'clients.json'; // stored at storage/app/clients.json
    }

    private function load(): array
    {
        if (! Storage::exists($this->filePath())) {
            return [];
        }
        $raw = Storage::get($this->filePath());
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function save(array $items): void
    {
        Storage::put($this->filePath(), json_encode(array_values($items), JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $clients = $this->load();
        return view('clients', compact('clients')); // Blade view comes in Step 3
    }

    public function store(Request $request)
    {
        $payload = $request->input('payload', '');
        $item = json_decode($payload, true);

        if (! is_array($item) || empty($item['number'])) {
            return redirect()->route('clients.index')->with('ok', 'Invalid payload');
        }

        $clients = $this->load();

        // Prevent duplicates by company number
        $number = (string) $item['number'];
        $found = false;
        foreach ($clients as $idx => $c) {
            if (($c['number'] ?? '') === $number) {
                $clients[$idx] = $item + ['saved_at' => now()->toDateTimeString()];
                $found = true;
                break;
            }
        }
        if (! $found) {
            $item['saved_at'] = now()->toDateTimeString();
            $clients[] = $item;
        }

        $this->save($clients);

        return redirect()->route('clients.index')->with('ok', 'Client saved.');
    }

    public function destroy(string $id)
    {
        $clients = $this->load();
        $clients = array_values(array_filter($clients, fn ($c) => ($c['number'] ?? '') !== $id));
        $this->save($clients);

        return redirect()->route('clients.index')->with('ok', 'Client removed.');
    }
}
