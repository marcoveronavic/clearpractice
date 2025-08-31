<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Task Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>Task Details</h1>
<p><a href="{{ route('tasks.index') }}">Back to Tasks</a></p>

<dl>
    <dt>Title</dt>
    <dd>{{ $task->title }}</dd>

    <dt>Assigned To</dt>
    <dd>{{ $task->user?->name ?? '-' }}</dd>

    <dt>Deadline</dt>
    <dd>{{ optional($task->deadline)->toDateString() ?? '-' }}</dd>

    <dt>Related</dt>
    <dd>
        @if($task->company)
            Company #{{ $task->company->number }}
        @elseif($task->individual)
            Individual #{{ $task->individual->id }}
        @else
            -
        @endif
    </dd>
</dl>

<p>
    <a href="{{ route('tasks.edit', $task) }}">Edit</a>
</p>
<form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
 </form>
</body>
</html>
