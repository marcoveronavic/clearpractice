<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Individuals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      :root { --border:#eaeaea; --muted:#666; --drawer-w:260px; --t:.25s; }
      * { box-sizing:border-box; }
      body { margin:0; font-family:system-ui, Arial, sans-serif; line-height:1.45; }
      a { color:inherit; }

      /* Drawer (push) */
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
      .table{width:100%;border-collapse:collapse}
      .table th,.table td{padding:8px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
      .table th{color:var(--muted);font-weight:600}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:8px 10px;background:#fff;color:inherit;cursor:pointer}
      .button.small{padding:6px 8px;font-size:12px;border-radius:8px}
      .muted{color:var(--muted)}
      .small{font-size:12px}
      code.inline{background:#f7f7f7;border:1px solid var(--border);border-radius:6px;padding:1px 6px}

      /* Modal */
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
      .field input,.field textarea{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font-size:15px}
      textarea{min-height:86px;resize:vertical}
      .bar{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
      .error{color:#b00020;font-weight:600;margin-top:6px}

      /* Tag editor for related companies */
      .tags{display:flex;flex-wrap:wrap;gap:6px}
      .tag{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--border);border-radius:999px;padding:4px 8px}
      .tag .x{cursor:pointer;font-weight:700;opacity:.7}
      .tag-input{border:1px dashed #bbb;border-radius:999px;padding:6px 10px;min-width:160px}
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
      <h1>Individuals</h1>

      @if ($individuals->isEmpty())
        <p class="muted">No individuals yet.</p>
      @else
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Address</th>
              <th style="width:1%"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($individuals as $i)
              <tr data-id="{{ $i->id }}">
                <td>
                  <a href="/individuals/{{ $i->id }}">{{ $i->first_name }} {{ $i->last_name }}</a>
                  <div class="small muted">ID: <code class="inline">{{ $i->id }}</code></div>
                </td>
                <td>{{ $i->email }}</td>
                <td>{{ $i->phone ?? '—' }}</td>
                <td class="small">{{ $i->address ?? '—' }}</td>
                <td><button class="button small editBtn" data-id="{{ $i->id }}">Edit</button></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>

    <!-- Modal -->
    <div id="modal" class="modal" aria-hidden="true">
      <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="dlg-title">
        <header>
          <h2 id="dlg-title">Edit individual</h2>
          <button class="close" id="closeBtn" aria-label="Close">✕</button>
        </header>
        <div class="content">
          <div id="err" class="error" style="display:none"></div>

          <div class="grid">
            <div class="field">
              <label for="email">Email</label>
              <input id="email" type="email" placeholder="name@example.com">
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" type="text" placeholder="+44 ...">
            </div>
            <div class="field" style="grid-column:1/-1">
              <label for="address">Address</label>
              <textarea id="address" placeholder="Address"></textarea>
            </div>

            <div class="field" style="grid-column:1/-1">
              <label>Related companies</label>
              <div id="tagBox" class="tags"></div>
              <input id="tagInput" class="tag-input" type="text" placeholder="Type a company and press Enter">
              <div class="small muted" style="margin-top:6px">Press <strong>Enter</strong> to add. Click “×” to remove.</div>
            </div>
          </div>

          <div class="bar">
            <button class="button" id="cancelBtn" type="button">Cancel</button>
            <button class="button" id="saveBtn" type="button">Save</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Drawer
      document.getElementById('menuBtn').addEventListener('click',()=>document.body.classList.toggle('drawer-open'));
      document.addEventListener('keydown',e=>{if(e.key==='Escape')document.body.classList.remove('drawer-open')});
      const p=location.pathname; document.querySelectorAll('.drawer nav a').forEach(a=>{const w=a.dataset.path; if(p===w||(w!=='/'&&p.startsWith(w))) a.classList.add('active')});

      // Helpers
      const qs = s => document.querySelector(s);
      const qsa = s => Array.from(document.querySelectorAll(s));
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const err = qs('#err'), modal=qs('#modal'), closeBtn=qs('#closeBtn'), cancelBtn=qs('#cancelBtn'), saveBtn=qs('#saveBtn');
      const emailI=qs('#email'), phoneI=qs('#phone'), addrI=qs('#address'), tagInput=qs('#tagInput'), tagBox=qs('#tagBox');
      let currentId = null, tags = [];

      function showModal(){ modal.classList.add('show'); modal.setAttribute('aria-hidden','false'); }
      function hideModal(){ modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); }

      function renderTags(){
        tagBox.innerHTML = tags.map((t,i)=>`<span class="tag">${escapeHtml(t)} <span class="x" data-i="${i}">×</span></span>`).join('');
      }
      function escapeHtml(s){ return (s||'').toString().replace(/[&<>"']/g,c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[c])); }

      tagBox.addEventListener('click', e=>{
        const x = e.target.closest('.x'); if(!x) return;
        tags.splice(+x.dataset.i,1); renderTags();
      });

      tagInput.addEventListener('keydown', e=>{
        if(e.key === 'Enter'){
          e.preventDefault();
          const v = tagInput.value.trim();
          if(v && !tags.includes(v)){ tags.push(v); renderTags(); }
          tagInput.value='';
        }
      });

      // Open editor
      document.addEventListener('click', async e=>{
        const btn = e.target.closest('.editBtn'); if(!btn) return;
        const id = +btn.dataset.id; currentId = id;
        err.style.display='none'; err.textContent='';
        emailI.value = phoneI.value = addrI.value = ''; tags = []; renderTags();
        showModal();

        try{
          const res = await fetch(`/api/individuals/${id}`); const j = await res.json();
          if(!res.ok) throw new Error(j.message || `HTTP ${res.status}`);
          const d = j.data || {};
          emailI.value = d.email || '';
          phoneI.value = d.phone || '';
          addrI.value  = d.address || '';
          tags = Array.isArray(d.related_companies) ? d.related_companies : [];
          renderTags();
        }catch(ex){
          err.textContent = 'Load error: ' + (ex.message || 'Unknown error');
          err.style.display='block';
        }
      });

      // Save
      async function save(){
        if(!currentId) return;
        err.style.display='none'; saveBtn.disabled=true; saveBtn.textContent='Saving…';
        try{
          const res = await fetch(`/api/individuals/${currentId}`,{
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},
            body: JSON.stringify({
              email: emailI.value.trim(),
              phone: phoneI.value.trim() || null,
              address: addrI.value.trim() || null,
              related_companies: tags
            })
          });
          const j = await res.json();
          if(!res.ok || j.saved !== true) throw new Error(j.message || `HTTP ${res.status}`);
          // Refresh just the row values in table
          const row = document.querySelector(`tr[data-id="${currentId}"]`);
          if(row){
            row.children[1].textContent = emailI.value.trim();
            row.children[2].textContent = (phoneI.value.trim() || '—');
            row.children[3].textContent = (addrI.value.trim()  || '—');
          }
          hideModal();
        }catch(ex){
          err.textContent = 'Save error: ' + (ex.message || 'Unknown error');
          err.style.display='block';
        }finally{
          saveBtn.disabled=false; saveBtn.textContent='Save';
        }
      }
      saveBtn.addEventListener('click', save);
      cancelBtn.addEventListener('click', hideModal);
      closeBtn.addEventListener('click', hideModal);
      modal.addEventListener('click', e=>{ if(e.target===modal) hideModal(); });
      document.addEventListener('keydown', e=>{ if(e.key==='Escape' && modal.classList.contains('show')) hideModal(); });
    </script>
  </body>
</html>
