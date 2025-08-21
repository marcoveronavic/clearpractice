@extends('layouts.app')

@section('content')
  <h1>{{ $company['name'] ?? 'Company' }}</h1>
  <div class="muted">Company number: <strong>{{ $company['number'] ?? '-' }}</strong></div>

  <div class="card" style="margin-top:12px">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
      <div class="card" style="padding:12px"><div class="muted">Status</div><div>{{ $company['status'] ?? '-' }}</div></div>
      <div class="card" style="padding:12px"><div class="muted">Type</div><div>{{ $company['type'] ?? '-' }}</div></div>
      <div class="card" style="padding:12px"><div class="muted">Jurisdiction</div><div>{{ $company['jurisdiction'] ?? '-' }}</div></div>
      <div class="card" style="padding:12px"><div class="muted">Date of creation</div><div>{{ $company['created'] ?? '-' }}</div></div>
    </div>

    <div style="margin-top:14px;padding:12px;border:1px dashed #e5e7eb;border-radius:10px;background:#f9fafb">
      <div class="muted">Registered office address</div>
      <div>{{ $company['address'] ?? '-' }}</div>
    </div>

    <div style="margin-top:14px">
      <div class="muted">SIC codes</div>
      @if (!empty($company['sic_codes']))
        <div>@foreach ($company['sic_codes'] as $code) <span class="pill">{{ $code }}</span> @endforeach</div>
      @else
        <div class="muted">None listed</div>
      @endif
    </div>

    <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap">
      <a class="btn" href="{{ url('/ch') }}">← Back to search</a>

      <form action="{{ route('companies.store') }}" method="POST" style="display:inline">
        @csrf
        <input type="hidden" name="number" value="{{ $company['number'] ?? '' }}">
        <input type="hidden" name="name" value="{{ $company['name'] ?? '' }}">
        <input type="hidden" name="status" value="{{ $company['status'] ?? '' }}">
        <input type="hidden" name="type" value="{{ $company['type'] ?? '' }}">
        <input type="hidden" name="jurisdiction" value="{{ $company['jurisdiction'] ?? '' }}">
        <input type="hidden" name="created" value="{{ $company['created'] ?? '' }}">
        <input type="hidden" name="address" value="{{ $company['address'] ?? '' }}">
        @foreach (($company['sic_codes'] ?? []) as $code)
          <input type="hidden" name="sic_codes[]" value="{{ $code }}">
        @endforeach
        <button type="submit" class="btn primary">Add to Companies</button>
      </form>

      <a class="btn" href="{{ $company['links']['companies_house'] }}" target="_blank" rel="noopener">Open on CH ↗</a>
      <a class="btn" href="{{ $company['links']['officers'] }}" target="_blank" rel="noopener">View officers on CH ↗</a>
      <a class="btn" href="{{ $company['links']['psc'] }}" target="_blank" rel="noopener">View PSC on CH ↗</a>
    </div>
  </div>

  <div class="card" style="margin-top:20px">
    <h3 style="margin:0 0 8px">Directors</h3>
    @isset($errors['officers']) <div class="err flash">Couldn’t load officers: {{ $errors['officers'] }}</div> @endisset

    @if (!empty($directors))
      <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr auto;gap:10px;padding:12px;background:#f9fafb;font-weight:600">
          <div>Name</div><div>Appointed</div><div>Nationality</div><div>Residence</div><div>Action</div>
        </div>
        @foreach ($directors as $d)
          <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr auto;gap:10px;padding:12px;border-top:1px solid #e5e7eb">
            <div>
              <div><strong>{{ $d['name'] ?? '-' }}</strong></div>
              <div class="muted">
                {{ $d['occupation'] ? $d['occupation'].' • ' : '' }}
                {{ $d['dob'] ? 'DOB: '.$d['dob'].' • ' : '' }}
                {{ $d['address'] ?? '' }}
              </div>
            </div>
            <div>{{ $d['appointed_on'] ?? '-' }}</div>
            <div>{{ $d['nationality'] ?? '-' }}</div>
            <div>{{ $d['country_of_residence'] ?? '-' }}</div>
            <div>
              <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                <input type="hidden" name="name" value="{{ $d['name'] ?? '' }}">
                <input type="hidden" name="number" value="{{ $company['number'] ?? '' }}">
                <input type="hidden" name="status" value="prospect">
                <input type="hidden" name="address" value="{{ $d['address'] ?? '' }}">
                <input type="hidden" name="company_name" value="{{ $company['name'] ?? '' }}">
                <input type="hidden" name="notes" value="Director of {{ $company['name'] ?? '' }} ({{ $company['number'] ?? '' }}). Appointed {{ $d['appointed_on'] ?? '-' }}. Nationality {{ $d['nationality'] ?? '-' }}, Residence {{ $d['country_of_residence'] ?? '-' }}{{ $d['dob'] ? ', DOB '.$d['dob'] : '' }}.">
                <button class="btn">Add to Clients</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="muted">No active directors listed.</div>
    @endif
  </div>

  <div class="card" style="margin-top:20px">
    <h3 style="margin:0 0 8px">People with Significant Control (PSC)</h3>
    @isset($errors['psc']) <div class="err flash">Couldn’t load PSC: {{ $errors['psc'] }}</div> @endisset

    @if (!empty($pscs))
      <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:10px;padding:12px;background:#f9fafb;font-weight:600">
          <div>Name</div><div>Kind</div><div>Notified</div><div>Ceased</div>
        </div>
        @foreach ($pscs as $p)
          <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:10px;padding:12px;border-top:1px solid #e5e7eb">
            <div>
              <div><strong>{{ $p['name'] ?? '-' }}</strong></div>
              <div class="muted">{{ $p['address'] ?? '' }}</div>
              @if (!empty($p['natures']))
                <div class="muted" style="margin-top:6px">Natures of control</div>
                <ul style="margin:6px 0 0 18px">@foreach ($p['natures'] as $n) <li>{{ $n }}</li> @endforeach</ul>
              @endif
            </div>
            <div>{{ $p['kind'] ?? '-' }}</div>
            <div>{{ $p['notified_on'] ?? '-' }}</div>
            <div>{{ $p['ceased_on'] ?? '-' }}</div>
          </div>
        @endforeach
      </div>
    @else
      <div class="muted">No PSC records listed.</div>
    @endif
  </div>
@endsection
