@extends('layout')

@section('title', 'Deadlines')

@section('content')
    {{-- Due pill styles (scoped to this page so it always works) --}}
    <style>
        .due-pill{display:inline-block;padding:3px 10px;border-radius:9999px;font-size:13px;line-height:1.35;font-weight:600;border:1px solid}
        .due-late   {background:#fee2e2;color:#991b1b;border-color:#fecaca}
        .due-60     {background:#ffcc80;color:#7c2d12;border-color:#ffb74d}
        .due-neutral{background:#eef2f7;color:#334155;border-color:#cbd5e1}
    </style>

    <div class="card" style="margin-bottom:14px">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap">
            <form method="POST" action="{{ route('practice.deadlines.refreshAll', $practice->slug) }}">
                @csrf
                <button class="btn" type="submit">Refresh all deadlines</button>
            </form>

            <form method="POST" action="{{ route('practice.deadlines.store', $practice->slug) }}" style="margin-left:auto; display:flex; gap:8px; align-items:center">
                @csrf
                <label style="display:flex; align-items:center; gap:6px">
                    <span class="muted">Title</span>
                    <input type="text" name="title" placeholder="e.g. Confirmation Statement" style="padding:6px 8px; border:1px solid #e5e7eb; border-radius:6px; width:260px">
                </label>

                <label style="display:flex; align-items:center; gap:6px">
                    <span class="muted">Due date</span>
                    <input type="date" name="due_date" placeholder="dd/mm/yyyy" style="padding:6px 8px; border:1px solid #e5e7eb; border-radius:6px">
                </label>

                <button class="btn primary" type="submit">Add deadline</button>
            </form>
        </div>
    </div>

    @php
        $fmtDate = function (?string $d) {
            if (!$d) return '—';
            try { return \Carbon\Carbon::parse($d)->format('d/m/Y'); } catch (\Throwable $e) { return $d; }
        };

        $fmtDue = function (?string $dueOn) use ($fmtDate) {
            if (!$dueOn) return '—';
            $due = \Carbon\Carbon::parse($dueOn)->startOfDay();
            $today = now()->startOfDay();
            $diff = $today->diffInDays($due, false);
            $label = $fmtDate($dueOn);
            if     ($diff > 0)  $label .= ' (in '.$diff.' days)';
            elseif ($diff === 0) $label .= ' (today)';
            else                  $label .= ' ('.abs($diff).' days late)';
            return $label;
        };

        // NEW: resolve the CSS class for the due pill
        $dueClass = function (?string $dueOn) {
            if (!$dueOn) return 'due-neutral';
            try {
                $due   = \Carbon\Carbon::parse($dueOn)->startOfDay();
                $today = now()->startOfDay();
                $diff  = $today->diffInDays($due, false); // negative if late
                if ($diff < 0)  return 'due-late';
                if ($diff <= 60) return 'due-60';
                return 'due-neutral';
            } catch (\Throwable $e) {
                return 'due-neutral';
            }
        };

        // Use buckets prepared in the route; if missing, derive from $deadlines.
        $accountsList = collect($accounts ?? []);
        $confirmList  = collect($confirmations ?? []);
        if ($accountsList->isEmpty() || $confirmList->isEmpty()) {
            $all = collect($deadlines ?? []);
            $accountsList = $all->where('type','accounts')->values();
            $confirmList  = $all->where('type','confirmation_statement')->values();
        }
    @endphp

    {{-- ACCOUNTS --}}
    <div class="card" style="margin-bottom:14px">
        <h3 style="margin:0 0 10px 0">Accounts — next deadlines</h3>
        <table>
            <thead>
            <tr>
                <th style="width:50%">Title</th>
                <th style="width:20%">Year end</th>
                <th style="width:20%">Due</th>
                <th style="width:10%">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($accountsList as $d)
                @php
                    $title   = $d->title ?? ('Accounts — '.($d->company_name ?? ''));
                    $yearEnd = $d->year_end ?? $fmtDate($d->period_end_on ?? null);
                    $dueRaw  = $d->due_on ?? null;
                    $dueLbl  = $d->display_due ?? $d->due ?? $fmtDue($dueRaw);
                    $dueCls  = $dueClass($dueRaw);
                @endphp
                <tr>
                    <td>{{ $title }}</td>
                    <td>{{ $yearEnd }}</td>
                    <td><span class="due-pill {{ $dueCls }}">{{ $dueLbl }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('practice.deadlines.destroy', ['practice' => $practice->slug, 'id' => $d->id]) }}">
                            @csrf @method('DELETE')
                            <button class="btn" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No accounts deadlines yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- CONFIRMATION STATEMENTS --}}
    <div class="card">
        <h3 style="margin:0 0 10px 0">Confirmation statements — next deadlines</h3>
        <table>
            <thead>
            <tr>
                <th style="width:50%">Title</th>
                <th style="width:20%">Year end</th>
                <th style="width:20%">Due</th>
                <th style="width:10%">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($confirmList as $d)
                @php
                    $title   = $d->title ?? ('Confirmation statement — '.($d->company_name ?? ''));
                    $yearEnd = $d->year_end ?? $fmtDate($d->period_end_on ?? null);
                    $dueRaw  = $d->due_on ?? null;
                    $dueLbl  = $d->display_due ?? $d->due ?? $fmtDue($dueRaw);
                    $dueCls  = $dueClass($dueRaw);
                @endphp
                <tr>
                    <td>{{ $title }}</td>
                    <td>{{ $yearEnd }}</td>
                    <td><span class="due-pill {{ $dueCls }}">{{ $dueLbl }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('practice.deadlines.destroy', ['practice' => $practice->slug, 'id' => $d->id]) }}">
                            @csrf @method('DELETE')
                            <button class="btn" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No confirmation statement deadlines yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
