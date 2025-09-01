{{-- resources/views/deadlines.blade.php --}}
@extends('layouts.app')

@section('title', 'Deadlines')

@section('head')
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #e5e7eb; text-align: left; }
        .actions form { display: inline; }
    </style>
@endsection

@section('content')
    <h1>Deadlines</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash err">@foreach ($errors->all() as $e) {{ $e }}<br>@endforeach</div>
    @endif

    @php
        // $practice is shared by EnsurePracticeAccess; fall back to the route param if needed
        $ws   = $practice ?? request()->route('practice');
        $slug = $ws instanceof \App\Models\Practice ? $ws->slug : $ws;
    @endphp

    {{-- Toolbar --}}
    <div style="margin-bottom:12px; display:flex; gap:8px">
        {{-- Refresh all deadlines --}}
        <form method="POST" action="{{ route('practice.deadlines.refreshAll', ['practice' => $slug]) }}">
            @csrf
            <button class="btn" type="submit">Refresh all deadlines</button>
        </form>
    </div>

    {{-- (Stub) Add a manual deadline --}}
    <form method="POST"
          action="{{ route('practice.deadlines.store', ['practice' => $slug]) }}"
          class="card"
          style="max-width:720px;margin-bottom:16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;align-items:end">
        @csrf

        <div>
            <label class="muted">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Confirmation Statement">
        </div>

        <div>
            <label class="muted">Due date</label>
            <input type="date" name="due_date" value="{{ old('due_date') }}">
        </div>

        <div>
            <button class="btn primary" type="submit">Add deadline</button>
        </div>
    </form>

    {{-- Existing deadlines (stub data supported) --}}
    <div class="card">
        @php
            // Ensure we have arrays to iterate (supports your current stub routes)
            $deadlines = $deadlines ?? [];
        @endphp

        @if (!empty($deadlines))
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Due</th>
                    <th class="actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($deadlines as $d)
                    @php
                        // Support both array and object shapes in case of stubbed data
                        $id    = is_array($d) ? ($d['id'] ?? null) : ($d->id ?? null);
                        $title = is_array($d) ? ($d['title'] ?? '-') : ($d->title ?? '-');
                        $due   = is_array($d) ? ($d['due_date'] ?? '-') : ($d->due_date ?? '-');
                    @endphp
                    <tr>
                        <td>{{ $title }}</td>
                        <td>{{ $due }}</td>
                        <td class="actions">
                            @if($id)
                                <form method="POST"
                                      action="{{ route('practice.deadlines.destroy', ['practice' => $slug, 'id' => $id]) }}"
                                      onsubmit="return confirm('Delete this deadline?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn">Delete</button>
                                </form>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">No deadlines yet.</p>
        @endif
    </div>
@endsection
