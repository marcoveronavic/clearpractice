<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Welcome — ClearPractice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{ --border:#e5e7eb; --ink:#111827; --muted:#6b7280; --primary:#111827 }
        *{ box-sizing:border-box } body{ margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif }
        .wrap{ max-width:880px; margin:60px auto; padding:0 18px }
        .grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; margin-top:14px }
        .card{ display:flex; flex-direction:column; gap:10px; padding:18px; border:1px solid var(--border); border-radius:12px; text-decoration:none; color:inherit }
        .card:hover{ box-shadow:0 10px 18px rgba(0,0,0,.06) } .title{ font-weight:700 } .desc{ color:#6b7280 }
        .btn{ margin-top:auto; padding:10px 12px; border-radius:10px; border:1px solid var(--border) }
        .btn.primary{ background:var(--primary); color:#fff; border-color:var(--primary) }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Welcome to ClearPractice</h1>
    <p style="color:#6b7280">Choose how you’d like to get started.</p>

    <div class="grid">
        <a class="card" href="{{ route('login') }}">
            <div class="title">Sign in</div>
            <div class="desc">Already have an account? Continue to your workspace.</div>
            <span class="btn">Go to sign in</span>
        </a>

        <a class="card" href="{{ route('register') }}">
            <div class="title">Create account</div>
            <div class="desc">New here? Create an admin account for your practice.</div>
            <span class="btn primary">Create account</span>
        </a>
    </div>
</div>
</body>
</html>
