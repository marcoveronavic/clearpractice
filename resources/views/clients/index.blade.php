<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Clients</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:24px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #e5e7eb; padding:8px; text-align:left; vertical-align: top; }
    .row { display:flex; gap:12px; align-items:center; margin-bottom:12px; }
    .badge { display:inline-block; background:#eef2ff; color:#3730a3; padding:4px 8px; border-radius:6px; font-size:12px; }
    .bad { background:#fee2e2; color:#991b1b; }
    a { color:#2563eb; text-decoration:none; }
    button { padding:6px 10px; border:1px solid #e5e7eb; background:#fff; border-radius:6px; cursor:pointer; }
  </style>
</head>
<body>

<h1>Clients</h1>

<div class="row">
  <a href="{{ route('ch') }}">+ Add another from Companies House</a>
  @if (session('ok'))   <span class="badge">{{ session('ok') }}</span> @endif
  @if (session('error'))<span class="badge bad">{{ session('error') }}</span> @endif
</div>

<table>
  <thead>
    <tr>
      <th>Number</th>
      <th>Name</th>
      <th>Status</th>
      <th>Address</th>
      <th>Accounts</th>
      <th>Confirmation</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  @forelse ($clients as $c)
    <tr>
      <td>{{ $c['number'] ?? '—' }}</td>
      <td>{{ $c['name'] ?? '—' }}</td>
      <td>{{ $c['status'] ?? '—' }}</td>
      <td>{{ $c['address'] ?? '—' }}</td>
      <td>
        YE: {{ data_get($c,'accounts.year_end','—') }}<br>
        Next due: {{ data_get($c,'accounts.next_due','—') }}
      </td>
      <td>Next due: {{ data_get($c,'confirmation.next_due','—') }}</td>
      <td>
        <form method="POST" action="{{ route('clients.destroy', $c['number']) }}">
          @csrf @method('DELETE')
          <button type="submit" onclick="return confirm('Remove this client?')">Delete</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="7">No clients yet.</td></tr>
  @endforelse
  </tbody>
</table>

</body>
</html>
