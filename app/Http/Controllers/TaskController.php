<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::latest('id')->paginate(15);
        $users = User::orderBy('name')->get();
        return view('tasks.index', compact('tasks', 'users'));



    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('tasks.create', compact('users'));
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
            $companyNumber = null;
            $individualId  = null;
        }

        $data = $request->validate($rules);

        Task::create([
            'user_id'        => $data['user_id'],
            'title'          => $data['title'],
            'deadline'       => Carbon::parse($data['deadline'])->startOfDay(),
            'company_number' => $companyNumber,
            'individual_id'  => $individualId,
        ]);

        return redirect()->route('tasks.index')->with('status', 'Task created.');
    }

    public function show(Task $task)
    {
        $task->load(['user','company','individual']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $users = User::orderBy('name')->get();
        $task->load(['user','company','individual']);
        return view('tasks.edit', compact('task','users'));
    }

    public function update(Request $request, Task $task)
    {
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
            $companyNumber = null;
            $individualId  = null;
        }

        $data = $request->validate($rules);

        $task->update([
            'user_id'        => $data['user_id'],
            'title'          => $data['title'],
            'deadline'       => Carbon::parse($data['deadline'])->startOfDay(),
            'company_number' => $companyNumber,
            'individual_id'  => $individualId,
        ]);

        return redirect()->route('tasks.index')->with('status', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('status', 'Task deleted.');
    }
}
