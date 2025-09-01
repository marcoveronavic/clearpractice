@extends('layouts.app')

@section('title', 'Clients')

@section('head')
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #e5e7eb; text-align: left; }
        .actions form { display: inline; }
    </style>
@endsection

@section('content')
    <h1>Clients</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash err">@foreach ($errors->all() as $e) {{ $e }}<br>@endforeach</div>
    @endif

    @php
        // $practice is shared by EnsurePracticeAccess; fall back to route param if needed
        $ws   = $practice ?? request()->route('practice');
        $slug = $ws instanceof \App\Models\Practice ? $ws->slug : $ws;
    @endphp

    {{-- Simple stub form (server route currently returns “not implemented yet”) --}}
    <form method="POST"
          action="{{ route('practice.clients.store', ['practice' => $slug]) }}"
          class="card"
          style="max-width:720px;margin-bottom:16px">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end">
            <div>
                <label class="muted">Client name</label>
                <input type="text" name="name" placeholder="e.g. Jane Smith">
            </div>
            <div>
                <label class="muted">Email</label>
                <input type="email" name="email" placeholder="jane@example.com">
            </div>
            <div style="grid-column:1 / span 2">
                <button class="btn primary" type="submit">Add client</button>
            </div>
        </div>
    </form>

    <div class="card">
        @if(!empty($clients) && count($clients))
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($clients as $client)
                    <tr>
                        <td>{{ $client->name ?? '-' }}</td>
                        <td>{{ $client->email ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="muted">No clients yet.</p>
        @endif
    </div>
@endsection

