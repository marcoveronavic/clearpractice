@extends('layouts.app')

@section('content')
  <h1>Deadlines</h1>

  @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
  @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

  @php
    $fmt = function($d){
      if (empty($d)) return '—';
      try {
        if (strpos($d, '-') !== false) {
          return \Illuminate\Support\Carbon::parse($d)->format('d/m/Y');
        }
        return $d;
      } catch (\Throwable $e) { return $d; }
    };
    $daysLeft = function($d){
      $neutral = 'background:#e5e7eb;color:#374151';
      if (empty($d)) return [null, $neutral];
      try {
        $due = strpos($d, '-') !== false
          ? \Illuminate\Support\Carbon::parse($d)->startOfDay()
          : \Illuminate\Support\Carbon::createFromFormat('d/m/Y', $d)->startOfDay();
      } catch (\Throwable $e) {
        return [null, $neutral];
      }
      $today = \Illuminate\Support\Carbon::now()->startOfDay();
      $diff  = $today->diffInDays($due, false);
      if     ($diff < 0)  $style = 'background:#fee2e2;color:#991b1b';
      elseif ($diff <=30) $style = 'background:#fb923c;color:#ffffff';
      elseif ($diff <=90) $style = 'background:#fef08a;color:#854d0e';
      else                $style = 'background:#bbf7d0;color:#065f46';
      return [$diff, $style];
    };
    $coNames = [];
    foreach (($companies ?? []) as $c) {
      $num = $c['number'] ?? '';
      if ($num !== '') $coNames[$num] = $c['name'] ?? '';
    }
  @endphp

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
      <strong>Company deadlines (auto da Companies House)</strong>
      <form action="{{ route('deadlines.refreshAll') }}" method="POST">
        @csrf
        <button class="btn" type="submit">Aggiorna da CH</button>
      </form>
    </div>
    <div class="muted" style="margin-bottom:8px">
      Le colonne <strong>Accounts</strong> e <strong>Confirmation statement</strong> arrivano da CH.
      La colonna <strong>VAT</strong> non è disponibile via CH (serve API HMRC).
    </div>

    <table>
      <thead>
        <tr>
          <th style="width:120px">Number</th>
          <th>Name</th>
          <th style="width:160px">Accounts deadline</th>
          <th style="width:220px">Confirmation statement<br>deadline</th>
          <th style="width:140px">VAT deadline</th>
          <th style="width:120px">Fetched</th>
          <th style="width:80px">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($auto as $row)
          <tr>
            <td>{{ $row['number'] }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['accounts_due'] ?? '—' }}</td>
            <td>{{ $row['cs_due'] ?? '—' }}</td>
            <td>{{ $row['vat_due'] ?? '—' }}</td>
            <td class="muted">{{ $row['fetched_at'] ?? '—' }}</td>
            <td>
              @if (($row['status'] ?? '') === 'OK')
                <span class="pill">OK</span>
              @elseif (($row['status'] ?? '') === 'ERR')
                <span class="pill" style="background:#fee2e2;color:#991b1b">ERR</span>
              @else
                <span class="pill" style="background:#e5e7eb;color:#374151">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted">Nessuna company salvata.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card" style="margin-top:16px">
    <strong>Manual deadlines</strong>

    <form action="{{ route('deadlines.store') }}" method="POST"
          style="display:grid;grid-template-columns:1.3fr 180px 1.5fr 1.6fr 160px auto;gap:10px;align-items:center;margin-top:8px">
      @csrf
      <input type="text"  name="title"   placeholder="Title (e.g. VAT Return Q3)">
      <input type="text"  name="due"     placeholder="dd/mm/yyyy">
      <input type="text"  name="related" placeholder="Related to (client/company)">
      <input type="text"  name="notes"   placeholder="Notes">
      <select name="status">
        <option value="Open" selected>Open</option>
        <option value="In progress">In progress</option>
        <option value="Done">Done</option>
      </select>
      <button class="btn" type="submit">Add</button>
    </form>

    <table style="margin-top:10px">
      <thead>
        <tr>
          <th>Title</th>
          <th style="width:120px">Due</th>
          <th style="width:110px">Days</th>
          <th>Related</th>
          <th style="width:140px">Status</th>
          <th style="width:160px">Added</th>
          <th style="width:120px">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($manual as $d)
          @php
            $relNum   = (string)($d['related'] ?? '');
            $relName  = $coNames[$relNum] ?? null;
            $dueRaw   = $d['due'] ?? null;
            [$diff, $style] = $daysLeft($dueRaw);
          @endphp
          <tr>
            <td>
              <strong>{{ $relName ? ($relName.' — ') : '' }}{{ $d['title'] }}</strong>
              <div class="muted">{{ $d['notes'] ?? '' }}</div>
            </td>
            <td>{{ $fmt($dueRaw) }}</td>
            <td>
              @if (is_null($diff))
                <span class="pill" style="background:#e5e7eb;color:#374151">—</span>
              @else
                <span class="pill" style="{{ $style }}">{{ $diff }}</span>
              @endif
            </td>
            <td>{{ $relName ?? ($relNum !== '' ? $relNum : '—') }}</td>
            <td>{{ $d['status'] ?? '' }}</td>
            <td class="muted">{{ $fmt($d['added'] ?? null) }}</td>
            <td>
              <form action="{{ route('deadlines.destroy', ['id' => $d['id']]) }}" method="POST"
                    onsubmit="return confirm('Delete this deadline?');">
                @csrf @method('DELETE')
                <button class="btn danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="muted">Nessuna deadline manuale.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
