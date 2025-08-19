<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add users — ClearCash</title>
  <link rel="stylesheet" href="/landing/styles.css" />
  <style>
    .wrap{ padding:48px 0 64px; }
    .card{ background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px; }
    .grid-3{ display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:10px; }
    .row{ display:grid;grid-template-columns:1fr 1fr 1.4fr;gap:10px;margin-top:8px; }
    input{ background:#F1F7F7;border:1px solid var(--border);border-radius:10px;padding:10px 12px; }
    input:focus{ border-color:#28C5F6; box-shadow:0 0 0 3px rgba(40,197,246,.18); outline:none; }
    .btn-mini{ padding:8px 12px;border-radius:10px;border:1px solid var(--border); }
    ul.list{ margin:10px 0 0;padding-left:18px;color:#64748b; }
  </style>
</head>
<body>
  <main class="wrap">
    <div class="container">
      <h1>Add users for {{ $lead->practice }}</h1>
      @if(session('ok'))
        <p class="lead" style="color:#0f766e;">{{ session('ok') }}</p>
      @endif

      <form method="post" action="{{ route('lead.users.store') }}" class="card" id="usersForm">
        @csrf
        <input type="hidden" name="t" value="{{ request('t') }}">
        <div class="grid-3" style="font-weight:600;margin-bottom:6px;">
          <div>First name</div><div>Last name</div><div>Email</div>
        </div>
        <div id="rows"></div>
        <button type="button" class="btn-mini" id="addRow">+ Add another</button>
        <div style="margin-top:14px;">
          <button type="submit" class="btn btn-primary btn-lg">Save users</button>
          <a href="/landing/" class="btn btn-ghost btn-lg">Finish later</a>
        </div>
      </form>

      @if($users->count())
        <div class="card" style="margin-top:18px;">
          <h3>Already added</h3>
          <ul class="list">
            @foreach($users as $u)
              <li>{{ $u->first_name }} {{ $u->last_name }} — {{ $u->email }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </main>

  <script>
    const rows = document.getElementById('rows');
    const addRowBtn = document.getElementById('addRow');

    function addRow(fn='', ln='', em='') {
      const idx = rows.children.length;
      const wrapper = document.createElement('div');
      wrapper.className = 'row';
      wrapper.innerHTML = `
        <input name="users[${idx}][first_name]" value="${fn}" required />
        <input name="users[${idx}][last_name]"  value="${ln}" required />
        <input name="users[${idx}][email]"      value="${em}" type="email" required />
      `;
      rows.appendChild(wrapper);
    }
    addRow();
    addRowBtn.addEventListener('click', () => addRow());
  </script>
</body>
</html>
