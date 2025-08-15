<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Clients</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      :root { --border:#e5e7eb; --muted:#6b7280; --bg:#f9fafb; }
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color:#111827; background:#fafafa; }
      a { color:#2563eb; text-decoration: none; }
      a:hover { text-decoration: underline; }
      h1 { margin: 0 0 12px; }
      .container { max-width: 980px; margin: 0 auto; }
      .card { background:#fff; border:1px solid var(--border); border-radius:12px; }
      .header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
      .flash { padding:10px 12px; border:1px solid var(--border); border-radius:10px; margin:10px 0; }
      .flash.ok { background:#ecfdf5; }
      .flash.err { background:#fef2f2; }
      table { width:100%; border-collapse: collapse; }
      th, td { padding: 12px; border-top:1px solid var(--border); text-align:left; }
      thead th { background: var(--bg); border-top:0; }
      .muted { color: var(--muted); }
      .btn { padding: 6px 10px; border:1px solid var(--border); border-radius:8px; background:#fff; cursor:pointer; }
      .btn.danger { border-color:#ef4444; color:#b91c1c; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="header">
        <h1>Clients</h1>
        <div><a href="{{ url('/ch') }}">← Back to search</a></div>
      </div>

      @if (session('success'))
        <div class="flash ok">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="flash err">{{ session('error') }}</div>
      @endif

      <div class="card">
        <table>
          <thead>
            <tr>
              <th>Number</th>
              <th>Name</th>
              <th>Status</th>
              <th>Notes</th>
              <th>Added</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($clients as $c)
              <tr>
                <td>{{ $c['number'] ?? '' }}</td>
                <td>
                  <div><strong>{{ $c['name'] ?? '' }}</strong></div>
                  @if (!empty($c['address']) || !empty($c['company_name']))
                    <div class="muted">
                      {{ $c['company_name'] ?? '' }}
                      {{ (!empty($c['company_name']) && !empty($c['address'])) ? ' • ' : '' }}
                      {{ $c['address'] ?? '' }}
                    </div>
                  @endif
                </td>
                <td>{{ $c['status'] ?? '' }}</td>
                <td class="muted">{{ $c['notes'] ?? '' }}</td>
                <td class="muted">{{ $c['added_at'] ?? '' }}</td>
                <td>
                  <form action="{{ route('clients.destroy', ['id' => $c['id']]) }}" method="POST" onsubmit="return confirm('Delete this client?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="muted">No clients yet.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
