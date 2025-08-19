<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Get started — ClearCash</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/landing/styles.css" />
  <link rel="icon" href="/landing/clearcash.png" />
  <style>
    .form-wrap{ padding:48px 0 64px; }
    .form-card{ width:min(720px, 92%); margin:24px auto 0; background:#fff; border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:0 8px 30px rgba(2,6,23,.06); }
    .grid-2{ display:grid; gap:14px; grid-template-columns: 1fr 1fr; }
    .field{ display:flex; flex-direction:column; gap:6px; }
    .field label{ font-weight:600; color:#0f172a; }
    .field input{ background:#F1F7F7; border:1px solid var(--border); border-radius:10px; padding:12px 14px; font:inherit; outline:none; }
    .field input:focus{ border-color:#28C5F6; box-shadow:0 0 0 3px rgba(40,197,246,.18); }
    .actions{ display:flex; gap:12px; margin-top:14px; }
    .note{ margin-top:8px; color:#637b7d; font-size:.95rem; }
    @media (max-width:720px){ .grid-2{ grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="/landing/"><img class="logo" src="/landing/clearcash.png" alt="ClearCash logo" /></a>
      <nav class="nav" aria-label="Main">
        <ul id="navMenu" class="nav-menu">
          <li><a href="/landing/#features">Features</a></li>
          <li><a href="/landing/#pricing">Pricing</a></li>
          <li class="divider" aria-hidden="true"></li>
          <li><a class="btn btn-ghost" href="/login">Sign in</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <section class="section form-wrap">
      <div class="container">
        <h1>Let’s get you set up</h1>
        <p class="lead">Tell us a few details and we’ll email you a confirmation link.</p>

        <form class="form-card" action="{{ route('lead.start') }}" method="post">
          @csrf
          <div class="grid-2">
            <div class="field">
              <label for="first_name">First name</label>
              <input id="first_name" name="first_name" type="text" autocomplete="given-name" required />
            </div>
            <div class="field">
              <label for="last_name">Last name</label>
              <input id="last_name" name="last_name" type="text" autocomplete="family-name" required />
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" autocomplete="email" required />
            </div>
            <div class="field">
              <label for="phone">Mobile phone</label>
              <input id="phone" name="phone" type="tel" inputmode="tel" placeholder="+44 7…" />
            </div>
          </div>

          <div class="field">
            <label for="practice">Practice name</label>
            <input id="practice" name="practice" type="text" placeholder="e.g., Bright Numbers Ltd" required />
          </div>

          <div class="actions">
            <button type="submit" class="btn btn-primary btn-lg">Continue</button>
            <a href="/landing/" class="btn btn-ghost btn-lg">Back</a>
          </div>
          <p class="note">We’ll send a confirmation email to verify your address.</p>
        </form>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <p>© <span id="y"></span> ClearCash. All rights reserved.</p>
      <nav aria-label="Footer">
        <a href="/landing/#features">Features</a>
        <a href="/landing/#pricing">Pricing</a>
        <a href="/login">Sign in</a>
      </nav>
    </div>
  </footer>
  <script src="/landing/app.js" defer></script>
</body>
</html>
