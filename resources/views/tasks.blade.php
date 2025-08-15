<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Tasks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      :root { --border:#e5e7eb; --muted:#6b7280; --bg:#f9fafb; }
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color:#111827; background:#fafafa; }
      a { color:#2563eb; text-decoration:none; } a:hover{ text-decoration:underline; }
      .container { max-width: 980px; margin: 0 auto; }
      h1 { margin:0 0 12px; }
      .nav { margin-bottom: 12px; }
      .flash { padding:10px 12px; border:1px solid var(--border); border-radius:10px; margin:10px 0; }
      .ok { background:#ecfdf5; } .err { background:#fef2f2; }
      .card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:14px; }
      table { width:100%; border-collapse: collapse; margin-top:10px; }
      th, td { padding: 10px 12px; border-top:1px solid var(--border); text-align:left; }
      thead th { background: var(--bg); border-top:0; }
      .muted { color: var(--muted); }
      .btn { padding: 8px 12px; border:1px solid var(--border); border-radius:8px; background:#fff; cursor:pointer; }
      .danger { border-color:#ef4444; color:#b91c1c; }
      input, select { padding:8px 10px; border:1px solid var(--border); border-radius:8px; width:100%; }
      form.inline { display:grid; grid-template-columns: 2fr 1.5fr 1fr 1fr auto; gap:10px; align-items:center; }
      .pill { display:inline-block; font-size:12px; border:1px solid var(--border); border-radius:999px; padding:2px 8px; margin:0 6px 0 0; background:#fff; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="nav">
        <a href="{{ url('/ch') }}">CH Search</a> •
        <a href="{{ route('clients.index') }}">Clients</a> •
        <strong>Tasks</strong> •
        <a href="{{ route('users.index') }}">Users</a> •
        <a href="{{ route('deadlines.index') }}">Deadlines</a>
      </div>

      <h1>Tasks</h1>

      @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
      @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

      <div class="card">
        <form class="inline" action="{{ route('tasks.store') }}" method="POST">
          @csrf
          <input type="text"   name="title" placeholder="Task title" required>
          <select name="assigned_to_id">
            <option value="">— Assign to —</option>
            @foreach (($users ?? []) as $u)
              <option value="{{ $u['id'] }}">{{ $u['name'] }}{{ !empty($u['email']) ? ' ('.$u['email'].')' : '' }}</option>
            @endforeach
          </select>
          <input type="date"   name="due_date">
          <select name="status">
            <option value="todo">To do</option>
            <option value="in-progress">In progress</option>
            <option value="done">Done</option>
          </select>
          <button class="btn" type="submit">Create task</button>
        </form>

        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Assigned</th>
              <th>Due</th>
              <th>Status</th>
              <th>Created</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($tasks as $t)
              <tr>
                <td><strong>{{ $t['title'] }}</strong></td>
                <td>
                  @if (!empty($t['assigned']['name']))
                    <span class="pill">{{ $t['assigned']['name'] }}</span>
                    <span class="muted">{{ $t['assigned']['email'] ?? '' }}</span>
                  @else
                    <span class="muted">Unassigned</span>
                  @endif
                </td>
                <td>{{ $t['due_date'] ?? '' }}</td>
                <td>{{ $t['status'] ?? '' }}</td>
                <td class="muted">{{ $t['created'] ?? '' }}</td>
                <td>
                  <form action="{{ route('tasks.destroy', ['id' => $t['id']]) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf @method('DELETE')
                    <button class="btn danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="muted">No tasks yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
