<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>ClearPractice — Landing</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--bg:#f6f7f9;--card:#fff;--muted:#6b7280;--border:#e5e7eb;--ink:#111827;--ink2:#374151}
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:0;background:var(--bg);color:var(--ink)}
    .wrap{max-width:860px;margin:60px auto;padding:0 16px}
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px 28px;box-shadow:0 6px 18px rgba(17,24,39,.05)}
    h1{font-size:32px;margin:0 0 14px}
    p{margin:0 0 12px}
    .muted{color:var(--muted)}
    .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-top:16px}
    .tile{border:1px solid var(--border);border-radius:12px;padding:14px}
    .tile h3{margin:0 0 8px;font-size:16px}
    .actions{display:flex;gap:10px;margin:12px 0 6px;align-items:center}
    a.btn, button.btn{display:inline-block;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600}
    .btn-primary{background:#111827;color:#fff;border:none}
    .btn-muted{background:#f3f4f6;border:1px solid #e5e7eb;color:#111827}
    .small{font-size:14px;color:var(--muted);margin-top:10px}
    .small a{color:#2563eb;text-decoration:none}
    form.inline{display:inline}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>ClearPractice</h1>
      <p class="muted">Keep companies, people and tasks in one fast, tidy workspace. Autofill from Companies House, assign tasks to teammates, and track deadlines easily.</p>

      @php
        // If you’re logged in, we send you to Step 2 directly.
        $registerUrl = auth()->check() ? route('setup.practice') : route('register');
      @endphp

      <div class="actions">
        <a class="btn btn-muted" href="{{ route('login') }}">Login</a>
        <a class="btn btn-primary" href="{{ $registerUrl }}">Create an account</a>

        @auth
          <form class="inline" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-muted" type="submit">Logout</button>
          </form>
        @endauth
      </div>

      <div class="grid">
        <div class="tile">
          <h3>Companies House autofill</h3>
          <p class="muted">Search by number/name and populate company details in seconds.</p>
        </div>
        <div class="tile">
          <h3>Tasks &amp; assignments</h3>
          <p class="muted">Create tasks and assign to your team with email notifications.</p>
        </div>
        <div class="tile">
          <h3>Deadlines overview</h3>
          <p class="muted">Accounts &amp; CS deadlines in one clean list—never miss a date.</p>
        </div>
      </div>

      <p class="small">
        Already inside? Go to
        <a href="{{ request()->is('demo/*') ? '/demo/companies' : '/companies' }}">Companies</a>.
      </p>
    </div>
  </div>
</body>
</html>
