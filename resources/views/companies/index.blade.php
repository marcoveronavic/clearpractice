<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Companies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
      :root { --border:#e5e7eb; --muted:#6b7280; --bg:#f9fafb; }
      * { box-sizing: border-box; }
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color:#111827; }
      h1 { margin: 0 0 14px; }
      form.add { display:flex; gap:10px; margin: 10px 0 18px; }
      input[type=text] { padding:10px 12px; border:1px solid var(--border); border-radius:10px; flex:1; }
      button { padding:10px 14px; border:1px solid #111827; background:#111827; color:#fff; border-radius:10px; cursor:pointer; }
      .msg { padding:10px 12px; border-radius:10px; margin-bottom:12px; }
      .ok { background:#ecfdf5; border:1px solid #10b981; color:#065f46; }
      .err { background:#fef2f2; border:1px solid #ef4444; color:#7f1d1d; }
      table { width:100%; border-collapse: collapse; }
      th, td { padding:10px; border-bottom:1px solid var(--border); text-align:left; vertical-align:top; }
      th { background: var(--bg); }
      .actions a, .actions form, .actions button { display:inline-block; margin-right:8px; }
      .del { border:1px solid #ef4444; color:#ef4444; background:#fff; }
      .muted { color: var(--muted); }
      .nav { margin-bottom: 8px; }
      .nav a { margin-right: 10px; color:#2563eb; text-decoration:none; }
      .nav a:hover { text-decoration:underline; }

      /* Modal */
      .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: none; align-items: center; justify-content: center; padding: 20px; }
      .modal { width: 560px; max-width: 100%; background: #fff; border:1px solid var(--border); border-radius: 12px; padding: 16px; }
      .modal h2 { margin: 6px 0 10px; }
      .row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
      .field { display:flex; flex-direction: column; gap:6px; margin-bottom: 10px; }
      .field input, .field select { padding:10px 12px; border:1px solid var(--border); border-radius:10px; }
      .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top: 8px; }
      .btn { padding:10px 14px; border-radius:10px; border:1px solid #111827; background:#111827; color:#fff; cursor:pointer; }
      .btn.secondary { background:#fff; color:#111827; }
      .btn[disabled]{ opacity:.6; cursor:not-allowed; }
      .small { font-size: 12px; }
      .tag { display:inline-block; padding:2px 8px; border:1px solid var(--border); border-radius:999px; margin-right:6px; font-size:12px; }
    </style>
  </head>
  <body>
    <div class="nav">
      <a href="{{ url('/ch') }}">Search</a>
      <a href="{{ route('companies.index') }}"><strong>Companies</strong></a>
      <a href="{{ url('/clients') }}">Clients</a>
    </div>

    <h1>Companies</h1>

    @if (session('status')) <div class="msg ok">{{ session('status') }}</div> @endif
    @if (session('error'))  <div class="msg err">{{ session('error') }}</div> @endif
    @if ($errors->any())
      <div class="msg err">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form class="add" method="POST" action="{{ route('companies.store') }}">
      @csrf
      <input type="text" name="number" placeholder="Enter a company number (e.g. 00445790)" required>
      <button type="submit">Fetch & Save</button>
    </form>

    @if($companies->count())
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Number</th>
            <th>Status / Created</th>
            <th>VAT</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="company-rows">
          @foreach ($companies as $c)
            <tr id="row-{{ $c->id }}">
              <td>
                <a href="{{ route('ch.company', ['number' => $c->number]) }}">{{ $c->name }}</a>
                <div class="muted small">{{ $c->address ?? '-' }}</div>
              </td>
              <td>{{ $c->number }}</td>
              <td>
                {{ $c->status ?? '-' }}<br>
                <span class="muted small">{{ $c->date_of_creation ? $c->date_of_creation->format('Y-m-d') : '-' }}</span>
              </td>
              <td class="small" id="vatcol-{{ $c->id }}">
                @if($c->vat_number)<span class="tag">VAT: {{ $c->vat_number }}</span>@endif
                @if($c->utr)<span class="tag">UTR: {{ $c->utr }}</span>@endif
                @if($c->auth_code)<span class="tag">Auth: {{ $c->auth_code }}</span>@endif
                @if($c->vat_period)
                  <span class="tag">
                    {{ strtoupper($c->vat_period) }}
                    @if($c->vat_period === 'quarterly' && $c->vat_quarter)
                      — {{
                        $c->vat_quarter === 'jan_apr_jul_oct' ? 'Jan/Apr/Jul/Oct' :
                        ($c->vat_quarter === 'feb_may_nov' ? 'Feb/May/Nov' : 'Mar/Jun/Sep/Dec')
                      }}
                    @endif
                  </span>
                @endif
                @if(!$c->vat_number && !$c->utr && !$c->auth_code && !$c->vat_period)
                  <span class="muted">No VAT data</span>
                @endif
              </td>
              <td class="actions">
                <a href="{{ route('ch.company', ['number' => $c->number]) }}">Open</a>
                <a href="https://find-and-update.company-information.service.gov.uk/company/{{ urlencode($c->number) }}" target="_blank" rel="noopener">CH ↗</a>
                <button class="btn secondary edit-btn"
                  data-id="{{ $c->id }}"
                  data-name="{{ $c->name }}"
                  data-vat-number="{{ $c->vat_number ?? '' }}"
                  data-utr="{{ $c->utr ?? '' }}"
                  data-auth="{{ $c->auth_code ?? '' }}"
                  data-period="{{ $c->vat_period ?? '' }}"
                  data-quarter="{{ $c->vat_quarter ?? '' }}"
                >Edit</button>
                <form method="POST" action="{{ route('companies.destroy', $c->id) }}" onsubmit="return confirm('Delete {{ $c->name }}?');" style="display:inline;">
                  @csrf @method('DELETE')
                  <button class="del" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="muted">No companies saved yet. Add one above.</p>
    @endif

    <!-- Modal -->
    <div id="modal-backdrop" class="modal-backdrop">
      <div class="modal">
        <h2 id="modal-title">Edit company</h2>
        <div class="field">
          <label>VAT number</label>
          <input id="f-vat-number" type="text" placeholder="GB123456789">
        </div>
        <div class="row">
          <div class="field">
            <label>UTR</label>
            <input id="f-utr" type="text" placeholder="10-digit UTR">
          </div>
          <div class="field">
            <label>Authentication code</label>
            <input id="f-auth" type="text" placeholder="Companies House auth code">
          </div>
        </div>
        <div class="row">
          <div class="field">
            <label>VAT period</label>
            <select id="f-period">
              <option value="">—</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
            </select>
          </div>
          <div class="field">
            <label>VAT quarter</label>
            <select id="f-quarter">
              <option value="">—</option>
              <option value="jan_apr_jul_oct">Jan / Apr / Jul / Oct</option>
              <option value="feb_may_nov">Feb / May / Nov</option>
              <option value="mar_jun_sep_dec">Mar / Jun / Sep / Dec</option>
            </select>
            <div class="small muted">Shown only when period is Quarterly.</div>
          </div>
        </div>
        <div class="modal-actions">
          <button id="btn-cancel" class="btn secondary" type="button">Cancel</button>
          <button id="btn-save" class="btn" type="button">Save</button>
        </div>
        <div id="modal-msg" class="small muted" style="margin-top:8px;"></div>
      </div>
    </div>

    <script>
      const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const UPDATE_URL_TMPL = @json(route('companies.update', ['company' => '___ID___']));

      const backdrop = document.getElementById('modal-backdrop');
      const titleEl  = document.getElementById('modal-title');
      const msgEl    = document.getElementById('modal-msg');
      const fVat     = document.getElementById('f-vat-number');
      const fUtr     = document.getElementById('f-utr');
      const fAuth    = document.getElementById('f-auth');
      const fPeriod  = document.getElementById('f-period');
      const fQuarter = document.getElementById('f-quarter');
      const btnSave  = document.getElementById('btn-save');
      const btnCancel= document.getElementById('btn-cancel');

      let currentId = null;

      function openModalFor(btn) {
        currentId = btn.dataset.id;
        titleEl.textContent = 'Edit: ' + (btn.dataset.name || '');
        fVat.value    = btn.dataset.vatNumber || '';
        fUtr.value    = btn.dataset.utr || '';
        fAuth.value   = btn.dataset.auth || '';
        fPeriod.value = btn.dataset.period || '';
        fQuarter.value= btn.dataset.quarter || '';
        toggleQuarter();
        msgEl.textContent = '';
        backdrop.style.display = 'flex';
      }
      function closeModal(){ backdrop.style.display = 'none'; }

      function toggleQuarter() {
        const qWrap = fQuarter.parentElement;
        if (fPeriod.value === 'quarterly') {
          fQuarter.disabled = false;
        } else {
          fQuarter.value = '';
          fQuarter.disabled = true;
        }
      }
      fPeriod.addEventListener('change', toggleQuarter);

      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => { e.preventDefault(); openModalFor(btn); });
      });
      btnCancel.addEventListener('click', () => closeModal());
      backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });

      function updateUrl(id){ return UPDATE_URL_TMPL.replace('___ID___', id); }

      btnSave.addEventListener('click', async () => {
        if (!currentId) return;

        const payload = {
          vat_number:  fVat.value.trim() || null,
          utr:         fUtr.value.trim() || null,
          auth_code:   fAuth.value.trim() || null,
          vat_period:  fPeriod.value || null,
          vat_quarter: fPeriod.value === 'quarterly' ? (fQuarter.value || null) : null,
        };

        try {
          btnSave.disabled = true; msgEl.textContent = 'Saving…';
          const res = await fetch(updateUrl(currentId), {
            method: 'PATCH',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify(payload)
          });
          const data = await res.json();
          if (!res.ok || data.ok === false) throw new Error((data && data.error) ? data.error : ('HTTP ' + res.status));

          // Update VAT column summary
          const col = document.getElementById('vatcol-' + currentId);
          if (col) {
            const tags = [];
            if (data.company.vat_number) tags.push(`<span class="tag">VAT: ${data.company.vat_number}</span>`);
            if (data.company.utr)        tags.push(`<span class="tag">UTR: ${data.company.utr}</span>`);
            if (data.company.auth_code)  tags.push(`<span class="tag">Auth: ${data.company.auth_code}</span>`);
            if (data.company.vat_period) {
              let label = data.company.vat_period.toUpperCase();
              if (data.company.vat_period === 'quarterly' && data.company.vat_quarter) {
                label += ' — ' + (data.company.vat_quarter === 'jan_apr_jul_oct' ? 'Jan/Apr/Jul/Oct'
                        : data.company.vat_quarter === 'feb_may_nov' ? 'Feb/May/Nov'
                        : 'Mar/Jun/Sep/Dec');
              }
              tags.push(`<span class="tag">${label}</span>`);
            }
            col.innerHTML = tags.length ? tags.join(' ') : '<span class="muted">No VAT data</span>';
          }

          msgEl.textContent = 'Saved ✓';
          setTimeout(closeModal, 600);
        } catch (err) {
          msgEl.textContent = 'Error: ' + err.message;
        } finally {
          btnSave.disabled = false;
        }
      });
    </script>
  </body>
</html>
