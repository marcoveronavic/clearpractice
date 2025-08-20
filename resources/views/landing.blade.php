kk<!-- resources/views/landing.blade.php -->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>ClearPractice — Welcome</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{--bg:#f6f7f9;--text:#111827;--muted:#6b7280;--border:#d7dbe2;--primary:#111827}
    *{box-sizing:border-box}
    body{margin:0;background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
    .wrap{max-width:820px;margin:60px auto;padding:0 20px}
    .card{background:#fff;border:1px solid var(--border);border-radius:18px;padding:28px 26px;box-shadow:0 10px 30px rgba(0,0,0,.06)}
    h1{margin:0 0 12px;font-size:34px;letter-spacing:.2px}
    p{color:var(--muted);line-height:1.6;margin:8px 0 18px}
    .row{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}
    a.btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none}
    .primary{background:var(--primary);color:#fff;font-weight:600}
    .ghost{background:#fff;color:var(--text);border:1px solid var(--border)}
    .features{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:18px}
    .tile{background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px}
    .tile h3{margin:0 0 6px;font-size:16px}
    .foot{margin-top:18px;color:#6b7280;font-size:13px}
    a{color:#2563eb}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>ClearPractice</h1>
      <p>
        Keep companies, people and tasks in one fast, tidy workspace.
        Autofill from Companies House, assign tasks to teammates, and track deadlines easily.
      </p>

      <div class="row">
        <a class="btn primary" href="/login">Login</a>
        <a class="btn ghost" href="/register">Create an account</a>
      </div>

      <div class="features">
        <div class="tile">
          <h3>Companies House autofill</h3>
          <p>Search by number/name and populate company details in seconds.</p>
        </div>
        <div class="tile">
          <h3>Tasks & assignments</h3>
          <p>Create tasks and assign to your team with email notifications.</p>
        </div>
        <div class="tile">
          <h3>Deadlines overview</h3>
          <p>Accounts & CS deadlines in one clean list—never miss a date.</p>
        </div>
      </div>

      <div class="foot">
        Already inside? Go to <a href="/companies">Companies</a>.
      </div>
    </div>
  </div>
</body>
</html>

