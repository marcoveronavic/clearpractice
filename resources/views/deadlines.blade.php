<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Deadlines</title>
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
      .scroll{overflow-x:auto;padding-bottom:6px}
      table.matrix{border-collapse:collapse;min-width:720px;width:100%}
      .matrix th,.matrix td{border:1px solid var(--border);padding:10px 12px;vertical-align:top}
      .matrix th{background:#fafafa;font-weight:600}
      .matrix th.company,.matrix td.company{min-width:280px}
      .deadline{text-align:center;white-space:nowrap;transition:background .2s ease,color .2s ease,border-color .2s ease}
      .deadline.d-red{background:#ffe6e6;color:#8a0000;border-color:#f5c2c7;font-weight:600}
      .deadline.d-orange{background:#fff0e1;color:#8a4b00;border-color:#ffd8b1;font-weight:600}
      .deadline.d-yellow{background:#fffbe6;color:#7a6e00;border-color:#fff0a6;font-weight:600}
      .deadline.d-green{background:#eaf6ec;color:#1b5e20;border-color:#c8e6c9;font-weight:600}
      .num{font-family:ui-monospace, Menlo, Consolas, monospace}
      .small{font-size:12px}
      .vatbox .editbox{display:none;gap:6px;align-items:center}
      .vatbox.editing .val{display:none}
      .vatbox.editing .editbox{display:inline-flex}
      input[type="date"]{padding:6px 8px;border:1px solid #ccc;border-radius:8px}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:6px 8px;background:#fff}
      .small.btn{font-size:12px}
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
      <h1>Deadlines</h1>

      @if (empty($companies))
        <p class="muted">No companies saved yet. Add some from <a href="/ch">Companies House Search</a>.</p>
        <a href="/tasks" data-path="/tasks">Tasks</a>
        <a href="/individuals" data-path="/individuals">Individuals</a>
      @else
        <div class="scroll">
          <table class="matrix" id="dlTable">
            <thead>
              <tr>
                <th class="company">Company</th>
                <th>Accounts deadline</th>
                <th>Confirmation statement deadline</th>
                <th>VAT deadline</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($companies as $c)
                @php $num = $c['number'] ?? ''; @endphp
                <tr data-number="{{ $num }}">
                  <td class="company">
                    <div><a href="/companies/{{ urlencode($num) }}">{{ $c['name'] ?? 'Company' }}</a></div>
                    <div class="small muted num">{{ $num }}</div>
                  </td>
                  <td id="acc-{{ $num }}" class="deadline"><span class="muted">Loading…</span></td>
                  <td id="cs-{{ $num }}" class="deadline"><span class="muted">Loading…</span></td>
                  <td class="deadline" id="vat-cell-{{ $num }}" data-vat-date="{{ $c['vat_deadline'] ?? '' }}">
                    <span id="vat-{{ $num }}" class="vatbox">
                      <span class="val">
                        @if (!empty($c['vat_deadline']))
                          {{ \Carbon\Carbon::parse($c['vat_deadline'])->format('d/m/Y') }}
                        @else
                          —
                        @endif
                      </span>
                      <button class="button small editBtn" data-number="{{ $num }}">Edit</button>
                      <span class="editbox">
                        <input type="date" class="dateInput" value="{{ $c['vat_deadline'] ?? '' }}">
                        <button class="button small saveBtn" data-number="{{ $num }}">Save</button>
                        <button class="button small cancelBtn" type="button">Cancel</button>
                      </span>
                    </span>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <script>
      // Drawer push
      document.getElementById('menuBtn').addEventListener('click',()=>{document.body.classList.toggle('drawer-open')});
      document.addEventListener('keydown',e=>{if(e.key==='Escape')document.body.classList.remove('drawer-open')});
      const p=location.pathname;
      document.querySelectorAll('.drawer nav a').forEach(a=>{const w=a.dataset.path; if(p===w||(w!=='/'&&p.startsWith(w)))a.classList.add('active')});

      // Deadlines colours + CH fetch
      const companyNumbers=@json(array_map(fn($x)=>$x['number'] ?? '', $companies ?? []));
      const csrf=document.querySelector('meta[name="csrf-token"]').content;
      const byId=id=>document.getElementById(id);
      const pad=n=>String(n).padStart(2,'0');
      function formatDate(s){ if(!s) return '—'; const m=/^(\d{4})-(\d{2})-(\d{2})$/.exec(s); let d; if(m){d=new Date(Date.UTC(+m[1],+m[2]-1,+m[3]))} else {const t=Date.parse(s); if(Number.isNaN(t)) return s; const tmp=new Date(t); d=new Date(Date.UTC(tmp.getFullYear(),tmp.getMonth(),tmp.getDate()))} return `${pad(d.getUTCDate())}/${pad(d.getUTCMonth()+1)}/${d.getUTCFullYear()}` }
      const MS=86400000;
      function daysUntil(s){ if(!s) return null; const m=/^(\d{4})-(\d{2})-(\d{2})$/.exec(s); let due; if(m){due=Date.UTC(+m[1],+m[2]-1,+m[3])} else {const t=Date.parse(s); if(Number.isNaN(t)) return null; const d=new Date(t); due=Date.UTC(d.getFullYear(),d.getMonth(),d.getDate())} const n=new Date(); const today=Date.UTC(n.getFullYear(),n.getMonth(),n.getDate()); return Math.floor((due-today)/MS) }
      const classify=d=>d===null?null:d<0?'d-red':d<=29?'d-orange':d<=90?'d-yellow':'d-green';
      function applyColor(cell,iso){ if(!cell) return; cell.classList.remove('d-red','d-orange','d-yellow','d-green'); if(!iso) return; const cls=classify(daysUntil(iso)); if(cls) cell.classList.add(cls) }

      async function loadCH(number){
        try{
          const res=await fetch(`/api/ch/${encodeURIComponent(number)}`); const j=await res.json();
          if(!res.ok||j.error) throw new Error(j.error||`HTTP ${res.status}`);
          const d=j.data||{};
          const accDue=d.accounts?.next_due || d.accounts?.next_accounts?.due_on || null;
          const csDue=d.confirmation_statement?.next_due || null;
          const accCell=byId(`acc-${number}`), csCell=byId(`cs-${number}`);
          if(accCell){ accCell.textContent=formatDate(accDue); applyColor(accCell, accDue) }
          if(csCell){ csCell.textContent=formatDate(csDue); applyColor(csCell, csDue) }
        }catch(e){
          const a=byId(`acc-${number}`); if(a){a.textContent='Error'; a.classList.remove('d-red','d-orange','d-yellow','d-green')}
          const c=byId(`cs-${number}`); if(c){c.textContent='Error'; c.classList.remove('d-red','d-orange','d-yellow','d-green')}
        }
      }
      companyNumbers.forEach(n=>{if(n)loadCH(n)});
      document.querySelectorAll('[id^="vat-cell-"]').forEach(td=>{applyColor(td, td.getAttribute('data-vat-date')||null)});

      // Inline VAT edit
      document.addEventListener('click',async e=>{
        const edit=e.target.closest('.editBtn'), cancel=e.target.closest('.cancelBtn'), save=e.target.closest('.saveBtn');
        if(edit){document.getElementById(`vat-${edit.dataset.number}`)?.classList.add('editing');return}
        if(cancel){cancel.closest('.vatbox')?.classList.remove('editing');return}
        if(save){
          const n=save.dataset.number, box=document.getElementById(`vat-${n}`); if(!box) return;
          const td=document.getElementById(`vat-cell-${n}`), dateInput=box.querySelector('.dateInput'), val=box.querySelector('.val'); const iso=dateInput.value||null;
          save.disabled=true; save.textContent='Saving…';
          try{
            const res=await fetch(`/api/companies/${encodeURIComponent(n)}`,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({vat_deadline:iso})});
            const j=await res.json().catch(()=>({})); if(!res.ok||j.saved===false) throw new Error(j.message||`HTTP ${res.status}`);
            val.textContent=iso?formatDate(iso):'—'; td.setAttribute('data-vat-date',iso||''); applyColor(td,iso); box.classList.remove('editing');
          }catch(err){ alert('Save failed: '+(err.message||'Unknown error')) } finally { save.disabled=false; save.textContent='Save' }
        }
      });
    </script>
  </body>
</html>
