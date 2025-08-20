<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title','App')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
        .wrap{max-width:560px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.06)}
        h1{margin:0 0 16px}
        form > div{margin:10px 0}
        input,button{font-size:16px;padding:10px 12px}
        input{width:100%;border:1px solid #d7dbe2;border-radius:8px;background:#fff}
        button{border:none;border-radius:8px;cursor:pointer}
        .primary{background:#111827;color:#fff}
        .row{display:flex;gap:10px}
        .error{color:#b91c1c;font-size:14px;margin-top:6px}
        .topnav{display:flex;justify-content:space-between;margin-bottom:16px}
        a{color:#2563eb;text-decoration:none}
        .muted{color:#6b7280;font-size:14px;margin-top:8px}
        .success{background:#ecfdf5;color:#065f46;padding:10px;border-radius:8px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="topnav">
        <div><a href="/">Home</a></div>
        <div>
            @auth
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button class="primary">Logout</button>
                </form>
            @endauth
        </div>
    </div>
    @yield('content')
</div>
</body>
</html>
