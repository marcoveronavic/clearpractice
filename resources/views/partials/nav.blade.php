{{-- Shared drawer + menu button (includes "Users") --}}
<button class="menu-btn" id="menuBtn">☰</button>

<div class="drawer" id="drawer">
  <header>Navigation</header>
  <nav>
    <a href="/companies"    data-path="/companies">Companies</a>
    <a href="/deadlines"    data-path="/deadlines">Deadlines</a>
    <a href="/ch"           data-path="/ch">Companies House Search</a>
    <a href="/tasks"        data-path="/tasks">Tasks</a>
    <a href="/individuals"  data-path="/individuals">Individuals</a>
    <a href="/users"        data-path="/users">Users</a>
  </nav>
</div>

<script>
  // Toggle drawer + close on ESC
  (function () {
    const btn = document.getElementById('menuBtn');
    if (btn) btn.addEventListener('click', () => document.body.classList.toggle('drawer-open'));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') document.body.classList.remove('drawer-open'); });

    // Mark active link
    const p = location.pathname;
    document.querySelectorAll('.drawer nav a').forEach(a => {
      const w = a.dataset.path || a.getAttribute('href');
      if (p === w || (w !== '/' && p.startsWith(w))) a.classList.add('active');
    });
  }());
</script>
