{{-- resources/views/ch.blade.php --}}
@extends('layouts.app')

@section('content')
  <div class="ch-search">
    <h1 style="margin-bottom:10px;">Companies House Search</h1>

    <div class="field" style="position:relative;max-width:900px;">
      <input id="ch-q" type="text" placeholder="Type a company name… e.g. tesco"
             autocomplete="off"
             style="width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;">

      <!-- dropdown -->
      <div id="ch-results"
           style="position:absolute;top:44px;left:0;right:0;z-index:30;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.08);display:none;max-height:420px;overflow:auto;">
      </div>
    </div>

    <p style="color:#6b7280;margin-top:10px;">
      Results come directly from Companies House. Click a row to log the item (you can wire it to any action later).
    </p>
  </div>

  <style>
    .ch-item{padding:10px 12px;border-top:1px solid #f3f4f6;cursor:pointer}
    .ch-item:first-child{border-top:0;border-top-left-radius:10px;border-top-right-radius:10px}
    .ch-item:last-child{border-bottom-left-radius:10px;border-bottom-right-radius:10px}
    .ch-item:hover{background:#f9fafb}
    .ch-title{font-weight:600}
    .ch-sub{color:#6b7280;font-size:13px;margin-top:2px}
    .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;margin-left:6px;background:#eef2ff;color:#3730a3}
  </style>

  <script>
    (() => {
      const q  = document.getElementById('ch-q');
      const box = document.getElementById('ch-results');

      let aborter = null;
      let hideTimer = null;

      function show() { box.style.display = 'block'; }
      function hide() { box.style.display = 'none'; box.innerHTML = ''; }

      function debounce(fn, ms){
        let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args), ms); };
      }

      function row(item){
        const el = document.createElement('div');
        el.className = 'ch-item';
        el.innerHTML = `
          <div class="ch-title">
            ${escapeHtml(item.name || '')}
            <span class="pill">${escapeHtml(item.number || '')}</span>
          </div>
          <div class="ch-sub">
            ${escapeHtml(item.address || '')}
            ${item.status ? ' · ' + escapeHtml(item.status) : ''}
            ${item.date ? ' · created ' + escapeHtml(item.date) : ''}
          </div>
        `;
        el.addEventListener('click', () => {
          console.log('Selected company:', item);
          // TODO: hook this to your "Add to Companies" flow if you want.
          hide();
        });
        return el;
      }

      function escapeHtml(s){
        return String(s ?? '').replace(/[&<>"']/g, m => ({
          '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
      }

      const runSearch = debounce(async function(){
        const term = q.value.trim();
        if (term.length < 2){ hide(); return; }

        if (aborter) aborter.abort();
        aborter = new AbortController();

        try{
          const r = await fetch(`/api/ch?q=${encodeURIComponent(term)}`, { signal: aborter.signal });
          const j = await r.json();
          const items = (j && j.data) ? j.data : [];

          box.innerHTML = '';
          if (!items.length){ hide(); return; }

          items.forEach(it => box.appendChild(row(it)));
          show();
        }catch(err){
          if (err.name !== 'AbortError') console.error(err);
          hide();
        }
      }, 250);

      q.addEventListener('input', runSearch);
      q.addEventListener('focus', () => {
        if (box.children.length) show();
      });

      // close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (e.target === q || box.contains(e.target)) return;
        hide();
      });

      // small UX: delay hide on blur to allow clicking inside the dropdown
      q.addEventListener('blur', () => {
        hideTimer = setTimeout(hide, 150);
      });
      box.addEventListener('mousedown', () => { clearTimeout(hideTimer); });
    })();
  </script>
@endsection
