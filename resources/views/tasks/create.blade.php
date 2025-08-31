<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Task</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        label { display: block; margin-top: 8px; }
        input, select { width: 300px; max-width: 100%; }
    </style>
</head>
<body>
<h1>Create Task</h1>
<p><a href="{{ route('tasks.index') }}">Back to Tasks</a></p>

@if ($errors->any())
    <div style="color: red;">
        <strong>Whoops!</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tasks.store') }}">
    @csrf

    <label>Title
        <input type="text" name="title" value="{{ old('title') }}" required>
    </label>

    <label>Assigned To (User)
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->name }} ({{ $u->email }})</option>
            @endforeach
        </select>
    </label>

    <label>Deadline
        <input type="date" name="deadline" value="{{ old('deadline') }}" required>
    </label>

    <fieldset style="margin-top: 12px;">
        <legend>Related Entity (optional)</legend>
        <label>Type
            <select name="related_type">
                <option value="" @selected(old('related_type','')==='')>None</option>
                <option value="company" @selected(old('related_type')==='company')>Company</option>
                <option value="individual" @selected(old('related_type')==='individual')>Individual</option>
            </select>
        </label>
        <label>Company Number
            <input type="text" name="company_number" value="{{ old('company_number') }}">
        </label>
        <label>Individual ID
            <input type="number" name="individual_id" value="{{ old('individual_id') }}">
        </label>
    </fieldset>

    <div style="margin-top:12px;">
        <button type="submit">Create</button>
    </div>
</form>
</body>
</html>
