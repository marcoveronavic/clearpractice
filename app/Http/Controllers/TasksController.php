<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssigned;

class TasksController extends Controller
{
    private function tasksPath(): string { return 'tasks.json'; }
    private function usersPath(): string { return 'users.json'; }

    private function readTasks(): array
    {
        if (!Storage::exists($this->tasksPath())) return [];
        $data = json_decode(Storage::get($this->tasksPath()), true);
        return is_array($data) ? $data : [];
    }

    private function writeTasks(array $rows): void
    {
        Storage::put($this->tasksPath(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    private function readUsers(): array
    {
        if (!Storage::exists($this->usersPath())) return [];
        $data = json_decode(Storage::get($this->usersPath()), true);
        return is_array($data) ? $data : [];
    }

    public function index()
    {
        $tasks = $this->readTasks();
        $users = $this->readUsers();

        // index users by id
        $uIndex = [];
        foreach ($users as $u) {
            if (!empty($u['id'])) $uIndex[$u['id']] = $u;
        }

        // attach assigned-to and assigned-by
        foreach ($tasks as &$t) {
            $to = $t['assigned_to_id'] ?? null;
            $by = $t['assigned_by_id'] ?? null;
            $t['assigned']    = $to && isset($uIndex[$to]) ? $uIndex[$to] : null;
            $t['assigned_by'] = $by && isset($uIndex[$by]) ? $uIndex[$by] : null;
        }

        return view('tasks', ['tasks' => $tasks, 'users' => $users]);
    }

    public function store(Request $req)
    {
        $rows = $this->readTasks();

        $task = [
            'id'              => uniqid('task_', true),
            'title'           => trim((string)$req->input('title')),
            'assigned_by_id'  => $req->input('assigned_by_id') !== '' ? $req->input('assigned_by_id') : null,
            'assigned_to_id'  => $req->input('assigned_to_id') !== '' ? $req->input('assigned_to_id') : null,
            'due_date'        => trim((string)$req->input('due_date')),
            'status'          => trim((string)$req->input('status', 'todo')),
            'created'         => now()->toDateTimeString(),
        ];

        $rows[] = $task;
        $this->writeTasks($rows);

        // ---- Send email reminder to the assignee (if email available) ----
        try {
            $users = $this->readUsers();

            $uIndex = [];
            foreach ($users as $u) if (!empty($u['id'])) $uIndex[$u['id']] = $u;

            $assignedTo = ($task['assigned_to_id'] ?? null) && isset($uIndex[$task['assigned_to_id']])
                ? $uIndex[$task['assigned_to_id']]
                : null;

            $assignedBy = ($task['assigned_by_id'] ?? null) && isset($uIndex[$task['assigned_by_id']])
                ? $uIndex[$task['assigned_by_id']]
                : null;

            if ($assignedTo && !empty($assignedTo['email'])) {
                Mail::to($assignedTo['email'])->send(new TaskAssigned($task, $assignedTo, $assignedBy));
            }
        } catch (\Throwable $e) {
            // swallow mail errors; still proceed
            // You can log if you like: \Log::warning('Task mail failed: '.$e->getMessage());
        }

        return redirect()->route('tasks.index')->with('success', 'Task created and reminder sent (if email available).');
    }

    public function destroy(string $id)
    {
        $rows = array_values(array_filter($this->readTasks(), fn ($r) => ($r['id'] ?? '') !== $id));
        $this->writeTasks($rows);
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }
}
