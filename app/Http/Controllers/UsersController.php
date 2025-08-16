<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    private function usersPath(): string { return 'users.json'; }

    private function readUsers(): array
    {
        if (!Storage::exists($this->usersPath())) return [];
        $data = json_decode(Storage::get($this->usersPath()), true);
        return is_array($data) ? $data : [];
    }

    private function writeUsers(array $rows): void
    {
        Storage::put($this->usersPath(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $users = $this->readUsers();
        return view('users', ['users' => $users]);
    }

    public function store(Request $req)
    {
        $rows   = $this->readUsers();
        $rows[] = [
            'id'      => uniqid('user_', true),
            'name'    => trim((string)$req->input('name')),
            'surname' => trim((string)$req->input('surname')),
            'email'   => trim((string)$req->input('email')),
            'phone'   => trim((string)$req->input('phone')),
            'address' => trim((string)$req->input('address', '')),
            'notes'   => trim((string)$req->input('notes', '')),
            'added'   => now()->toDateTimeString(),
        ];
        $this->writeUsers($rows);

        return redirect()->route('users.index')->with('success', 'User added.');
    }

    public function update(Request $req, string $id)
    {
        $rows = $this->readUsers();
        $updated = false;

        foreach ($rows as &$u) {
            if (($u['id'] ?? '') === $id) {
                $u['name']    = trim((string)$req->input('name',    $u['name']    ?? ''));
                $u['surname'] = trim((string)$req->input('surname', $u['surname'] ?? ''));
                $u['email']   = trim((string)$req->input('email',   $u['email']   ?? ''));
                $u['phone']   = trim((string)$req->input('phone',   $u['phone']   ?? ''));
                $u['address'] = trim((string)$req->input('address', $u['address'] ?? ''));
                $u['notes']   = trim((string)$req->input('notes',   $u['notes']   ?? ''));
                $u['updated'] = now()->toDateTimeString();
                $updated = true;
                break;
            }
        }

        if ($updated) $this->writeUsers($rows);

        return redirect()->route('users.index')
            ->with($updated ? 'success' : 'error', $updated ? 'User updated.' : 'User not found.');
    }

    public function destroy(string $id)
    {
        $rows = array_values(array_filter($this->readUsers(), fn ($r) => ($r['id'] ?? '') !== $id));
        $this->writeUsers($rows);

        return redirect()->route('users.index')->with('success', 'User removed.');
    }
}
