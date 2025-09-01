{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')
@section('title','Users')

@section('content')
    <h1>Users</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash err">@foreach ($errors->all() as $e) {{ $e }}<br>@endforeach</div>
    @endif

    @if (session('invite_url'))
        <div class="flash info">
            Invite link:
            <a href="{{ session('invite_url') }}" target="_blank" rel="noopener">{{ session('invite_url') }}</a>
            <span class="muted"> — copy/share if email is delayed.</span>
        </div>
    @endif

    @if (!empty($practice))
        <p class="muted" style="margin-bottom:10px">
            Managing members of: <strong>{{ $practice->name }}</strong>
        </p>

        {{-- Invite user to this practice --}}
        <form id="invite-form" method="POST" action="{{ route('practice.users.store', $practice->slug) }}"
              class="card"
              style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;max-width:1200px">
            @csrf

            <div>
                <label class="muted">First name</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required>
            </div>

            <div>
                <label class="muted">Surname</label>
                <input type="text" name="surname" value="{{ old('surname') }}" required>
            </div>

            <div>
                <label class="muted">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div>
                {{-- Use type="button" + requestSubmit() to avoid nested/outer form issues --}}
                <button id="invite-submit" class="btn primary" type="button">Send invite</button>
            </div>
        </form>

        <script>
            (function () {
                var btn = document.getElementById('invite-submit');
                var form = document.getElementById('invite-form');
                if (!btn || !form) return;
                btn.addEventListener('click', function () {
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.submit();
                });
            })();
        </script>

        {{-- Members table --}}
        <div class="card" style="margin-top:12px">
            @php
                $adminCount = $members->filter(fn($u) => ($u->pivot->role ?? 'member') === 'admin')->count();
            @endphp

            @if ($members->count())
                <table>
                    <thead>
                    <tr>
                        <th style="width:30%">Name</th>
                        <th style="width:35%">Email</th>
                        <th style="width:12%">Role</th>
                        <th style="width:23%">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($members as $m)
                        @php
                            $role      = $m->pivot->role ?? 'member';
                            $isAdmin   = $role === 'admin';
                            $canRemove = !($isAdmin && $adminCount <= 1);
                        @endphp
                        <tr>
                            <td>{{ $m->name ?? '—' }}</td>
                            <td>{{ $m->email }}</td>
                            <td><span class="pill">{{ $isAdmin ? 'admin' : 'member' }}</span></td>
                            <td style="display:flex;gap:8px;align-items:center">
                                @if ($canRemove)
                                    <form method="POST" action="{{ route('practice.users.destroy', [$practice->slug, $m->id]) }}"
                                          onsubmit="return confirm('Remove this user from the practice?');">
                                        @csrf @method('DELETE')
                                        <button class="btn" type="submit">Remove</button>
                                    </form>
                                @else
                                    <span class="muted">Cannot remove last admin</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">No users yet.</p>
            @endif
        </div>

        {{-- Pending invitations --}}
        <div class="card" style="margin-top:12px">
            <h3 style="margin-top:0">Pending invitations</h3>
            @if (!empty($invites) && $invites->count())
                <table>
                    <thead>
                    <tr>
                        <th style="width:30%">Name</th>
                        <th style="width:40%">Email</th>
                        <th style="width:15%">Expires</th>
                        <th style="width:15%">Link</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($invites as $inv)
                        <tr>
                            <td>{{ trim(($inv->first_name ?? '').' '.($inv->surname ?? '')) }}</td>
                            <td>{{ $inv->email }}</td>
                            <td>{{ optional($inv->expires_at)->diffForHumans() ?? '—' }}</td>
                            <td>
                                <a href="{{ route('invites.show', $inv->token) }}" target="_blank" rel="noopener">Open</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">No pending invitations.</p>
            @endif
        </div>
    @else
        <div class="flash err">No active practice found.</div>
    @endif
@endsection
