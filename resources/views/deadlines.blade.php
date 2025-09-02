@extends('layout')

@section('title', 'Deadlines')

@section('content')
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
                    $due     = $d->display_due ?? $d->due ?? $fmtDue($d->due_on ?? null);
                @endphp
                <tr>
                    <td>{{ $title }}</td>
                    <td>{{ $yearEnd }}</td>
                    <td>{{ $due }}</td>
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
                    $due     = $d->display_due ?? $d->due ?? $fmtDue($d->due_on ?? null);
                @endphp
                <tr>
                    <td>{{ $title }}</td>
                    <td>{{ $yearEnd }}</td>
                    <td>{{ $due }}</td>
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
