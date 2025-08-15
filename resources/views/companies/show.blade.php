<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Company • {{ $company->name ?? $company->number }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root{
    --fg:#111827; --muted:#6b7280; --line:#e5e7eb; --bg:#f9fafb;
    --card:#ffffff; --accent:#111827; --badge:#f3f4f6;
  }
  *{box-sizing:border-box}
  body{margin:0; font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:var(--fg); background:var(--bg)}
  .wrap{max-width:1100px; margin:24px auto; padding:0 16px}
  .toolbar{display:flex; gap:12px; align-items:center; margin-bottom:14px}
  .btn{display:inline-block; padding:8px 12px; border:1px solid var(--line); border-radius:10px; background:#fff; color:#111; text-decoration:none}
  .btn:hover{border-color:#cbd5e1}
  .title{font-size:28px; font-weight:700; margin:6px 0 2px}
  .muted{color:var(--muted); font-size:14px}
  .grid{display:grid; gap:16px; grid-template-columns: 1fr}
  @media (min-width: 960px){ .grid{grid-template-columns: 1.2fr 1fr} }
  .card{background:var(--card); border:1px solid var(--line); border-radius:12px; padding:14px}
  .card h3{margin:0 0 12px; font-size:18px}
  .row{display:grid; grid-template-columns:180px 1fr; gap:8px; padding:8px 0; border-top:1px solid var(--line)}
  .row:first-child{border-top:none}
  .badge{display:inline-block; padding:2px 8px; background:var(--badge); border-radius:999px; font-size:12px; color:#374151}
  .table{width:100%; border-collapse:collapse}
  .table th,.table td{padding:8px 10px; border-top:1px solid var(--line); text-align:left; vertical-align:top}
  .table th{font-size:12px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.02em}
  .pill{display:inline-block; font-size:12px; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; margin-right:6px; margin-bottom:6px}
  .two{display:grid; gap:16px}
  @media (min-width: 960px){ .two{grid-template-columns:1fr 1fr} }
  .hint{color:var(--muted); font-size:13px; margin-top:6px}
</style>
</head>
<body>
<div class="wrap">

  <div class="toolbar">
    <a class="btn" href="/companies">← Back to companies</a>
    <a class="btn" target="_blank" rel="noopener"
       href="https://find-and-update.company-information.service.gov.uk/company/{{ urlencode($company->number) }}">
      Open on Companies House ↗
    </a>
  </div>

  <div class="title">{{ $company->name ?? '—' }}</div>
  <div class="muted">
    <span class="badge">Number {{ $company->number }}</span>
    @if(!empty($company->status))
      <span class="badge">{{ $company->status }}</span>
    @endif
    @if(!empty($company->type))
      <span class="badge">{{ $company->type }}</span>
    @endif
  </div>

  <div class="grid" style="margin-top:14px">
    <!-- Left column: summary -->
    <div class="card">
      <h3>Summary</h3>

      <div class="row">
        <div class="muted">Incorporated</div>
        <div>{{ $company->created ?: '—' }}</div>
      </div>

      <div class="row">
        <div class="muted">Registered address</div>
        <div>{{ $company->address ?: '—' }}</div>
      </div>

      <div class="row">
        <div class="muted">SIC codes</div>
        <div>
          @php $sic = is_array($company->sic_codes) ? $company->sic_codes : (empty($company->sic_codes)?[]:((is_string($company->sic_codes))?json_decode($company->sic_codes,true):[])); @endphp
          @if(!empty($sic))
            @foreach($sic as $code)
              <span class="pill">{{ $code }}</span>
            @endforeach
          @else
            —
          @endif
        </div>
      </div>

      <div class="row">
        <div class="muted">Accounts</div>
        <div>
          @php
            $acc = $accounts ?? [];
            $last = $acc['last_accounts'] ?? [];
          @endphp
          <div>Next due: <strong>{{ $acc['next_due'] ?? '—' }}</strong></div>
          <div>Next made up to: <strong>{{ $acc['next_made_up_to'] ?? '—' }}</strong></div>
          <div>Overdue: <strong>{{ !empty($acc['overdue']) ? 'true' : 'false' }}</strong></div>
          <div class="hint">Last accounts: {{ $last ? json_encode($last) : '—' }}</div>
        </div>
      </div>

      <div class="row">
        <div class="muted">Confirmation statement</div>
        <div>
          @php $cs = $confirmation ?? []; @endphp
          <div>Next due: <strong>{{ $cs['next_due'] ?? '—' }}</strong></div>
          <div>Next made up to: <strong>{{ $cs['next_made_up_to'] ?? '—' }}</strong></div>
          <div>Overdue: <strong>{{ !empty($cs['overdue']) ? 'true' : 'false' }}</strong></div>
        </div>
      </div>
    </div>

    <!-- Right column: PSC + contact (if you’re storing these fields locally) -->
    <div class="card">
      <h3>Contact & VAT (local fields)</h3>
      <div class="row">
        <div class="muted">Email</div>
        <div>{{ $company->email ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">Telephone</div>
        <div>{{ $company->telephone ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">VAT Reg. number</div>
        <div>{{ $company->vat_number ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">Authentication code</div>
        <div>{{ $company->authentication_code ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">UTR</div>
        <div>{{ $company->utr ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">VAT period</div>
        <div>{{ $company->vat_period ?: '—' }}</div>
      </div>
      <div class="row">
        <div class="muted">VAT quarter end</div>
        <div>{{ $company->vat_quarter_group ?: '—' }}</div>
      </div>
    </div>
  </div>

  <!-- Directors / Officers + PSCs -->
  <div class="two" style="margin-top:16px">

    <div class="card">
      <h3>Officers — Active</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th><th>Role</th><th>Appointed</th><th>Occupation</th><th>Nationality</th><th>Country</th>
          </tr>
        </thead>
        <tbody>
        @forelse($officersActive ?? [] as $o)
          <tr>
            <td>{{ $o['name'] ?? '—' }}</td>
            <td>{{ $o['role'] ?? '—' }}</td>
            <td>{{ $o['appointed'] ?? '—' }}</td>
            <td>{{ $o['occupation'] ?? '—' }}</td>
            <td>{{ $o['nationality'] ?? '—' }}</td>
            <td>{{ $o['country'] ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">No active officers.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Officers — Resigned</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th><th>Role</th><th>Appointed</th><th>Resigned</th><th>Occupation</th><th>Nationality</th>
          </tr>
        </thead>
        <tbody>
        @forelse($officersResigned ?? [] as $o)
          <tr>
            <td>{{ $o['name'] ?? '—' }}</td>
            <td>{{ $o['role'] ?? '—' }}</td>
            <td>{{ $o['appointed'] ?? '—' }}</td>
            <td>{{ $o['resigned'] ?? '—' }}</td>
            <td>{{ $o['occupation'] ?? '—' }}</td>
            <td>{{ $o['nationality'] ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">No resigned officers.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

  </div>

  <div class="two" style="margin-top:16px">
    <div class="card">
      <h3>Persons with Significant Control — Current</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th><th>Kind</th><th>Notified</th><th>Ceased</th><th>Country</th><th>Control</th>
          </tr>
        </thead>
        <tbody>
        @forelse($pscsCurrent ?? [] as $p)
          <tr>
            <td>{{ $p['name'] ?? '—' }}</td>
            <td>{{ $p['kind'] ?? '—' }}</td>
            <td>{{ $p['notified_on'] ?? '—' }}</td>
            <td>{{ $p['ceased_on'] ?? '—' }}</td>
            <td>{{ $p['country'] ?? '—' }}</td>
            <td>
              @php $ctrl = $p['control'] ?? []; if(!is_array($ctrl)) $ctrl = [$ctrl]; @endphp
              @if(count($ctrl))
                @foreach($ctrl as $c) <span class="pill">{{ $c }}</span> @endforeach
              @else — @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">No current PSCs.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3>Persons with Significant Control — Former</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th><th>Kind</th><th>Notified</th><th>Ceased</th><th>Country</th><th>Control</th>
          </tr>
        </thead>
        <tbody>
        @forelse($pscsFormer ?? [] as $p)
          <tr>
            <td>{{ $p['name'] ?? '—' }}</td>
            <td>{{ $p['kind'] ?? '—' }}</td>
            <td>{{ $p['notified_on'] ?? '—' }}</td>
            <td>{{ $p['ceased_on'] ?? '—' }}</td>
            <td>{{ $p['country'] ?? '—' }}</td>
            <td>
              @php $ctrl = $p['control'] ?? []; if(!is_array($ctrl)) $ctrl = [$ctrl]; @endphp
              @if(count($ctrl))
                @foreach($ctrl as $c) <span class="pill">{{ $c }}</span> @endforeach
              @else — @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">No former PSCs.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
