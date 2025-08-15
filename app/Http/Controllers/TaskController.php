<?php

namespace App\Http\Controllers;

use App\Jobs\SendTaskReminder;
use App\Mail\TaskAssignedMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        $tasks = Task::with(['user','company','individual'])->latest()->get();
        return view('tasks.index', compact('users', 'tasks'));
    }

    public function store(Request $request)
    {
        // related_type: 'company' | 'individual' | '' (none)
        $relatedType    = (string) $request->input('related_type', '');
        $companyNumber  = $request->input('company_number');
        $individualId   = $request->input('individual_id');

        $rules = [
            'user_id'  => ['required', 'exists:users,id'],
            'title'    => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'related_type' => ['nullable', Rule::in(['company','individual',''])],
        ];

        if ($relatedType === 'company') {
            $rules['company_number'] = ['required', Rule::exists('companies','number')];
            $companyNumber = (string) $companyNumber;
            $individualId  = null;
        } elseif ($relatedType === 'individual') {
            $rules['individual_id'] = ['required', Rule::exists('individuals','id')];
            $companyNumber = null;
            $individualId  = (int) $individualId;
        } else {
            // no related entity
            $companyNumber = null;
            $individualId  = null;
        }

        $data = $request->validate($rules);

        $task = Task::create([
            'user_id'        => $data['user_id'],
            'title'          => $data['title'],
            'deadline'       => Carbon::parse($data['deadline'])->startOfDay(),
            'company_number' => $companyNumber,
            'individual_id'  => $individualId,
        ]);

        if ($task->user?->email) {
            Mail::to($task->user->email)->send(new TaskAssignedMail($task));
        }

        $d45 = $task->deadline?->copy()->subDays(45);
        $d21 = $task->deadline?->copy()->subDays(21);

        if ($d45 && $d45->isFuture()) {
            SendTaskReminder::dispatch($task->id, '45 days before')->delay($d45);
        }
        if ($d21 && $d21->isFuture()) {
            SendTaskReminder::dispatch($task->id, '21 days before')->delay($d21);
        }

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }
}
