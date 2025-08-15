<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TasksController extends Controller
{
    private function path(): string { return 'tasks.json'; }
    private function usersPath(): string { return 'users.json'; }

    private function readAll(): array {
        if (!Storage::exists($this->path())) return [];
        $data = json_decode(Storage::get($this->path()), true);
        return is_array($data) ? $data : [];
    }
    private function writeAll(array $rows): void {
        Storage::put($this->path(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }
    private function readUsers(): array {
        if (!Storage::exists($this->usersPath())) return [];
        $data = json_decode(Storage::get($this->usersPath()), true);
        return is_array($data) ? $data : [];
    }

    public function index() {
        $tasks = $this->readAll();
        $users = $this->readUsers(); // for the "Assigned to" dropdown
        return view('tasks', ['tasks' => $tasks, 'users' => $users]);
    }

    public function store(Request $req) {
        $title = trim((string)$req->input('title', ''));
        if ($title === '') {
            return redirect()->route('tasks.index')->with('error', 'Task title is required.');
        }

        $users = $this->readUsers();
        $assignee = null;
        if ($id = $req->input('assigned_to_id')) {
            foreach ($users as $u) {
                if (($u['id'] ?? null) === $id) { $assignee = $u; break; }
            }
        }

        $tasks   = $this->readAll();
        $tasks[] = [
            'id'       => uniqid('task_', true),
            'title'    => $title,
            'status'   => $req->input('status', 'todo'),     // todo | in-progress | done
            'due_date' => $req->input('due_date'),
            'assigned' => $assignee ? [
                'id' => $assignee['id'],
                'name' => $assignee['name'] ?? null,
                'email'=> $assignee['email'] ?? null,
            ] : null,
            'created'  => now()->toDateTimeString(),
        ];

        $this->writeAll($tasks);
        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function destroy(string $id) {
        $tasks = array_values(array_filter($this->readAll(), fn($t) => ($t['id'] ?? '') !== $id));
        $this->writeAll($tasks);
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }
}
