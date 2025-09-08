<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset password — ClearPractice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
        .wrap{max-width:560px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;border:1px solid #e5e7eb}
        form>div{margin:10px 0}
        input,button{font-size:16px;padding:10px 12px}
        input{width:100%;border:1px solid #d7dbe2;border-radius:8px;background:#fff}
        button{border:none;border-radius:8px;cursor:pointer;background:#111827;color:#fff}
        .error{color:#b91c1c;font-size:14px;margin-top:6px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>Choose a new password</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div>
            <label>New password</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirm password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <div>
            <button type="submit">Update password</button>
        </div>
    </form>

    <p style="margin-top:10px"><a href="{{ route('login') }}">Back to sign in</a></p>
</div>
</body>
</html>

