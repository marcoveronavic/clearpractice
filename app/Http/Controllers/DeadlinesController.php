<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeadlinesController extends Controller
{
    private function path(): string
    {
        return 'deadlines.json';
    }

    private function readAll(): array
    {
        if (!Storage::exists($this->path())) {
            return [];
        }
        $data = json_decode(Storage::get($this->path()), true);
        return is_array($data) ? $data : [];
    }

    private function writeAll(array $rows): void
    {
        Storage::put($this->path(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $deadlines = $this->readAll();
        return view('deadlines', ['deadlines' => $deadlines]);
    }

    public function store(Request $req)
    {
        $title = trim((string) $req->input('title', ''));
        if ($title === '') {
            return redirect()->route('deadlines.index')->with('error', 'Title is required.');
        }

        $rows   = $this->readAll();
        $rows[] = [
            'id'       => uniqid('ddl_', true),
            'title'    => $title,
            'due_date' => $req->input('due_date'),
            'related'  => $req->input('related'),
            'notes'    => $req->input('notes'),
            'status'   => $req->input('status', 'open'), // open | done
            'created'  => now()->toDateTimeString(),
        ];

        $this->writeAll($rows);

        return redirect()->route('deadlines.index')->with('success', 'Deadline added.');
    }

    public function destroy(string $id)
    {
        $rows = array_values(array_filter(
            $this->readAll(),
            fn($r) => ($r['id'] ?? '') !== $id
        ));

        $this->writeAll($rows);

        return redirect()->route('deadlines.index')->with('success', 'Deadline removed.');
    }
}
