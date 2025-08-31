<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Verify your email — ClearPractice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
        .wrap{max-width:560px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;border:1px solid #e5e7eb}
        h1{margin:0 0 12px}
        p{margin:8px 0}
        form{margin-top:12px}
        button{border:none;border-radius:8px;cursor:pointer;background:#111827;color:#fff;padding:10px 12px}
        .info{color:#065f46;background:#ecfdf5;padding:8px 10px;border-radius:8px;margin:8px 0}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Verify your email</h1>

    @if (session('status'))
        <div class="info">{{ session('status') }}</div>
    @endif

    <p>We’ve sent a verification link to your email address. Please click it to continue.</p>
    <p>If you didn’t receive the email, you can request another:</p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend verification email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
        @csrf
        <button type="submit">Log out</button>
    </form>
</div>
</body>
</html>

