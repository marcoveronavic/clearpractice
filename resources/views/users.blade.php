@extends('layouts.app')

@section('content')
  <h1>Users</h1>

  @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
  @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

  <div class="card">
    {{-- Add user (quick) --}}
    <form
      style="display:grid;grid-template-columns:1fr 1fr 1.7fr 1fr auto;gap:10px;align-items:center"
      action="{{ route('users.store') }}" method="POST">
      @csrf
      <input type="text"  name="name"    placeholder="Name">
      <input type="text"  name="surname" placeholder="Surname">
      <input type="email" name="email"   placeholder="Email">
      <input type="text"  name="phone"   placeholder="Phone">
      <button class="btn" type="submit">Add user</button>
    </form>

    <table style="margin-top:10px">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($users as $u)
        <tr>
          <td>
            <strong>{{ trim(($u['name'] ?? '').' '.($u['surname'] ?? '')) }}</strong>
            <div class="muted">
              {{ $u['added'] ?? '' }}
              @if(!empty($u['updated'])) • updated {{ $u['updated'] }} @endif
            </div>
            @if(!empty($u['address']) || !empty($u['notes']))
              <div class="muted" style="margin-top:4px">
                {{ $u['address'] ?? '' }}@if(!empty($u['address']) && !empty($u['notes'])) • @endif{{ $u['notes'] ?? '' }}
              </div>
            @endif
          </td>
          <td>{{ $u['email'] ?? '' }}</td>
          <td>{{ $u['phone'] ?? '' }}</td>
          <td style="display:flex;gap:6px">
            {{-- EDIT / VIEW button opens modal --}}
            <button
              class="btn"
              data-edit
              data-id="{{ $u['id'] }}"
              data-name="{{ $u['name'] ?? '' }}"
              data-surname="{{ $u['surname'] ?? '' }}"
              data-email="{{ $u['email'] ?? '' }}"
              data-phone="{{ $u['phone'] ?? '' }}"
              data-address="{{ $u['address'] ?? '' }}"
              data-notes="{{ $u['notes'] ?? '' }}"
            >View / Edit</button>

            <form action="{{ route('users.destroy', ['id' => $u['id']]) }}" method="POST" onsubmit="return confirm('Delete this user?');">
              @csrf @method('DELETE')
              <button class="btn danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="muted">No users yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Modal (hidden) --}}
  <div id="modalOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center;z-index:80"></div>
  <div id="modal" style="position:fixed;inset:auto;left:50%;top:50%;transform:translate(-50%,-50%) scale(.98);opacity:0;pointer-events:none;z-index:90;transition:.15s">
    <div class="card" style="width:min(640px,94vw);max-height:80vh;overflow:auto">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <h3 style="margin:0">View / Edit user</h3>
        <button id="modalClose" class="btn">Close</button>
      </div>

      <form id="modalForm" method="POST">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div>
            <label class="muted">Name</label>
            <input type="text" name="name" id="m_name">
          </div>
          <div>
            <label class="muted">Surname</label>
            <input type="text" name="surname" id="m_surname">
          </div>
          <div>
            <label class="muted">Email</label>
            <input type="email" name="email" id="m_email">
          </div>
          <div>
            <label class="muted">Phone</label>
            <input type="text" name="phone" id="m_phone">
          </div>
          <div style="grid-column:1/-1">
            <label class="muted">Address</label>
            <input type="text" name="address" id="m_address">
          </div>
          <div style="grid-column:1/-1">
            <label class="muted">Notes</label>
            <textarea name="notes" id="m_notes" rows="4" style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px"></textarea>
          </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:14px">
          <button type="button" class="btn" id="modalCancel">Cancel</button>
          <button type="submit" class="btn primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>

@endsection

@section('scripts')
<script>
(function(){
  const $ = (sel, p=document) => p.querySelector(sel);
  const $$ = (sel, p=document) => Array.from(p.querySelectorAll(sel));

  const overlay = $('#modalOverlay');
  const modal   = $('#modal');
  const form    = $('#modalForm');

  const open = () => { overlay.style.display='flex'; modal.style.opacity='1'; modal.style.pointerEvents='auto'; modal.style.transform='translate(-50%,-50%) scale(1)'; };
  const close= () => { overlay.style.display='none'; modal.style.opacity='0'; modal.style.pointerEvents='none'; modal.style.transform='translate(-50%,-50%) scale(.98)'; };

  $('#modalClose').addEventListener('click', close);
  $('#modalCancel').addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', e => { if(e.key==='Escape') close(); });

  // Wire edit buttons
  $$('[data-edit]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      $('#m_name').value    = btn.dataset.name || '';
      $('#m_surname').value = btn.dataset.surname || '';
      $('#m_email').value   = btn.dataset.email || '';
      $('#m_phone').value   = btn.dataset.phone || '';
      $('#m_address').value = btn.dataset.address || '';
      $('#m_notes').value   = btn.dataset.notes || '';

      // Point the form to /users/{id}
      form.setAttribute('action', '{{ url('/users') }}/' + encodeURIComponent(id));
      open();
    });
  });
})();
</script>
@endsection
