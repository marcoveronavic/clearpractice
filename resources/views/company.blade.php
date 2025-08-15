<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>{{ $company['name'] ?? 'Company' }} · Companies House</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      :root { --border:#e5e7eb; --muted:#6b7280; --bg:#f9fafb; }
      * { box-sizing: border-box; }
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; line-height: 1.5; color: #111827; background:#fff; }
      a { color: #2563eb; text-decoration: none; }
      a:hover { text-decoration: underline; }
      .container { max-width: 980px; margin: 0 auto; }
      .crumbs { margin-bottom: 12px; color: var(--muted); }
      .card { background: white; border: 1px solid var(--border); border-radius: 12px; padding: 18px; }
      h1 { margin: 4px 0 6px; font-size: 28px; }
      h2 { margin: 18px 0 8px; font-size: 20px; }
      .muted { color: var(--muted); }
      .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-top: 12px; }
      .field { border: 1px solid var(--border); border-radius: 10px; padding: 12px; background: #fff; }
      .label { font-size: 12px; letter-spacing: .02em; text-transform: uppercase; color: var(--muted); }
      .value { font-size: 16px; margin-top: 4px; }
      .address { margin-top: 14px; padding: 12px; border: 1px dashed var(--border); border-radius: 10px; background: var(--bg); }
      .actions { margin-top: 16px; display: flex; gap: 10px; flex-wrap: wrap; }
      .btn { display: inline-block; padding: 10px 14px; border-radius: 10px; border: 1px solid var(--border); background: #fff; cursor:pointer; }
      .btn.primary { background: #111827; color: #fff; border-color: #111827; }
      .btn.small { padding: 6px 10px; font-size: 13px; }
      .empty { color: var(--muted); font-style: italic; }

      .list { margin-top: 10px; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
      .row { display: grid; gap: 10px; padding: 12px; border-top: 1px solid var(--border); }
      .row:first-child { border-top: 0; }
      .head { background: var(--bg); font-weight: 600; }

      /* Directors table has an Action column (button) */
      .list-directors .row { grid-template-columns: 1.5fr 1fr 1fr 1fr auto; align-items: center; }
      .list-psc .row { grid-template-columns: 1.5fr 1fr 1fr 1fr; }

      .pill { display:inline-block; font-size:12px; border:1px solid var(--border); border-radius:999px; padding:2px 8px; margin:2px 6px 2px 0; background:#fff; }
      .warn { color:#b00020; }
      .muted-line { color: var(--muted); }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="crumbs">
        <a href="{{ url('/ch') }}">← Back to search</a>
      </div>

      <div class="card">
        <h1>{{ $company['name'] ?? '-' }}</h1>
        <div class="muted">
          Company number: <strong>{{ $company['number'] ?? '-' }}</strong>
        </div>

        <div class="grid">
          <div class="field">
            <div class="label">Status</div>
            <div class="value">{{ $company['status'] ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Type</div>
            <div class="value">{{ $company['type'] ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Jurisdiction</div>
            <div class="value">{{ $company['jurisdiction'] ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Date of creation</div>
            <div class="value">{{ $company['created'] ?? '-' }}</div>
          </div>
        </div>

        <div class="address">
          <div class="label">Registered office address</div>
          <div class="value">
            @if (!empty($company['address']))
              {{ $company['address'] }}
            @else
              <span class="empty">Not available</span>
            @endif
          </div>
        </div>

        <div style="margin-top:14px;">
          <div class="label">SIC codes</div>
          @if (!empty($company['sic_codes']))
            <div>
              @foreach ($company['sic_codes'] as $code)
                <span class="pill">{{ $code }}</span>
              @endforeach
            </div>
          @else
            <div class="empty">None listed</div>
          @endif
        </div>

        <div class="actions">
          <a class="btn" href="{{ url('/ch') }}">← Back to search</a>
          <a class="btn primary" href="{{ $company['links']['companies_house'] }}" target="_blank" rel="noopener">Open on Companies House ↗</a>
          <a class="btn" href="{{ $company['links']['officers'] }}" target="_blank" rel="noopener">View officers on CH ↗</a>
          <a class="btn" href="{{ $company['links']['psc'] }}" target="_blank" rel="noopener">View PSC on CH ↗</a>
        </div>
      </div>

      {{-- Directors --}}
      <div class="card" style="margin-top:20px;">
        <h2>Directors</h2>
        @isset($errors['officers'])
          <div class="warn">Couldn’t load officers: {{ $errors['officers'] }}</div>
        @endisset

        @if (!empty($directors))
          <div class="list list-directors">
            <div class="row head">
              <div>Name</div>
              <div>Appointed</div>
              <div>Nationality</div>
              <div>Residence</div>
              <div>Action</div>
            </div>

            @foreach ($directors as $d)
              <div class="row">
                <div>
                  <div><strong>{{ $d['name'] ?? '-' }}</strong></div>
                  <div class="muted-line">
                    {{ $d['occupation'] ? $d['occupation'].' • ' : '' }}
                    {{ $d['dob'] ? 'DOB: '.$d['dob'].' • ' : '' }}
                    {{ $d['address'] ?? '' }}
                  </div>
                </div>
                <div>{{ $d['appointed_on'] ?? '-' }}</div>
                <div>{{ $d['nationality'] ?? '-' }}</div>
                <div>{{ $d['country_of_residence'] ?? '-' }}</div>

                {{-- Add to Clients (POST -> clients.store) --}}
                <div>
                  <form action="{{ route('clients.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="{{ $d['name'] ?? '' }}">
                    <input type="hidden" name="number" value="{{ $company['number'] ?? '' }}">
                    <input type="hidden" name="status" value="prospect">
                    <input type="hidden" name="address" value="{{ $d['address'] ?? '' }}">
                    <input type="hidden" name="company_name" value="{{ $company['name'] ?? '' }}">
                    <input type="hidden" name="notes" value="Director of {{ $company['name'] ?? '' }} ({{ $company['number'] ?? '' }}). Appointed {{ $d['appointed_on'] ?? '-' }}. Nationality {{ $d['nationality'] ?? '-' }}, Residence {{ $d['country_of_residence'] ?? '-' }}{{ $d['dob'] ? ', DOB '.$d['dob'] : '' }}.">
                    <button type="submit" class="btn small">Add to Clients</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="empty">No active directors listed.</div>
        @endif
      </div>

      {{-- PSC --}}
      <div class="card" style="margin-top:20px;">
        <h2>People with Significant Control (PSC)</h2>
        @isset($errors['psc'])
          <div class="warn">Couldn’t load PSC: {{ $errors['psc'] }}</div>
        @endisset

        @if (!empty($pscs))
          <div class="list list-psc">
            <div class="row head">
              <div>Name</div>
              <div>Kind</div>
              <div>Notified</div>
              <div>Ceased</div>
            </div>
            @foreach ($pscs as $p)
              <div class="row">
                <div>
                  <div><strong>{{ $p['name'] ?? '-' }}</strong></div>
                  <div class="muted-line">{{ $p['address'] ?? '' }}</div>
                  @if (!empty($p['natures']))
                    <div class="label" style="margin-top:6px;">Natures of control</div>
                    <ul style="margin: 6px 0 0 18px;">
                      @foreach ($p['natures'] as $n)
                        <li>{{ $n }}</li>
                      @endforeach
                    </ul>
                  @endif
                </div>
                <div>{{ $p['kind'] ?? '-' }}</div>
                <div>{{ $p['notified_on'] ?? '-' }}</div>
                <div>{{ $p['ceased_on'] ?? '-' }}</div>
              </div>
            @endforeach
          </div>
        @else
          <div class="empty">No PSC records listed.</div>
        @endif
      </div>
    </div>
  </body>
</html>
