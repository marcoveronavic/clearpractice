{{-- resources/views/practices/index.blade.php --}}
@extends('layouts.app')

@section('title','Practices')

@section('content')
    <h1>Practices</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    <div style="margin:10px 0">
        <a class="btn primary" href="{{ route('practices.create') }}">+ Create practice</a>
    </div>

    <div class="card">
        @if ($practices->count())
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th style="width:160px">Role</th>
                    <th style="width:160px">Created</th>
                    <th style="width:160px">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($practices as $p)
                    @php
                        // Determine role: owner if owner_id matches; otherwise from pivot when present
                        $role = auth()->id() === $p->owner_id
                          ? 'owner'
                          : optional($p->members->firstWhere('id', auth()->id()))?->pivot?->role;
                    @endphp
                    <tr>
                        <td><a href="{{ route('practices.show', $p) }}"><strong>{{ $p->name }}</strong></a><br>
                            <span class="muted"><code>{{ $p->slug }}</code></span></td>
                        <td class="muted">{{ $role ?: 'member' }}</td>
                        <td class="muted">{{ optional($p->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                        <td>
                            <a class="btn" href="{{ route('practices.show', $p) }}">Open</a>
                            @if (auth()->id() === $p->owner_id)
                                <a class="btn" href="{{ route('practices.edit', $p) }}">Edit</a>
                                <form action="{{ route('practices.destroy', $p) }}" method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete this practice?');">
                                    @csrf @method('DELETE')
                                    <button class="btn danger" type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="margin-top:10px">{{ $practices->links() }}</div>
        @else
            <p class="muted">No practices yet.</p>
        @endif
    </div>
@endsection
