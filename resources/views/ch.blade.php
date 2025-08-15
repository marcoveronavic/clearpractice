<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Companies House Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      body { font-family: system-ui, Arial, sans-serif; margin: 24px; line-height: 1.4; color:#111827; }
      h1 { margin: 0 0 12px; }
      .row { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; }
      input[type="text"] { flex: 1; padding: 10px 12px; font-size: 16px; border: 1px solid #ccc; border-radius: 8px; }
      .status { font-size: 14px; opacity: .8; }
      ul { list-style: none; padding: 0; margin: 12px 0 0; }
      li { border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
      .item { display:block; padding: 12px; text-decoration: none; color: inherit; }
      .item:hover { background:#f8fafc; }
      .name { font-weight: 600; }
      .meta { font-size: 13px; opacity: .85; }
      .addr { margin-top: 4px; font-size: 13px; opacity: .9; }
      .muted { opacity: .7; }
      .error { color: #b00020; }
      .actions { display:flex; gap:8px; align-items:center; padding: 0 12px 12px 12px; }
      .btn { display:inline-block; padding:8px 12px; border:1px solid #111827; background:#111827; color:#fff; border-radius:10px; cursor:pointer; font-size:14px; }
      .btn[disabled] { opacity:.6; cursor:not-allowed; }
      .pill { font-size:12px; padding:2px 8px; border:1px solid #e5e7eb; border-radius:999px; margin-left:8px; }
      .ok { color:#065f46; }
    </style>
  </head>
  <body>
    <h1>Companies House Search</h1>

    <div class="row">
      <!-- only our list (no datalist) -->
      <input id="q" type="text" placeholder="Type a company name… e.g. tesco" autocomplete="off" autofocus>
      <span id="status" class="status muted"></span>
    </div>

    <ul id="results"></ul>

    <script>
      const q = document.getElementById('q');
      const list = document.getElementById('results');
      const statusEl = document.getElementById('status');

      const API_URL = "{{ route('ch.search') }}";
      const COMPANY_URL_TMPL = "{{ route('ch.company', ['number' => '___NUMBER___']) }}";
      const STORE_URL = "{{ route('companies.store') }}";
      const companyUrl = (num) => COMPANY_URL_TMPL.replace('___NUMBER___', encodeURIComponent(num || ''));

      const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      function escapeHtml(s) {
        return (s || '').toString().replace(/[&<>"']/g, c => ({
          '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c]));
      }

      function debounce(fn, ms = 300) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
      }

      function liTemplate(it) {
        const href = it.number ? companyUrl(it.number) : '#';
        return `
          <li data-number="${escapeHtml(it.number || '')}">
            <a class="item" href="${href}">
              <div class="name">${escapeHtml(it.name)} <span class="pill">Open →</span></div>
              <div class="meta">
                Number: <strong>${escapeHtml(it.number || '-')}</strong>
                &nbsp;•&nbsp; Status: ${escapeHtml(it.status || '-')}
                &nbsp;•&nbsp; Created: ${escapeHtml(it.date || '-')}
              </div>
              <div class="addr">${escapeHtml(it.address || '')}</div>
            </a>
            <div class="actions">
              <button class="btn save-btn">Add</button>
              <span class="save-msg muted"></span>
            </div>
          </li>
        `;
      }

      function render(items) {
        list.innerHTML = items.map(liTemplate).join('');
      }

      async function saveCompany(number, btn, msgEl) {
        if (!number) { msgEl.textContent = 'Missing company number'; msgEl.classList.remove('ok'); return; }
        try {
          btn.disabled = true;
          msgEl.textContent = 'Saving…';
          const res = await fetch(STORE_URL, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ number })
          });
          const data = await res.json();
          if (!res.ok || data.ok === false) {
            throw new Error((data && data.error) ? data.error : `HTTP ${res.status}`);
          }
          msgEl.textContent = data.action === 'created' ? 'Saved ✓' : 'Updated ✓';
          msgEl.classList.add('ok'); msgEl.classList.remove('muted');
        } catch (e) {
          msgEl.textContent = 'Error: ' + e.message;
          msgEl.classList.remove('ok'); msgEl.classList.remove('muted');
        } finally {
          btn.disabled = false;
        }
      }

      list.addEventListener('click', (e) => {
        const btn = e.target.closest('.save-btn');
        if (!btn) return;
        e.preventDefault(); e.stopPropagation(); // don’t trigger the link
        const li = btn.closest('li');
        const num = li.getAttribute('data-number');
        const msg = li.querySelector('.save-msg');
        saveCompany(num, btn, msg);
      });

      async function search(term) {
        term = term.trim();
        if (!term) { list.innerHTML = ''; statusEl.textContent = ''; statusEl.classList.remove('error'); return; }

        statusEl.textContent = 'Searching…';
        statusEl.classList.remove('error');

        try {
          const res = await fetch(API_URL + '?q=' + encodeURIComponent(term), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
          });

          const ct = res.headers.get('content-type') || '';
          if (!ct.includes('application/json')) {
            const txt = await res.text();
            throw new Error(`Non-JSON (HTTP ${res.status}). First chars: ${txt.slice(0,80)}`);
          }

          const data = await res.json();
          if (!res.ok || data.error) throw new Error(data.error || `HTTP ${res.status}`);

          const items = data.data || [];
          statusEl.textContent = `${items.length} result${items.length === 1 ? '' : 's'}`;
          render(items);
        } catch (e) {
          statusEl.textContent = 'Error: ' + e.message;
          statusEl.classList.add('error');
          list.innerHTML = '';
          console.error(e);
        }
      }

      const onType = debounce(() => search(q.value), 250);
      q.addEventListener('input', onType);
      q.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); search(q.value); }
      });
    </script>
  </body>
</html>
