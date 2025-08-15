<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Clients</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f7f7f8;margin:0}
    .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
    a.btn{color:#0ea5e9;text-decoration:none}
    table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden}
    th,td{padding:10px;border-bottom:1px solid #e5e7eb;text-align:left}
    form{display:inline}
    button{background:#ef4444;border:none;color:#fff;padding:6px 10px;border-radius:6px;cursor:pointer}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Clients</h1>
    <p><a href="{{ route('ch.page') }}" class="btn">← Back to search</a></p>

    @if(session('ok')) <p>{{ session('ok') }}</p> @endif

    <table>
      <thead><tr>
        <th>Number</th><th>Name</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($clients as $c)
        <tr>
          <td>{{ $c['number'] ?? ($c['company']['number'] ?? '—') }}</td>
          <td>{{ $c['company']['name'] ?? '—' }}</td>
          <td>{{ $c['company']['status'] ?? '—' }}</td>
          <td>
            <form method="POST" action="{{ route('clients.destroy', $c['number'] ?? ($c['company']['number'] ?? '')) }}">
              @csrf @method('DELETE')
              <button type="submit">Remove</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4">No clients yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</body>
</html>
