<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      :root { --border:#eaeaea; --muted:#666; --drawer-w:260px; --t:.25s; }
      * { box-sizing:border-box; }
      body { margin:0; font-family:system-ui, Arial, sans-serif; line-height:1.45; }
      a { color:inherit; text-decoration:none }

      /* Drawer (push) */
      .menu-btn{position:fixed;top:16px;left:16px;z-index:1201;border:1px solid var(--border);background:#fff;border-radius:10px;padding:8px 10px;cursor:pointer}
      body.drawer-open .menu-btn{left:calc(var(--drawer-w) + 16px)}
      .drawer{position:fixed;inset:0 auto 0 0;width:var(--drawer-w);background:#fff;border-right:1px solid var(--border);transform:translateX(-100%);transition:transform var(--t) ease;z-index:1202;display:flex;flex-direction:column}
      body.drawer-open .drawer{transform:translateX(0)}
      .drawer header{padding:14px 16px;border-bottom:1px solid var(--border);font-weight:700}
      .drawer nav a{display:block;padding:10px 14px;border-bottom:1px solid var(--border)}
      .drawer nav a.active{background:#111;color:#fff}
      .page{padding:24px;transition:transform var(--t) ease}
      body.drawer-open .page{transform:translateX(var(--drawer-w))}

      h1{margin:0 0 12px}
      .bar{display:flex;gap:8px;align-items:center;margin-bottom:12px}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:8px 10px;background:#fff;color:inherit;cursor:pointer}
      .button.primary{background:#111;color:#fff;border-color:#111}
      .muted{color:var(--muted)}

      .card{border:1px solid var(--border);border-radius:14px;padding:16px;background:#fff}
      .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
      .field{display:flex;flex-direction:column;gap:6px}
      .field label{font-size:13px;color:var(--muted)}
      input{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font-size:15px;width:100%}

      .table{width:100%;border-collapse:collapse}
      .table th,.table td{padding:8px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
      .table th{color:var(--muted);font-weight:600}
      .small{font-size:12px}
      .ok{color:#1b5e20}
      .error{color:#b00020}
    </style>
  </head>
  <body>
    @include('partials.nav')

    <div class="page">
      <div class="bar">
        <h1 style="flex:1">Users</h1>
        <button class="button" id="toggleAdd">+ Add user</button>
      </div>

      @if (session('status'))
        <p class="ok">{{ session('status') }}</p>
      @endif
      @if ($errors->any())
        <div class="error" style="margin:8px 0">
          @foreach ($errors->all() as $e) • {{ $e }}<br>@endforeach
        </div>
      @endif

      <div id="addCard" class="card" style="display:block;margin-bottom:16px">
        <form method="POST" action="/users" autocomplete="off">
          @csrf
          <div class="grid">
            <div class="field">
              <label for="first_name">First name</label>
              <input id="first_name" name="first_name" type="text" required value="{{ old('first_name') }}">
            </div>
            <div class="field">
              <label for="last_name">Surname</label>
              <input id="last_name" name="last_name" type="text" required value="{{ old('last_name') }}">
            </div>
            <div class="field" style="grid-column:1/-1">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" required value="{{ old('email') }}">
            </div>
          </div>
          <div class="small muted" style="margin-top:6px">
            A secure temporary password will be generated automatically.
          </div>
          <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
            <button type="button" class="button" id="cancelAdd">Cancel</button>
            <button type="submit" class="button primary">Create user</button>
          </div>
        </form>
      </div>

      @if ($users->isEmpty())
        <p class="muted">No users yet.</p>
      @else
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $u)
              <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td class="small muted">{{ optional($u->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <script>
      const addCard=document.getElementById('addCard');
      document.getElementById('toggleAdd').addEventListener('click',()=>{addCard.style.display=addCard.style.display==='none'?'block':'none'});
      document.getElementById('cancelAdd').addEventListener('click',()=>{addCard.style.display='none'});
    </script>
  </body>
</html>
