<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Companies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      :root { --border:#eaeaea; --muted:#666; --drawer-w:260px; --t:.25s; }
      * { box-sizing:border-box; }
      body { margin:0; font-family:system-ui, Arial, sans-serif; line-height:1.45; }
      a { color:inherit; }

      .menu-btn{position:fixed;top:16px;left:16px;z-index:1201;border:1px solid var(--border);background:#fff;border-radius:10px;padding:8px 10px;cursor:pointer}
      body.drawer-open .menu-btn{left:calc(var(--drawer-w) + 16px)}
      .drawer{position:fixed;inset:0 auto 0 0;width:var(--drawer-w);background:#fff;border-right:1px solid var(--border);transform:translateX(-100%);transition:transform var(--t) ease;z-index:1202;display:flex;flex-direction:column}
      body.drawer-open .drawer{transform:translateX(0)}
      .drawer header{padding:14px 16px;border-bottom:1px solid var(--border);font-weight:700}
      .drawer nav a{display:block;padding:10px 14px;border-bottom:1px solid var(--border);text-decoration:none}
      .drawer nav a.active{background:#111;color:#fff}

      .page{padding:24px;transition:transform var(--t) ease}
      body.drawer-open .page{transform:translateX(var(--drawer-w))}

      h1{margin:0 0 12px}
      .actions{margin:12px 0 16px;display:flex;gap:8px;flex-wrap:wrap}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:8px 10px;text-decoration:none;color:inherit;background:#fff}
      .button.primary{background:#111;color:#fff;border-color:#111}
      .muted{color:var(--muted)}
      .table{width:100%;border-collapse:collapse}
      .table th,.table td{text-align:left;padding:8px 10px;vertical-align:top}
      .table th{color:var(--muted);font-weight:600;border-bottom:1px solid var(--border)}
      .table tr+tr td{border-top:1px solid var(--border)}
      code.inline{background:#f7f7f7;border:1px solid var(--border);border-radius:6px;padding:1px 6px}
      .small{font-size:12px}
      .name-link{text-decoration:underline}

      /* Edit modal */
      .modal{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;padding:24px;z-index:2000}
      .modal.show{display:flex}
      .dialog{background:#fff;border-radius:14px;width:100%;max-width:720px;max-height:85vh;overflow:auto;box-shadow:0 10px 30px rgba(0,0,0,.25)}
      .dialog header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border);position:sticky;top:0;background:#fff}
      .dialog h2{margin:0;font-size:18px}
      .close{border:1px solid var(--border);background:#fff;border-radius:8px;padding:6px 10px;cursor:pointer}
      .content{padding:16px}
      .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
      .field{display:flex;flex-direction:column;gap:6px}
      .field label{font-size:13px;color:var(--muted)}
      .field input{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font-size:15px}
      .kv{margin:6px 0}
      .kv b{display:inline-block;width:170px;color:var(--muted);font-weight:600}
      .bar{display:flex;justify-content:flex-end;gap:8px;padding-top:12px;margin-top:8px;border-top:1px dashed var(--border)}
      .error{color:#b00020;font-weight:600}
    </style>
  </head>
  <body>
    <button class="menu-btn" id="menuBtn">☰</button>
    <div class="drawer" id="drawer">
      <header>Navigation</header>
      <nav>
        <a href="/companies" data-path="/companies">Companies</a>
        <a href="/deadlines" data-path="/deadlines">Deadlines</a>
        <a href="/ch" data-path="/ch">Companies House Search</a>
        <a href="/tasks" data-path="/tasks">Tasks</a>
        <a href="/individuals" data-path="/individuals">Individuals</a>
      </nav>
    </div>

    <div class="page">
      <h1>Companies</h1>

      <div class="actions">
        <a class="button" href="/ch">＋ Add more</a>
        <a class="button" href="/deadlines">Deadlines</a>
      </div>

      @if (empty($companies))
        <p class="muted">No companies saved yet. Use <a href="/ch">Companies House Search</a> to add one.</p>
        <a href="/tasks" data-path="/tasks">Tasks</a>
        <a href="/individuals" data-path="/individuals">Individuals</a>
      @else
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Number</th>
              <th>Status</th>
              <th>Created</th>
              <th>Address</th>
              <th>Saved</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($companies as $c)
              @php $num = $c['number'] ?? ''; @endphp
              <tr data-row-number="{{ $num }}">
                <td><a class="name-link" href="/companies/{{ urlencode($num) }}">{{ $c['name'] ?? '' }}</a></td>
                <td><code class="inline">{{ $num }}</code></td>
                <td>{{ $c['status'] ?? '-' }}</td>
                <td>{{ $c['created'] ?? '-' }}</td>
                <td>{{ $c['address'] ?? '-' }}</td>
                <td class="muted small">{{ $c['saved_at'] ?? '' }}</td>
                <td style="white-space:nowrap;">
                  <button class="button editBtn" data-number="{{ $num }}">Edit</button>
                  <a class="button" href="https://find-and-update.company-information.service.gov.uk/company/{{ urlencode($num) }}" target="_blank" rel="noopener noreferrer">Open on CH ↗</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" aria-hidden="true">
      <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="dlg-title">
        <header>
          <h2 id="dlg-title">Edit company</h2>
          <button class="close" id="closeEdit" aria-label="Close">✕</button>
        </header>
        <div class="content">
          <div id="companyInfo" class="kv muted">Loading…</div>

          <div class="grid" style="margin-top:10px;">
            <div class="field"><label for="vat">VAT registration number</label><input id="vat" type="text" placeholder="e.g. GB123456789"></div>
            <div class="field"><label for="auth">Companies House authentication code</label><input id="auth" type="text" placeholder="6 characters"></div>
            <div class="field"><label for="utr">Corporation Tax UTR</label><input id="utr" type="text" placeholder="10 digits"></div>
            <div class="field"><label for="email">Email</label><input id="email" type="email" placeholder="name@company.com"></div>
            <div class="field"><label for="tel">Telephone</label><input id="tel" type="text" placeholder="+44 ..."></div>
          </div>

          <div id="editError" class="error" style="display:none;margin-top:8px;"></div>

          <div class="bar">
            <button class="button" id="cancelBtn" type="button">Cancel</button>
            <button class="button primary" id="saveBtn" type="button">Save</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Drawer push
      document.getElementById('menuBtn').addEventListener('click',()=>{document.body.classList.toggle('drawer-open')});
      document.addEventListener('keydown',e=>{if(e.key==='Escape')document.body.classList.remove('drawer-open')});
      const p=location.pathname;
      document.querySelectorAll('.drawer nav a').forEach(a=>{const w=a.dataset.path; if(p===w||(w!=='/'&&p.startsWith(w)))a.classList.add('active')});

      // Edit modal
      const modal=document.getElementById('editModal'), closeEdit=document.getElementById('closeEdit'), cancelBtn=document.getElementById('cancelBtn'), saveBtn=document.getElementById('saveBtn');
      const infoBox=document.getElementById('companyInfo'), errBox=document.getElementById('editError');
      const vatInput=document.getElementById('vat'), authInput=document.getElementById('auth'), utrInput=document.getElementById('utr'), emailInput=document.getElementById('email'), telInput=document.getElementById('tel');
      let currentNumber=null;
      const escapeHtml=s=>(s||'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
      const showModal=()=>{modal.classList.add('show');modal.setAttribute('aria-hidden','false')};
      const hideModal=()=>{modal.classList.remove('show');modal.setAttribute('aria-hidden','true')};

      async function loadCompany(number){
        infoBox.textContent='Loading…'; errBox.style.display='none';
        try{
          const res=await fetch(`/api/companies/${encodeURIComponent(number)}`); const j=await res.json();
          if(!res.ok||j.error) throw new Error(j.error||`HTTP ${res.status}`);
          const d=j.data||{};
          infoBox.innerHTML=`<div class="kv"><b>Name</b> ${escapeHtml(d.name||'')}</div><div class="kv"><b>Number</b> <code class="inline">${escapeHtml(number)}</code></div><div class="kv"><b>Status</b> ${escapeHtml(d.status||'-')}</div><div class="kv"><b>Created</b> ${escapeHtml(d.created||'-')}</div><div class="kv"><b>Address</b> ${escapeHtml(d.address||'-')}</div>`;
          vatInput.value=d.vat_number||''; authInput.value=d.authentication_code||''; utrInput.value=d.utr||''; emailInput.value=d.email||''; telInput.value=d.telephone||'';
        }catch(e){ infoBox.textContent=''; errBox.textContent='Error: '+(e.message||'Unknown error'); errBox.style.display='block'; }
      }
      async function saveCompany(){
        if(!currentNumber) return;
        errBox.style.display='none'; saveBtn.disabled=true; saveBtn.textContent='Saving…';
        try{
          const csrf=document.querySelector('meta[name="csrf-token"]').content;
          const res=await fetch(`/api/companies/${encodeURIComponent(currentNumber)}`,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({vat_number:vatInput.value.trim()||null,authentication_code:authInput.value.trim()||null,utr:utrInput.value.trim()||null,email:emailInput.value.trim()||null,telephone:telInput.value.trim()||null})});
          const j=await res.json().catch(()=>({})); if(!res.ok||j.saved===false) throw new Error(j.message||`HTTP ${res.status}`);
          window.location.reload();
        }catch(e){ errBox.textContent='Save failed: '+(e.message||'Unknown error'); errBox.style.display='block'; saveBtn.disabled=false; saveBtn.textContent='Save'; }
      }

      document.addEventListener('click',e=>{const btn=e.target.closest('.editBtn'); if(!btn) return; e.preventDefault(); currentNumber=btn.dataset.number; showModal(); loadCompany(currentNumber);});
      closeEdit.addEventListener('click',hideModal);
      cancelBtn.addEventListener('click',hideModal);
      modal.addEventListener('click',e=>{if(e.target===modal)hideModal()});
      document.addEventListener('keydown',e=>{if(e.key==='Escape'&&modal.classList.contains('show'))hideModal()});
      saveBtn.addEventListener('click',saveCompany);
    </script>
  </body>
</html>
