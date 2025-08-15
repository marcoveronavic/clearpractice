<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Users</title>
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
      .row { display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:10px; }
      .btn { padding: 8px 12px; border:1px solid var(--border); border-radius:8px; background:#fff; cursor:pointer; }
      .danger { border-color:#ef4444; color:#b91c1c; }
      input, select { padding:8px 10px; border:1px solid var(--border); border-radius:8px; width:100%; }
      form.inline { display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:10px; align-items:center; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="nav">
        <a href="{{ url('/ch') }}">CH Search</a> •
        <a href="{{ route('clients.index') }}">Clients</a> •
        <a href="{{ route('tasks.index') }}">Tasks</a> •
        <strong>Users</strong> •
        <a href="{{ route('deadlines.index') }}">Deadlines</a>
      </div>

      <h1>Users</h1>

      @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
      @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

      <div class="card">
        <form class="inline" action="{{ route('users.store') }}" method="POST">
          @csrf
          <input type="text" name="name" placeholder="Name" required>
          <input type="email" name="email" placeholder="Email">
          <input type="text" name="phone" placeholder="Phone">
          <button class="btn" type="submit">Add user</button>
        </form>

        <table>
          <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr></thead>
          <tbody>
          @forelse ($users as $u)
            <tr>
              <td><strong>{{ $u['name'] }}</strong><div class="muted">{{ $u['added'] ?? '' }}</div></td>
              <td>{{ $u['email'] ?? '' }}</td>
              <td>{{ $u['phone'] ?? '' }}</td>
              <td>
                <form action="{{ route('users.destroy', ['id' => $u['id']]) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                  @csrf @method('DELETE')
                  <button class="btn danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted">No users yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
