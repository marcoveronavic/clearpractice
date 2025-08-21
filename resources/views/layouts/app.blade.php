<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>@yield('title','ClearPractice')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      :root{
        --border:#e5e7eb; --muted:#6b7280; --ink:#111827; --bg:#f9fafb;
        --sidebar-bg:#f8fafc; --sidebar-w:260px; --link:#1f2937; --link-active:#111827;
      }
      *{box-sizing:border-box}
      html,body{height:100%}
      body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);background:#fff}

      /* Sidebar (desktop: fixed; mobile: off-canvas) */
      .sidebar{
        position:fixed; inset:auto auto 0 0; top:0; width:var(--sidebar-w);
        background:var(--sidebar-bg); border-right:1px solid var(--border);
        padding:14px 10px; overflow:auto; z-index:70;
      }
      .brand{padding:10px 12px; font-weight:700; letter-spacing:.3px; color:var(--link-active)}
      .nav a{
        display:flex; align-items:center; gap:10px;
        padding:10px 12px; margin:4px 8px; border-radius:10px;
        color:var(--link); text-decoration:none;
      }
      .nav a:hover{background:#00000007}
      .nav a.active{background:#111827; color:#fff}

      /* Page wrapper */
      .page{margin-left:var(--sidebar-w); padding:24px}
      .page h1{margin:0 0 12px}

      /* Mobile behaviour */
      .menu-btn{position:fixed; top:12px; left:12px; z-index:80; display:none;
        border:1px solid var(--border); background:#fff; border-radius:10px; padding:8px 10px; cursor:pointer}
      .overlay{position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:60; opacity:0; pointer-events:none; transition:opacity .2s}
      .overlay.show{opacity:1; pointer-events:auto}
      @media (max-width: 980px){
        .menu-btn{display:block}
        .sidebar{transform:translateX(-110%); transition:transform .25s ease; box-shadow:0 10px 30px rgba(0,0,0,.2)}
        .sidebar.open{transform:translateX(0)}
        .page{margin-left:0; padding-top:60px}
      }

      /* Utilities */
      .card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px}
      .muted{color:var(--muted)}
      .btn{padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer}
      .btn.primary{background:#111827;color:#fff;border-color:#111827}
      .btn.danger{border-color:#ef4444;color:#b91c1c}
      table{width:100%;border-collapse:collapse}
      th,td{padding:10px 12px;border-top:1px solid var(--border);text-align:left;vertical-align:top}
      thead th{background:var(--bg);border-top:0}
      input,select{padding:8px 10px;border:1px solid var(--border);border-radius:8px;width:100%}
      .pill{display:inline-block;font-size:12px;border:1px solid var(--border);border-radius:999px;padding:2px 8px;margin:0 6px 6px 0;background:#fff}
      .flash{padding:10px 12px;border:1px solid var(--border);border-radius:10px;margin:10px 0}
      .ok{background:#ecfdf5} .err{background:#fef2f2}
    </style>
    @yield('head')
  </head>
  <body>
    <button id="menuBtn" class="menu-btn" aria-controls="sidebar" aria-expanded="false">☰</button>

    <aside id="sidebar" class="sidebar" aria-hidden="false">
      <div class="brand">clearpractice</div>
      <nav class="nav">
        @php
          $items = [
            ['label'=>'CH Search', 'href'=>route('ch.page'),         'active'=>request()->is('ch')],
            ['label'=>'Companies', 'href'=>route('companies.index'), 'active'=>request()->is('companies*')],
            ['label'=>'Clients',   'href'=>route('clients.index'),   'active'=>request()->is('clients*')],
            ['label'=>'Tasks',     'href'=>route('tasks.index'),     'active'=>request()->is('tasks*')],
            ['label'=>'Users',     'href'=>route('users.index'),     'active'=>request()->is('users*')],
            ['label'=>'Deadlines', 'href'=>route('deadlines.index'), 'active'=>request()->is('deadlines*')],
          ];
        @endphp
        @foreach ($items as $it)
          <a href="{{ $it['href'] }}" class="{{ $it['active'] ? 'active' : '' }}">{{ $it['label'] }}</a>
        @endforeach
      </nav>
    </aside>

    <div id="overlay" class="overlay"></div>

    <div class="page">
      @yield('content')
    </div>

    <script>
      (function(){
        const btn = document.getElementById('menuBtn');
        const sb  = document.getElementById('sidebar');
        const ov  = document.getElementById('overlay');
        const open = () => { sb.classList.add('open'); ov.classList.add('show'); btn.setAttribute('aria-expanded','true'); };
        const close= () => { sb.classList.remove('open'); ov.classList.remove('show'); btn.setAttribute('aria-expanded','false'); };
        btn.addEventListener('click', () => sb.classList.contains('open') ? close() : open());
        ov.addEventListener('click', close);
        document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });
      }());
    </script>
    @yield('scripts')
  </body>
</html>
