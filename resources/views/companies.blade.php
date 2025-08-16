@extends('layouts.app')

@section('content')
  <h1>Companies</h1>

  @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
  @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

  <div class="card">
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
      <input class="input" placeholder="Company number" id="add-number" style="width:160px">
      <input class="input" placeholder="Company name"   id="add-name"   style="width:280px">
      <button class="btn" id="add-update">Add / Update</button>
      <span class="muted">Use CH search page to copy number/name, or fill manually.</span>
    </div>

    <table>
      <thead>
      <tr>
        <th style="width:120px">Number</th>
        <th>Name</th>
        <th style="width:180px">Status / Type</th>
        <th style="width:120px">Created</th>
        <th>Address</th>
        <th style="width:160px">Action</th>
      </tr>
      </thead>
      <tbody>
      @forelse ($companies as $c)
        @php
          $meta = $c['meta'] ?? [];
          $num  = $c['number'] ?? '';
          $name = $c['name']   ?? '';
          $openChUrl = $num ? 'https://find-and-update.company-information.service.gov.uk/company/' . urlencode($num) : '#';
        @endphp

        <!-- Make entire row clickable (except links/buttons/forms) -->
        <tr class="row-open"
            data-number="{{ $num }}"
            data-name="{{ $name }}"
            data-meta='@json($meta)'>
          <td>
            <div style="font-weight:600">{{ $num }}</div>
            @if($num)
              <a class="muted" href="{{ $openChUrl }}" target="_blank" rel="noopener">Open on CH ↗</a>
            @endif
          </td>
          <td>
            <div style="font-weight:600">{{ $name }}</div>
            @if (!empty($c['sic']) && is_array($c['sic']))
              <div class="muted" style="margin-top:2px">
                @foreach ($c['sic'] as $code)
                  <span class="pill" style="margin-right:4px">{{ $code }}</span>
                @endforeach
              </div>
            @endif
          </td>
          <td>
            <div>{{ $c['status'] ?? '' }}</div>
            <div class="muted">
              {{ $c['type'] ?? '' }}
              @if(!empty($c['jurisdiction'])) • {{ $c['jurisdiction'] }} @endif
            </div>
          </td>
          <td>{{ $c['created'] ?? '' }}</td>
          <td>{{ $c['address'] ?? '' }}</td>
          <td>
            <!-- Dedicated button (works even if row click is disabled) -->
            <button class="btn light open-edit"
                    data-number="{{ $num }}"
                    data-name="{{ $name }}"
                    data-meta='@json($meta)'>
              Edit / View
            </button>

            <form action="{{ route('companies.destroy', ['number' => $num]) }}"
                  method="POST"
                  style="display:inline-block;margin-left:8px"
                  onsubmit="return confirm('Delete this company?');">
              @csrf
              @method('DELETE')
              <button class="btn danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="muted">No companies saved yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <!-- Edit / View Modal -->
  <dialog id="edit-modal" style="max-width:720px;border-radius:14px;border:1px solid #e5e7eb;">
    <form id="edit-form" method="POST">
      @csrf
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <div>
          <div style="font-weight:700" id="edit-title">Edit company</div>
          <div class="muted" id="edit-subtitle"></div>
        </div>
        <button type="button" class="btn light" id="edit-close">Close</button>
      </div>

      <div class="grid-2" style="gap:10px">
        <div class="field">
          <label>Authentication code</label>
          <input class="input" name="authentication_code" />
        </div>
        <div class="field">
          <label>UTR</label>
          <input class="input" name="utr" />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Registered office</label>
          <input class="input" name="registered_office" />
        </div>

        <div class="field">
          <label>VAT number</label>
          <input class="input" name="vat_number" />
        </div>
        <div class="field">
          <label>VAT quarter end (e.g. Mar / 03 / March)</label>
          <input class="input" name="vat_quarter" />
        </div>

        <div class="field">
          <label>GOV ID (Gateway)</label>
          <input class="input" name="gov_id" />
        </div>
        <div class="field">
          <label>GOV password</label>
          <input class="input" name="gov_password" />
        </div>

        <div class="field">
          <label>PAYE account office ref</label>
          <input class="input" name="paye_office_ref" />
        </div>
        <div class="field">
          <label>Employer reference</label>
          <input class="input" name="employer_ref" />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Related companies (comma separated numbers)</label>
          <input class="input" name="related_companies" />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Notes</label>
          <textarea class="input" rows="3" name="notes"></textarea>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:12px">
        <button class="btn" type="submit">Save</button>
      </div>
    </form>
  </dialog>

  <style>
    .grid-2{display:grid;grid-template-columns:1fr 1fr}
    .field label{display:block;font-size:12px;color:#6b7280;margin-bottom:4px}
    .row-open{cursor:pointer}
    .row-open a,.row-open button,.row-open form{cursor:auto} /* keep native cursor on controls */
  </style>

  <script>
    (function(){
      const addBtn  = document.getElementById('add-update');
      const addNumber = document.getElementById('add-number');
      const addName   = document.getElementById('add-name');

      // Add / Update action -> POST /companies (number, optional name)
      addBtn?.addEventListener('click', async () => {
        const number = addNumber.value.trim();
        const name   = addName.value.trim();
        if (!number) { alert('Please type a company number'); return; }

        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('number', number);
        if (name) fd.append('name', name);

        const r = await fetch('{{ route('companies.store') }}', { method:'POST', body: fd });
        if (r.redirected) window.location = r.url; else location.reload();
      });

      // Modal logic
      const dlg = document.getElementById('edit-modal');
      const closeBtn = document.getElementById('edit-close');
      const form = document.getElementById('edit-form');
      const title = document.getElementById('edit-title');
      const sub   = document.getElementById('edit-subtitle');

      function fillForm(meta){
        const set = (name, v) => { const el = form.querySelector(`[name="${name}"]`); if (el) el.value = v ?? ''; };
        set('authentication_code', meta.authentication_code);
        set('utr',                meta.utr);
        set('registered_office',  meta.registered_office);
        set('vat_number',         meta.vat_number);
        set('vat_quarter',        meta.vat_quarter ?? meta.vat_quarter_end ?? meta.vat_qtr ?? meta.vat_quarter_month ?? meta.vat_anchor ?? meta.vat_month);
        set('gov_id',             meta.gov_id);
        set('gov_password',       meta.gov_password);
        set('paye_office_ref',    meta.paye_office_ref);
        set('employer_ref',       meta.employer_ref);
        set('related_companies',  Array.isArray(meta.related_companies) ? meta.related_companies.join(',') : (meta.related_companies ?? ''));
        set('notes',              meta.notes);
      }

      function openModal(number, name, meta){
        title.textContent = `Edit company ${number || ''}`;
        sub.textContent   = name || '';
        fillForm(meta || {});
        form.action = '{{ url('/companies') }}/' + encodeURIComponent(number || '') + '/meta';
        dlg.showModal();
      }

      // Dedicated Edit / View buttons
      document.querySelectorAll('.open-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation(); // don’t let row click also trigger
          const number = btn.dataset.number || '';
          const name   = btn.dataset.name   || '';
          const meta   = JSON.parse(btn.dataset.meta || '{}');
          openModal(number, name, meta);
        });
      });

      // Click on the whole row (except links/buttons/forms) opens modal
      document.querySelectorAll('tr.row-open').forEach(tr => {
        tr.addEventListener('click', (e) => {
          // Ignore clicks on controls/links/forms inside the row
          if (e.target.closest('a,button,form,input,select,textarea')) return;
          const number = tr.dataset.number || '';
          const name   = tr.dataset.name   || '';
          const meta   = JSON.parse(tr.dataset.meta || '{}');
          openModal(number, name, meta);
        });
      });

      closeBtn?.addEventListener('click', () => dlg.close());
    })();
  </script>
@endsection
