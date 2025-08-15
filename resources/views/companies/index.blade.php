<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Companies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root{--bg:#f6f7f9;--card:#fff;--text:#1f2937;--muted:#6b7280;--line:#e5e7eb;}
        *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--text);font:14px/1.4 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,"Helvetica Neue",Arial}
        a{color:#2563eb;text-decoration:none} a:hover{text-decoration:underline}
        .wrap{max-width:1100px;margin:32px auto;padding:0 16px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .header{display:flex;align-items:center;justify-content:space-between;margin:0 0 16px}
        h1{font-size:22px;margin:0}
        .btn{display:inline-block;padding:8px 12px;border:1px solid var(--line);border-radius:8px;background:#fff}
        .muted{color:var(--muted)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:12px 14px;text-align:left;border-top:1px solid var(--line)}
        thead th{background:#f8fafc;font-weight:600}
        tr:hover td{background:#fafafa}
        .badge{background:#e5e7eb;border-radius:999px;padding:2px 8px;font-size:12px}
        .empty{padding:18px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Companies</h1>
        <div>
            <a class="btn" href="{{ url('/deadlines') }}">Deadlines</a>
            <a class="btn" href="{{ url('/ch') }}">Companies House Search</a>
        </div>
    </div>

    <div class="card">
        @if ($companies->isEmpty())
            <div class="empty">
                <p class="muted">
                    No companies saved yet. Use
                    <a href="{{ url('/ch') }}">Companies House Search</a>
                    and click <strong>Add company</strong>.
                </p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($companies as $c)
                    <tr>
                        <td>
                            <a href="{{ route('companies.show', $c->number) }}">{{ $c->name }}</a>
                        </td>
                        <td><span class="badge">{{ $c->number }}</span></td>
                        <td>{{ $c->status ?? '—' }}</td>
                        <td>{{ $c->created ?? '—' }}</td>
                        <td class="muted">{{ $c->address ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
</body>
</html>
