<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>@yield('title','App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Use Vite if built; otherwise fall back to inline base styles --}}
    @php $manifest = public_path('build/manifest.json'); @endphp
    @if (file_exists($manifest))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <style>
        :root { --border:#eaeaea; --muted:#666; --drawer-w:260px; --t:.25s; }
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,Arial,sans-serif;line-height:1.45;background:#fff;color:#111}
        a{color:inherit}
        /* Push drawer */
        .menu-btn{position:fixed;top:16px;left:16px;z-index:1201;border:1px solid var(--border);background:#fff;border-radius:10px;padding:8px 10px;cursor:pointer}
        body.drawer-open .menu-btn{left:calc(var(--drawer-w) + 16px)}
        .drawer{position:fixed;inset:0 auto 0 0;width:var(--drawer-w);background:#fff;border-right:1px solid var(--border);
                transform:translateX(-100%);transition:transform var(--t) ease;z-index:1202;display:flex;flex-direction:column}
        body.drawer-open .drawer{transform:translateX(0)}
        .drawer header{padding:14px 16px;border-bottom:1px solid var(--border);font-weight:700}
        .drawer nav a{display:block;padding:10px 14px;border-bottom:1px solid var(--border);text-decoration:none}
        .drawer nav a.active{background:#111;color:#fff}
        .page{padding:24px;transition:transform var(--t) ease}
        body.drawer-open .page{transform:translateX(var(--drawer-w))}
        h1{margin:0 0 12px}
      </style>
    @endif

    @stack('head')
  </head>
  <body>
    {{-- Drawer --}}
    <button class="menu-btn" id="menuBtn">☰</button>
    <div class="drawer" id="drawer">
      <header>Navigation</header>
      <nav>
        <a href="/companies"   data-path="/companies">Companies</a>
        <a href="/deadlines"   data-path="/deadlines">Deadlines</a>
        <a href="/ch"          data-path="/ch">Companies House Search</a>
        <a href="/tasks"       data-path="/tasks">Tasks</a>
        <a href="/individuals" data-path="/individuals">Individuals</a>
      </nav>
    </div>

    {{-- Main content --}}
    <div class="page">
      @yield('content')
    </div>

    {{-- Drawer script (works with or without Vite) --}}
    <script>
      (function () {
        const btn = document.getElementById('menuBtn');
        if (btn) btn.addEventListener('click', () => document.body.classList.toggle('drawer-open'));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') document.body.classList.remove('drawer-open'); });
        const p = location.pathname;
        document.querySelectorAll('.drawer nav a').forEach(a => {
          const want = a.dataset.path;
          if (p === want || (want !== '/' && p.startsWith(want))) a.classList.add('active');
        });
      })();
    </script>

    @stack('scripts')
  </body>
</html>
