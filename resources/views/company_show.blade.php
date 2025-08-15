<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Company Details</title>
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
      .muted{color:var(--muted)}
      .wrap{display:grid;grid-template-columns:1.4fr 1fr;gap:24px}
      .card{border:1px solid var(--border);border-radius:14px;padding:16px}
      .card h2{margin:0 0 10px;font-size:18px}
      .kv{margin:6px 0}
      .kv b{display:inline-block;width:220px;color:var(--muted);font-weight:600}
      .section{margin-top:14px;padding-top:10px;border-top:1px dashed var(--border)}
      .section h3{margin:0 0 6px;font-size:16px}
      .table{width:100%;border-collapse:collapse}
      .table th,.table td{text-align:left;padding:6px 8px;vertical-align:top}
      .table th{color:var(--muted);font-weight:600;border-bottom:1px solid var(--border)}
      .table tr+tr td{border-top:1px solid var(--border)}
      .pill{display:inline-block;border:1px solid var(--border);border-radius:999px;padding:2px 8px;font-size:12px;margin:2px 4px 0 0}
      .bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:16px}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:8px 10px;text-decoration:none;color:inherit;background:#fff;cursor:pointer}
      .button.primary{background:#111;color:#fff;border-color:#111}
      .button.small{padding:6px 8px;font-size:12px;border-radius:8px}
      .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
      .field{display:flex;flex-direction:column;gap:6px}
      .field label{font-size:13px;color:var(--muted)}
      .field input,.field select{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font-size:15px}
      code.inline{background:#f7f7f7;border:1px solid var(--border);border-radius:6px;padding:1px 6px}
      .error{color:#b00020;font-weight:600}
      .ok{color:#1b5e20}
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
      @php $num = $company['number'] ?? ''; $name = $company['name'] ?? ''; @endphp

      <div class="bar">
        <a class="button" href="/companies">← Back to Companies</a>
        <a class="button" href="https://find-and-update.company-information.service.gov.uk/company/{{ urlencode($num) }}" target="_blank" rel="noopener noreferrer">Open on CH ↗</a>
      </div>

      <h1>{{ $name ?: 'Company' }} <span class="muted">(<code class="inline">{{ $num }}</code>)</span></h1>

      <div class="wrap" style="margin-top:12px;">
        <!-- LEFT: live CH data -->
        <div class="card" id="chCard">
          <h2>Companies House (live)</h2>
          <div id="chContent" class="muted">Loading…</div>
        </div>

        <!-- RIGHT: your records -->
        <div class="card">
          <h2>Your records</h2>
          <div class="kv"><b>Name</b> {{ $company['name'] ?? '-' }}</div>
          <div class="kv"><b>Number</b> <code class="inline">{{ $num }}</code></div>

          <div class="grid" style="margin-top:10px;">
            <div class="field"><label for="vat">VAT registration number</label><input id="vat" type="text" placeholder="e.g. GB123456789" value="{{ $company['vat_number'] ?? '' }}"></div>
            <div class="field"><label for="auth">Companies House authentication code</label><input id="auth" type="text" placeholder="6 characters" value="{{ $company['authentication_code'] ?? '' }}"></div>
            <div class="field"><label for="utr">Corporation Tax UTR</label><input id="utr" type="text" placeholder="10 digits" value="{{ $company['utr'] ?? '' }}"></div>
            <div class="field"><label for="email">Email</label><input id="email" type="email" placeholder="name@company.com" value="{{ $company['email'] ?? '' }}"></div>
            <div class="field"><label for="tel">Telephone</label><input id="tel" type="text" placeholder="+44 ..." value="{{ $company['telephone'] ?? '' }}"></div>
            <div class="field">
              <label for="vat_period">VAT period</label>
              <select id="vat_period">
                <option value="" @if(empty($company['vat_period'])) selected @endif>— Select —</option>
                <option value="monthly" @if(($company['vat_period'] ?? '') === 'monthly') selected @endif>Monthly</option>
                <option value="quarterly" @if(($company['vat_period'] ?? '') === 'quarterly') selected @endif>Quarterly</option>
              </select>
            </div>
            <div class="field">
              <label for="vat_quarter">VAT quarter end</label>
              <select id="vat_quarter">
                <option value="" @if(empty($company['vat_quarter_group'])) selected @endif>— Select —</option>
                <option value="Jan/Apr/Jul/Oct" @if(($company['vat_quarter_group'] ?? '') === 'Jan/Apr/Jul/Oct') selected @endif>Jan / Apr / Jul / Oct</option>
                <option value="Feb/May/Aug/Nov" @if(($company['vat_quarter_group'] ?? '') === 'Feb/May/Aug/Nov') selected @endif>Feb / May / Aug / Nov</option>
                <option value="Mar/Jun/Sep/Dec" @if(($company['vat_quarter_group'] ?? '') === 'Mar/Jun/Sep/Dec') selected @endif>Mar / Jun / Sep / Dec</option>
              </select>
            </div>
          </div>

          <div id="errBox" class="error" style="display:none;margin-top:8px;"></div>
          <div class="bar" style="justify-content:flex-end;margin-top:8px;">
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

      const number=@json($num), chContent=document.getElementById('chContent');
      const saveBtn=document.getElementById('saveBtn'), errBox=document.getElementById('errBox');
      const vatInput=document.getElementById('vat'), authInput=document.getElementById('auth'), utrInput=document.getElementById('utr'), emailInput=document.getElementById('email'), telInput=document.getElementById('tel'), vatPeriodSel=document.getElementById('vat_period'), vatQuarterSel=document.getElementById('vat_quarter');

      const escapeHtml=s=>(s||'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
      function renderOfficers(o){
        const a=o?.active||[], r=o?.resigned||[];
        const table = (set, withActions=false) => set.length ? `
          <table class="table">
            <thead>
              <tr>
                <th>Name</th><th>Role</th><th>Appointed</th><th>Resigned</th><th>Nationality</th><th>Country</th><th>Occupation</th>
                ${withActions ? '<th style="width:1%;white-space:nowrap;">Actions</th>' : ''}
              </tr>
            </thead>
            <tbody>
              ${set.map(x=>`<tr>
                <td>${escapeHtml(x.name||'')}</td>
                <td>${escapeHtml(x.role||'')}</td>
                <td>${escapeHtml(x.appointed||'-')}</td>
                <td>${escapeHtml(x.resigned||'')}</td>
                <td>${escapeHtml(x.nationality||'-')}</td>
                <td>${escapeHtml(x.country||'-')}</td>
                <td>${escapeHtml(x.occupation||'-')}</td>
                ${withActions ? `<td><button class="button small make-individual" data-name="${escapeHtml(x.name||'')}">Add as individual</button></td>` : ''}
              </tr>`).join('')}
            </tbody>
          </table>` : '<div class="muted">None</div>';
        return `
          <div class="section"><h3>Directors & Officers — Active</h3>${table(a, true)}</div>
          <div class="section"><h3>Directors & Officers — Resigned</h3>${table(r, false)}</div>`;
      }
      function renderPscs(ps){const c=ps?.current||[],f=ps?.former||[];const t=s=>s.length?`<table class="table"><thead><tr><th>Name</th><th>Kind</th><th>Control</th><th>Notified</th><th>Ceased</th><th>Country</th></tr></thead><tbody>${s.map(p=>`<tr><td>${escapeHtml(p.name||'')}</td><td>${escapeHtml(p.kind||'-')}</td><td>${(p.control||[]).map(u=>`<span class="pill">${escapeHtml(u)}</span>`).join('')}</td><td>${escapeHtml(p.notified_on||'-')}</td><td>${escapeHtml(p.ceased_on||'')}</td><td>${escapeHtml(p.country||'-')}</td></tr>`).join('')}</tbody></table>`:'<div class="muted">None</div>';return `<div class="section"><h3>Persons with Significant Control — Current</h3>${t(c)}</div><div class="section"><h3>Persons with Significant Control — Former</h3>${t(f)}</div>`}
      function formatYearEnd(acc){const d=acc?.accounting_reference_date; if(!d?.day||!d?.month) return '-'; return `${String(d.day).padStart(2,'0')}/${String(d.month).padStart(2,'0')}`}
      function renderCH(d){
        const sic=(d.sic_codes||[]).join(', '), ye=formatYearEnd(d.accounts);
        const lastMadeUp=d.accounts?.last_accounts?.made_up_to||'-';
        const nextDue=d.accounts?.next_due || d.accounts?.next_accounts?.due_on || '-';
        const nextMadeUp=d.accounts?.next_made_up_to || d.accounts?.next_accounts?.period_end_on || '-';
        return `
          <div class="kv"><b>Name</b> ${escapeHtml(d.name||'')}</div>
          <div class="kv"><b>Number</b> <code class="inline">${escapeHtml(d.number||'')}</code></div>
          <div class="kv"><b>Status</b> ${escapeHtml(d.status||'-')}</div>
          <div class="kv"><b>Type</b> ${escapeHtml(d.type||'-')}</div>
          <div class="kv"><b>Date of creation</b> ${escapeHtml(d.created||'-')}</div>
          <div class="kv"><b>Registered office</b> ${escapeHtml(d.address||'-')}</div>
          <div class="kv"><b>SIC codes</b> ${escapeHtml(sic||'-')}</div>
          <div class="section">
            <h3>Deadlines & Accounts</h3>
            <div class="kv"><b>Year-end (ARD)</b> ${escapeHtml(ye)}</div>
            <div class="kv"><b>Last accounts made up to</b> ${escapeHtml(lastMadeUp)}</div>
            <div class="kv"><b>Accounts next due</b> ${escapeHtml(nextDue)}</div>
            <div class="kv"><b>Accounts next made up to</b> ${escapeHtml(nextMadeUp)}</div>
            <div class="kv"><b>Confirmation statement next due</b> ${escapeHtml(d.confirmation_statement?.next_due||'-')}</div>
            <div class="kv"><b>Confirmation statement next made up to</b> ${escapeHtml(d.confirmation_statement?.next_made_up_to||'-')}</div>
          </div>
          ${renderOfficers(d.officers)}${renderPscs(d.pscs)}
        `;
      }
      async function loadCH(){
        try{const res=await fetch(`/api/ch/${encodeURIComponent(number)}`);const j=await res.json();
          if(!res.ok||j.error) throw new Error(j.error||`HTTP ${res.status}`);
          chContent.innerHTML=renderCH(j.data||{});
        }catch(e){chContent.innerHTML=`<div class="error">Error: ${escapeHtml(e.message||'Unknown error')}</div>`}
      }

      function updateQuarterEnablement(){ if(vatPeriodSel.value==='monthly'){vatQuarterSel.disabled=true;vatQuarterSel.value=''} else {vatQuarterSel.disabled=false} }
      updateQuarterEnablement(); vatPeriodSel.addEventListener('change',updateQuarterEnablement);

      async function saveCustom(){
        errBox.style.display='none'; saveBtn.disabled=true; saveBtn.textContent='Saving…';
        try{
          const csrf=document.querySelector('meta[name="csrf-token"]').content;
          const payload={vat_number:vatInput.value.trim()||null,authentication_code:authInput.value.trim()||null,utr:utrInput.value.trim()||null,email:emailInput.value.trim()||null,telephone:telInput.value.trim()||null,vat_period:vatPeriodSel.value||null,vat_quarter_group:(vatPeriodSel.value==='monthly')?null:(vatQuarterSel.value||null)};
          const res=await fetch(`/api/companies/${encodeURIComponent(number)}`,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
          const j=await res.json().catch(()=>({})); if(!res.ok||j.saved===false) throw new Error(j.message||`HTTP ${res.status}`);
          saveBtn.textContent='Saved ✓';
        }catch(e){errBox.textContent='Save failed: '+(e.message||'Unknown error');errBox.style.display='block';saveBtn.textContent='Save';saveBtn.disabled=false;return}
        saveBtn.disabled=false; setTimeout(()=>{saveBtn.textContent='Save'},1200);
      }
      saveBtn.addEventListener('click',saveCustom);

      // === New: create Individual from director row ===
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.make-individual');
        if (!btn) return;
        e.preventDefault();
        const fullName = btn.getAttribute('data-name') || '';
        if (!fullName) return;

        const orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
          const csrf = document.querySelector('meta[name="csrf-token"]').content;
          const res = await fetch('/api/individuals', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ name: fullName })
          });
          const j = await res.json();
          if (!res.ok || j.saved !== true) throw new Error(j.message || `HTTP ${res.status}`);

          // Replace the button with a link to the new individual
          const id = j.id;
          btn.outerHTML = `<a class="button small ok" href="/individuals/${id}">Open individual ↗</a>`;
        } catch (err) {
          alert('Could not create individual: ' + (err.message || 'Unknown error'));
          btn.disabled = false;
          btn.textContent = orig;
        }
      });

      loadCH();
    </script>
  </body>
</html>
