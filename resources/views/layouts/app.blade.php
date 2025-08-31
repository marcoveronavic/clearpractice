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

        /* Sidebar (fixed) */
        .sidebar{
            position:fixed; inset:auto auto 0 0; top:0; width:var(--sidebar-w);
            background:var(--sidebar-bg); border-right:1px solid var(--border);
            padding:14px 10px 0; overflow:auto; z-index:70;
            display:flex; flex-direction:column; min-height:100vh;
        }
        .brand{padding:10px 12px; font-weight:700; letter-spacing:.3px; color:var(--link-active)}
        .nav{padding-bottom:12px}
        .nav a{
            display:flex; align-items:center; gap:10px;
            padding:10px 12px; margin:4px 8px; border-radius:10px;
            color:var(--link); text-decoration:none;
        }
        .nav a:hover{background:#00000007}
        .nav a.active{background:#111827; color:#fff}

        /* USER FOOTER */
        .userfoot{
            position:sticky; bottom:0; margin-top:auto;
            border-top:1px solid var(--border); background:#fff;
        }
        .userfoot a{
            display:flex; gap:10px; align-items:center;
            padding:10px 12px; text-decoration:none; color:inherit;
        }
        .userfoot .avatar{
            width:30px; height:30px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            border:1px solid var(--border);
            font-weight:600;
        }
        .userfoot .meta{min-width:0}
        .userfoot .name{font-weight:600; line-height:1.2}
        .userfoot .email{color:var(--muted); font-size:12px; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis}

        /* Page wrapper */
        .page{margin-left:var(--sidebar-w); padding:24px}
        .page h1{margin:0 0 12px}

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

<aside id="sidebar" class="sidebar" aria-hidden="false">
    <div class="brand">clearpractice</div>

    <nav class="nav">
        @php
            // Use classic global route names; if missing, fall back to '#'
            $safe = function ($name) {
                return \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';
            };

            $items = [
              ['label'=>'CH Search', 'href'=>$safe('ch.page'),         'active'=>request()->is('ch')],
              ['label'=>'Companies', 'href'=>$safe('companies.index'), 'active'=>request()->is('companies*')],
              ['label'=>'Clients',   'href'=>$safe('clients.index'),   'active'=>request()->is('clients*')],
              ['label'=>'Tasks',     'href'=>$safe('tasks.index'),     'active'=>request()->is('tasks*')],
              ['label'=>'Users',     'href'=>$safe('users.index'),     'active'=>request()->is('users*')],
              ['label'=>'Deadlines', 'href'=>$safe('deadlines.index'), 'active'=>request()->is('deadlines*')],
            ];
        @endphp
        @foreach ($items as $it)
            <a href="{{ $it['href'] }}" class="{{ $it['active'] ? 'active' : '' }}">{{ $it['label'] }}</a>
        @endforeach
    </nav>

    {{-- USER FOOTER --}}
    @if (auth()->check())
        @php
            $u = auth()->user();
            $initial = strtoupper(mb_substr($u->name ?? 'U', 0, 1));
        @endphp
        <div class="userfoot">
            <a href="{{ route('account') }}" title="Account">
                <div class="avatar">{{ $initial }}</div>
                <div class="meta">
                    <div class="name">{{ $u->name }}</div>
                    <div class="email">{{ $u->email }}</div>
                </div>
            </a>
        </div>
    @endif
</aside>

<div class="page">
    @yield('content')
</div>

@yield('scripts')
</body>
</html>
