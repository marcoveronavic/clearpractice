<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    private function path(): string { return 'users.json'; }

    private function readAll(): array {
        if (!Storage::exists($this->path())) return [];
        $data = json_decode(Storage::get($this->path()), true);
        return is_array($data) ? $data : [];
    }

    private function writeAll(array $rows): void {
        Storage::put($this->path(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    public function index() {
        $users = $this->readAll();
        return view('users', ['users' => $users]);
    }

    public function store(Request $req) {
        $name = trim((string)$req->input('name', ''));
        if ($name === '') {
            return redirect()->route('users.index')->with('error', 'Name is required.');
        }
        $users = $this->readAll();
        $users[] = [
            'id'    => uniqid('usr_', true),
            'name'  => $name,
            'email' => $req->input('email'),
            'phone' => $req->input('phone'),
            'added' => now()->toDateTimeString(),
        ];
        $this->writeAll($users);
        return redirect()->route('users.index')->with('success', 'User added.');
    }

    public function destroy(string $id) {
        $users = array_values(array_filter($this->readAll(), fn($u) => ($u['id'] ?? '') !== $id));
        $this->writeAll($users);
        return redirect()->route('users.index')->with('success', 'User removed.');
    }
}
