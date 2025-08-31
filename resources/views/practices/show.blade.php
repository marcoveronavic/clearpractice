{{-- resources/views/practices/show.blade.php --}}
@extends('layouts.app')

@section('title', $practice->name . ' — Practice')

@section('content')
    <h1>{{ $practice->name }}</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    <div class="card" style="max-width:760px">
        <table>
            <tbody>
            <tr>
                <th style="width:160px">Owner</th>
                <td>{{ optional($practice->owner)->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Slug</th>
                <td><code>{{ $practice->slug }}</code></td>
            </tr>
            <tr>
                <th>Created</th>
                <td class="muted">
                    {{ optional($practice->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px; display:flex; gap:8px">
        <a class="btn" href="{{ route('practices.index') }}">← All practices</a>
        <a class="btn" href="{{ route('users.index') }}">Manage users</a>
        <a class="btn" href="{{ route('tasks.index') }}">Tasks</a>
    </div>
@endsection
