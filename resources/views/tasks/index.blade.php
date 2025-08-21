<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Tasks</title>
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
      .card{border:1px solid var(--border);border-radius:14px;padding:16px;background:#fff}
      .grid{display:grid;grid-template-columns:1.2fr 1fr;gap:24px}
      .field{display:flex;flex-direction:column;gap:6px;margin-bottom:10px}
      label{font-size:13px;color:var(--muted)}
      input[type="text"],input[type="date"],select{border:1px solid #ccc;border-radius:8px;padding:10px 12px;font-size:15px}
      .button{display:inline-block;border:1px solid var(--border);border-radius:10px;padding:8px 10px;text-decoration:none;background:#fff;color:inherit;cursor:pointer}
      .button.primary{background:#111;color:#fff;border-color:#111}
      .muted{color:var(--muted)}
      .table{width:100%;border-collapse:collapse}
      .table th,.table td{padding:8px 10px;border-bottom:1px solid var(--border);text-align:left;vertical-align:top}
      .table th{color:var(--muted);font-weight:600}
      .small{font-size:12px}
      .ok{color:#1b5e20}
      .error{color:#b00020}

      /* --- Autocomplete dropdown --- */
      .ac-wrap{position:relative}
      .ac-results{
        position:absolute;left:0;right:0;top:100%;
        border:1px solid var(--border);border-radius:10px;background:#fff;
        box-shadow:0 6px 16px rgba(0,0,0,.08);margin-top:4px;display:none;
        max-height:300px;overflow:auto;z-index:2147483647;
      }
      .ac-item{padding:8px 10px;cursor:pointer}
      .ac-item:hover{background:#f6f6f6}
      .ac-group{padding:6px 10px;font-size:12px;color:#444;background:#f8f8f8;border-top:1px solid var(--border)}
      .badge{display:inline-block;border:1px solid var(--border);border-radius:999px;padding:2px 8px;font-size:12px}
    </style>
  </head>
  <body>
    @include('partials.nav')

    <div class="page">
      <h1>Tasks</h1>

      <div class="grid">
        <div class="card">
          <h2 style="margin:0 0 10px;font-size:18px">Add a task</h2>

          @if ($errors->any())
            <div class="error">
              @foreach ($errors->all() as $e) • {{ $e }}<br>@endforeach
            </div>
          @endif
          @if (session('status'))
            <div class="ok">{{ session('status') }}</div>
          @endif

          <form method="POST" action="/tasks" autocomplete="off">
            @csrf

            <div class="field">
              <label for="user_id">Assign task to</label>
              <select id="user_id" name="user_id" required>
                <option value="">— Select —</option>
                @foreach ($users as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
              </select>
            </div>

            <div class="field">
              <label for="related_search">Related to (Company or Individual) — optional</label>
              <div class="ac-wrap">
                <input id="related_search" type="text" placeholder="Start typing a company or individual">
                <input type="hidden" id="related_type" name="related_type" value="">
                <input type="hidden" id="company_number" name="company_number" value="">
                <input type="hidden" id="individual_id"  name="individual_id"  value="">
                <div id="related_results" class="ac-results"></div>
                <div id="ac_error" class="small error" style="display:none;margin-top:6px;"></div>
              </div>
              <div class="small muted">Leave empty for <em>No related entity</em>.</div>
            </div>

            <div class="field">
              <label for="title">Task title</label>
              <input id="title" name="title" type="text" placeholder="e.g. Request bank statements" required>
            </div>

            <div class="field">
              <label for="deadline">Deadline</label>
              <input id="deadline" name="deadline" type="date" required>
            </div>

            <button class="button primary" type="submit">Add task</button>
          </form>
        </div>

        <div class="card">
          <h2 style="margin:0 0 10px;font-size:18px">Recent</h2>
          @if ($tasks->isEmpty())
            <p class="muted">No tasks yet.</p>
          @else
            <table class="table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Assigned to</th>
                  <th>Related to</th>
                  <th>Deadline</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($tasks as $t)
                  <tr>
                    <td>{{ $t->title }}</td>
                    <td>{{ $t->user?->name ?? '—' }}</td>
                    <td>
                      @if($t->company)
                        Company: {{ $t->company->name }} <span class="badge">{{ $t->company->number }}</span>
                      @elseif($t->individual)
                        Individual: {{ trim(($t->individual->first_name ?? '').' '.($t->individual->last_name ?? '')) }}
                        @if($t->individual->email)
                          <span class="badge">{{ $t->individual->email }}</span>
                        @endif
                      @else
                        <span class="muted">—</span>
                      @endif
                    </td>
                    <td class="small">{{ optional($t->deadline)->format('d/m/Y') ?: '—' }}</td>
                    <td class="small muted">{{ optional($t->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>

    <script>
      (function(){
        const SUGGEST_URL = '/api/entities/suggest'; // RELATIVE so it always uses your current host/port

        const input   = document.getElementById('related_search');
        const list    = document.getElementById('related_results');
        const typeEl  = document.getElementById('related_type');
        const compEl  = document.getElementById('company_number');
        const indEl   = document.getElementById('individual_id');
        const err     = document.getElementById('ac_error');
        let timer;

        function hide(){ list.style.display='none'; list.innerHTML=''; }
        function show(){ list.style.display='block'; }
        function msg(t){ err.textContent=t; err.style.display=t ? 'block':'none'; }
        function clearHidden(){ typeEl.value=''; compEl.value=''; indEl.value=''; }
        function pickNone(){ clearHidden(); input.value=''; hide(); }
        function pickCompany(c){ typeEl.value='company'; compEl.value=c.number; indEl.value=''; input.value=`${c.name} (${c.number})`; hide(); }
        function pickIndividual(i){
          typeEl.value='individual'; indEl.value=i.id; compEl.value='';
          const full = `${i.first_name ?? ''} ${i.last_name ?? ''}`.trim();
          input.value = i.email ? `${full} <${i.email}>` : full; hide();
        }
        function group(t){ const h=document.createElement('div'); h.className='ac-group'; h.textContent=t; return h; }
        function item(label, on){ const el=document.createElement('div'); el.className='ac-item'; el.innerHTML=label; el.addEventListener('click', on); return el; }

        function render(payload){
          list.innerHTML='';
          list.appendChild(item('No related entity', pickNone));

          const { companies=[], individuals=[] } = payload || {};

          if (companies.length){ list.appendChild(group('Companies'));
            companies.forEach(c=>{
              list.appendChild(item(`<strong>${c.name}</strong> <span class="badge">${c.number}</span>`, ()=>pickCompany(c)));
            });
          }
          if (individuals.length){ list.appendChild(group('Individuals'));
            individuals.forEach(i=>{
              const full = `${i.first_name ?? ''} ${i.last_name ?? ''}`.trim();
              const lbl  = i.email ? `<strong>${full}</strong> <span class="badge">${i.email}</span>` : `<strong>${full}</strong>`;
              list.appendChild(item(lbl, ()=>pickIndividual(i)));
            });
          }
          show();
        }

        async function search(q){
          if (!q || q.length < 2){ hide(); return; }
          try{
            msg('');
            const res = await fetch(`${SUGGEST_URL}?q=${encodeURIComponent(q)}`, {
              headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok){ msg(`Autocomplete HTTP ${res.status}`); hide(); return; }
            const json = await res.json();
            render(json);
          }catch(e){ msg('Autocomplete failed'); hide(); }
        }

        input.addEventListener('input', ()=>{
          clearHidden();
          clearTimeout(timer);
          timer = setTimeout(()=>search(input.value.trim()), 200);
        });

        input.addEventListener('focus', ()=>{
          const v = input.value.trim();
          if (v.length >= 2) search(v);
        });

        document.addEventListener('click', (e)=>{
          if (!list.contains(e.target) && e.target !== input) hide();
        });
      }());
    </script>
  </body>
</html>
