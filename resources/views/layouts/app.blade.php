<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name', 'clearpractice') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu; background:#fff; color:#111; }
        header { height:52px; display:flex; align-items:center; justify-content:space-between; padding:0 16px; border-bottom:1px solid #eee; }
        .wrap { display:flex; }
        .sidebar {
            width:180px; border-right:1px solid #eee; min-height:calc(100dvh - 52px);
            padding:12px 8px; display:flex; flex-direction:column; gap:6px;
        }
        .sidebar a { display:block; padding:8px 10px; border-radius:6px; text-decoration:none; color:#111; }
        .sidebar a.active, .sidebar a:hover { background:#f3f4f6; }
        main { flex:1; padding:22px; }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#fff; }
        .btn { border:1px solid #d1d5db; border-radius:6px; padding:6px 10px; background:#fff; cursor:pointer; }
        .btn.primary { background:#111827; color:#fff; border-color:#111827; }
        .pill { font-size:12px; padding:2px 8px; border-radius:999px; border:1px solid #d1d5db; }
        .muted { color:#6b7280; }
        .flash { padding:10px 12px; border-radius:8px; margin:8px 0; }
        .flash.ok { background:#ecfdf5; border:1px solid #10b98133; }
        .flash.err { background:#fef2f2; border:1px solid #ef444433; }
        .flash.info { background:#eff6ff; border:1px solid #3b82f633; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:10px; border-bottom:1px solid #f1f5f9; }

        /* user chip at bottom-left */
        .user-chip { margin-top:auto; padding:10px 8px; border-top:1px solid #eee; display:flex; gap:8px; align-items:center; }
        .avatar { width:28px; height:28px; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:600; color:#fff; background:#111827; }
        .user-info { min-width:0 }
        .user-name { font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-email { font-size:12px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
</head>
<body>
<header>
    <div>
        <strong>{{ config('app.name', 'clearpractice') }}</strong>
        @isset($practice)
            <span class="muted" style="margin-left:8px">— {{ $practice->name }}</span>
        @endisset
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn" type="submit">Logout</button>
    </form>
</header>

<div class="wrap">
    <aside class="sidebar">
        @isset($practice)
            {{-- Practice‑scoped nav --}}
            <a href="{{ route('practice.ch.page', $practice->slug) }}" class="{{ request()->routeIs('practice.ch.page') ? 'active' : '' }}">CH Search</a>
            <a href="{{ route('practice.companies.index', $practice->slug) }}" class="{{ request()->routeIs('practice.companies.*') ? 'active' : '' }}">Companies</a>
            <a href="{{ route('practice.clients.index', $practice->slug) }}" class="{{ request()->routeIs('practice.clients.*') ? 'active' : '' }}">Clients</a>
            <a href="{{ route('practice.tasks.index', $practice->slug) }}" class="{{ request()->routeIs('practice.tasks.*') ? 'active' : '' }}">Tasks</a>
            <a href="{{ route('practice.users.index', $practice->slug) }}" class="{{ request()->routeIs('practice.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('practice.deadlines.index', $practice->slug) }}" class="{{ request()->routeIs('practice.deadlines.*') ? 'active' : '' }}">Deadlines</a>

            {{-- Signed-in user chip (bottom) --}}
            @auth
                @php
                    $initials = strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1));
                @endphp
                <div class="user-chip" title="{{ auth()->user()->name ?? auth()->user()->email }}">
                    <div class="avatar">{{ $initials }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? '—' }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            @endauth
        @else
            {{-- Fallback nav when no practice is in context (e.g. landing/login) --}}
            <a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'active' : '' }}">Home</a>
            @auth
                <a href="{{ route('users.index') }}">Users</a>
                <a href="{{ route('companies.index') }}">Companies</a>
                <a href="{{ route('clients.index') }}">Clients</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('deadlines.index') }}">Deadlines</a>
            @endauth
        @endisset
    </aside>

    <main>
        @yield('content')
    </main>
</div>
</body>
</html>
