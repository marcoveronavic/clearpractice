<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Step 2 — Practice name — ClearPractice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:#f6f7f9}
    .wrap{max-width:640px;margin:40px auto;padding:24px;background:#fff;border-radius:12px;border:1px solid #e5e7eb}
    h1{margin:0 0 16px}
    form>div{margin:10px 0}
    input,button{font-size:16px;padding:10px 12px}
    input{width:100%;border:1px solid #d7dbe2;border-radius:8px;background:#fff}
    button{border:none;border-radius:8px;cursor:pointer;background:#111827;color:#fff}
    .note{color:#6b7280}
    .err{color:#b91c1c;font-size:14px;margin-top:6px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Step 2 — Name your practice</h1>
    <p class="note">You’re signed in as <strong>{{ auth()->user()->name }}</strong> (admin).</p>

    @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('setup.practice.save') }}">
      @csrf
      <div>
        <label>Practice name</label>
        <input type="text" name="practice_name" placeholder="e.g. Fidcorp Ltd"
               value="{{ old('practice_name') }}" required>
      </div>
      <div>
        <button type="submit">Continue</button>
      </div>
    </form>
  </div>
</body>
</html>
