<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create account — ClearPractice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
    .wrap{max-width:560px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;border:1px solid #e5e7eb}
    h1{margin:0 0 16px}
    form>div{margin:10px 0}
    input,button{font-size:16px;padding:10px 12px}
    input{width:100%;border:1px solid #d7dbe2;border-radius:8px;background:#fff}
    button{border:none;border-radius:8px;cursor:pointer;background:#111827;color:#fff}
    .error{color:#b91c1c;font-size:14px;margin-top:6px}
    a{color:#2563eb;text-decoration:none}
    .row{display:flex;gap:10px}
    .row>div{flex:1}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Create account</h1>
    <p>This will be the <strong>admin</strong> user of your practice.</p>

    @if ($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
      @csrf

      <div class="row">
        <div>
          <label>Name</label>
          <input type="text" name="name" value="{{ old('name') }}" placeholder="First name" required>
        </div>
        <div>
          <label>Surname</label>
          <input type="text" name="surname" value="{{ old('surname') }}" placeholder="Surname" required>
        </div>
      </div>

      <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
      </div>
      <div>
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <div>
        <label>Confirm password</label>
        <input type="password" name="password_confirmation" required>
      </div>

      <div>
        <button type="submit">Create account</button>
      </div>
    </form>

    <p style="margin-top:10px">Already have an account? <a href="{{ route('login') }}">Login</a>.</p>
  </div>
</body>
</html>
