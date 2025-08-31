{{-- resources/views/account.blade.php --}}
@extends('layouts.app')
@section('title','Account')

@section('content')
    <h1>My account</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash err">
            @foreach ($errors->all() as $e) {{ $e }}<br>@endforeach
        </div>
    @endif

    {{-- Account details / password change --}}
    <div class="card" style="max-width:720px">
        <form method="POST" action="{{ route('account.update') }}"
              style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            @csrf
            @method('PATCH')

            <div style="grid-column:1/-1">
                <label class="muted">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div style="grid-column:1/-1">
                <label class="muted">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div>
                <label class="muted">New password (optional)</label>
                <input type="password" name="password" autocomplete="new-password">
            </div>

            <div>
                <label class="muted">Confirm password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </div>

            <div style="grid-column:1/-1; display:flex; gap:8px; justify-content:flex-end">
                <button class="btn primary" type="submit">Save changes</button>
            </div>
        </form>

        {{-- Email verification helper (separate form; not nested) --}}
        @if (! $user->hasVerifiedEmail())
            <div class="muted" style="margin-top:10px">
                Email not verified.
                <form method="POST" action="{{ route('verification.send') }}" style="display:inline">
                    @csrf
                    <button class="btn" type="submit">Resend verification</button>
                </form>
            </div>
        @endif

        {{-- Logout (separate POST form; not nested) --}}
        <form method="POST" action="{{ route('logout') }}" style="margin-top:10px">
            @csrf
            <button class="btn" type="submit">Log out</button>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="card" style="max-width:720px; margin-top:14px">
        <strong>Danger zone</strong>
        <p class="muted" style="margin-top:8px">
            Deleting your account will remove your access. Practices you own will be deleted (canvas mode).
        </p>
        <form method="POST" action="{{ route('account.destroy') }}"
              onsubmit="return confirm('Delete your account? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button class="btn danger" type="submit">Delete account</button>
        </form>
    </div>
@endsection
