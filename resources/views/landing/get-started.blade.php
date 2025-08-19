<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Get started — ClearCash</title>
  <link rel="stylesheet" href="/landing/styles.css" />
</head>
<body>
  <main class="section">
    <div class="container" style="max-width:760px;">
      <h1>Let’s get you set up</h1>
      <p class="lead">Tell us a few details and we’ll email you a confirmation link.</p>

      <form action="{{ route('lead.start') }}" method="post" style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:20px;">
        @csrf

        <div style="display:grid;gap:12px;grid-template-columns:1fr 1fr;">
          <div>
            <label>First name</label>
            <input name="first_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;">
          </div>
          <div>
            <label>Last name</label>
            <input name="last_name" type="text" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;">
          </div>
        </div>

        <div style="display:grid;gap:12px;grid-template-columns:1fr 1fr;margin-top:12px;">
          <div>
            <label>Email</label>
            <input name="email" type="email" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;">
          </div>
          <div>
            <label>Mobile phone</label>
            <input name="phone" type="tel" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;">
          </div>
        </div>

        <div style="margin-top:12px;">
          <label>Practice name</label>
          <input name="practice" type="text" required placeholder="e.g., Bright Numbers Ltd"
                 style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;">
        </div>

        <div style="margin-top:16px;display:flex;gap:10px;">
          <button type="submit" class="btn btn-primary">Continue</button>
          <a href="/landing/" class="btn btn-ghost">Back</a>
        </div>
      </form>
    </div>
  </main>
  <script src="/landing/app.js" defer></script>
</body>
</html>
