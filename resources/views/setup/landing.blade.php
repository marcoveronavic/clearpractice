<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Welcome to ClearPractice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --bg:#f8fafc; --card:#fff; --muted:#6b7280; --border:#e5e7eb; --btn:#0f172a; --btnText:#fff; }
        html,body{height:100%}
        body{margin:0;font-family:ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:var(--bg); color:#0f172a;}
        .wrap{max-width:980px;margin:0 auto;padding:48px 24px 72px;}
        h1{font-weight:700; font-size:32px; margin:32px 0 8px}
        p.lead{color:var(--muted); margin:0 0 24px}
        .grid{display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:24px}
        .card{background:var(--card); border:1px solid var(--border); border-radius:14px; padding:20px 24px; box-shadow:0 1px 2px rgba(0,0,0,.04)}
        .card h2{font-size:18px;margin:0 0 6px}
        .card p{color:var(--muted); margin:0 0 12px; font-size:14px}
        .btn{display:inline-block; background:var(--btn); color:var(--btnText); text-decoration:none; padding:12px 16px; border-radius:10px; font-weight:600}
        .btn:focus,.btn:hover{opacity:.95}
        @media (max-width:720px){ .grid{grid-template-columns:1fr} }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Welcome to ClearPractice</h1>
    <p class="lead">Choose how you’d like to get started.</p>

    <div class="grid">
        <div class="card">
            <h2>Sign in</h2>
            <p>Already have an account? Continue to your workspace.</p>
            @if (Route::has('login'))
                <a class="btn" href="{{ route('login') }}">Go to sign in</a>
            @else
                <a class="btn" href="/login">Go to sign in</a>
            @endif
        </div>

        <div class="card">
            <h2>Create account</h2>
            <p>New here? Create an admin account for your practice.</p>
            @if (Route::has('register'))
                <a class="btn" href="{{ route('register') }}">Create account</a>
            @else
                <a class="btn" href="/register">Create account</a>
            @endif
        </div>
    </div>
</div>
</body>
</html>
