@extends('layouts.app')

@section('content')
  <h1>Clients</h1>

  @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
  @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

  <div class="card">
    <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px">
      <button class="btn" id="btn-add">Add client</button>
      <span class="muted">Individuals/people you work with.</span>
    </div>

    <table>
      <thead>
      <tr>
        <th>Name</th>
        <th style="width:220px">Email</th>
        <th style="width:160px">Phone</th>
        <th>Address</th>
        <th style="width:160px">Action</th>
      </tr>
      </thead>
      <tbody>
      @forelse ($clients as $c)
        <tr>
          <td>
            <div style="font-weight:600">{{ ($c['name'] ?? '') . (($c['surname'] ?? '') ? ' ' . $c['surname'] : '') }}</div>
            @if(!empty($c['notes'])) <div class="muted">{{ $c['notes'] }}</div> @endif
          </td>
          <td>{{ $c['email'] ?? '' }}</td>
          <td>{{ $c['phone'] ?? '' }}</td>
          <td>{{ $c['address'] ?? '' }}</td>
          <td>
            <button class="btn light btn-edit"
                    data-id="{{ $c['id'] ?? '' }}"
                    data-name="{{ $c['name'] ?? '' }}"
                    data-surname="{{ $c['surname'] ?? '' }}"
                    data-email="{{ $c['email'] ?? '' }}"
                    data-phone="{{ $c['phone'] ?? '' }}"
                    data-address="{{ $c['address'] ?? '' }}"
                    data-notes="{{ $c['notes'] ?? '' }}">
              Edit / View
            </button>

            <form action="{{ route('clients.destroy', ['id' => $c['id'] ?? '']) }}"
                  method="POST"
                  style="display:inline-block;margin-left:8px"
                  onsubmit="return confirm('Delete this client?');">
              @csrf
              @method('DELETE')
              <button class="btn danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="muted">No clients yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <!-- Add/Edit modal -->
  <dialog id="client-modal" style="max-width:720px;border-radius:14px;border:1px solid #e5e7eb;">
    <form id="client-form" method="POST" action="{{ route('clients.store') }}">
      @csrf
      <input type="hidden" name="id" />

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <div>
          <div style="font-weight:700" id="client-title">Add client</div>
          <div class="muted" id="client-subtitle"></div>
        </div>
        <button type="button" class="btn light" id="client-close">Close</button>
      </div>

      <div class="grid-2" style="gap:10px">
        <div class="field">
          <label>Name</label>
          <input class="input" name="name" />
        </div>
        <div class="field">
          <label>Surname</label>
          <input class="input" name="surname" />
        </div>

        <div class="field">
          <label>Email</label>
          <input class="input" name="email" />
        </div>
        <div class="field">
          <label>Phone</label>
          <input class="input" name="phone" />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Address</label>
          <input class="input" name="address" />
        </div>

        <div class="field" style="grid-column:1/-1">
          <label>Notes</label>
          <textarea class="input" name="notes" rows="3"></textarea>
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
  </style>

  <script>
    (function(){
      const dlg = document.getElementById('client-modal');
      const form = document.getElementById('client-form');
      const closeBtn = document.getElementById('client-close');
      const title = document.getElementById('client-title');

      function set(name, val){
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.value = val ?? '';
      }
      function openAdd(){
        title.textContent = 'Add client';
        set('id', ''); set('name',''); set('surname',''); set('email',''); set('phone',''); set('address',''); set('notes','');
        dlg.showModal();
      }
      function openEdit(d){
        title.textContent = 'Edit client';
        set('id', d.id); set('name', d.name); set('surname', d.surname); set('email', d.email);
        set('phone', d.phone); set('address', d.address); set('notes', d.notes);
        dlg.showModal();
      }

      document.getElementById('btn-add').addEventListener('click', openAdd);
      document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => openEdit({
          id: btn.dataset.id,
          name: btn.dataset.name,
          surname: btn.dataset.surname,
          email: btn.dataset.email,
          phone: btn.dataset.phone,
          address: btn.dataset.address,
          notes: btn.dataset.notes,
        }));
      });
      closeBtn.addEventListener('click', () => dlg.close());
    })();
  </script>
@endsection
