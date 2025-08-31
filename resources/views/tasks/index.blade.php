@extends('layouts.app')

@section('title', 'Tasks')

{{-- Optional head additions for this page --}}
@section('head')
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        .actions form { display: inline; }
    </style>

    {{-- Only include Vite assets if a build exists locally --}}
    @php $hasVite = file_exists(public_path('build/manifest.json')); @endphp
    @if($hasVite)
        @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
@endsection

@section('content')
    <h1>Tasks</h1>

    @if(session('status'))
        <p style="color: green">{{ session('status') }}</p>
    @endif

    <p><a href="{{ route('tasks.create') }}">Create Task</a></p>

    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Assigned To</th>
            <th>Deadline</th>
            <th>Related</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->user?->name ?? '-' }}</td>
                <td>{{ optional($task->deadline)->toDateString() ?? '-' }}</td>
                <td>
                    @if($task->company)
                        Company #{{ $task->company->number }}
                    @elseif($task->individual)
                        Individual #{{ $task->individual->id }}
                    @else
                        -
                    @endif
                </td>
                <td class="actions">
                    <a href="{{ route('tasks.show', $task) }}">Show</a>
                    <a href="{{ route('tasks.edit', $task) }}">Edit</a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No tasks found.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
