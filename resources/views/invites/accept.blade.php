{{-- resources/views/invites/accept.blade.php --}}
@extends('layouts.app')
@section('title','Finish setup')

@section('content')
    <h1>Finish setting up your account</h1>

    @if ($errors->any())
        <div class="flash err">@foreach ($errors->all() as $e) {{ $e }}<br>@endforeach</div>
    @endif

    <p class="muted" style="margin-bottom:10px">
        You’re joining <strong>{{ $inv->practice->name }}</strong>.
    </p>

    <form method="POST" action="{{ route('invites.accept', $inv->token) }}"
          class="card"
          style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end;max-width:720px">
        @csrf

        <div>
            <label class="muted">First name</label>
            <input type="text" name="first_name" value="{{ old('first_name', $inv->first_name) }}" required>
        </div>

        <div>
            <label class="muted">Surname</label>
            <input type="text" name="surname" value="{{ old('surname', $inv->surname) }}" required>
        </div>

        <div style="grid-column:1 / span 2">
            <label class="muted">Email</label>
            <input type="email" value="{{ $inv->email }}" readonly>
        </div>

        <div>
            <label class="muted">Password</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label class="muted">Confirm password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <div style="grid-column:1 / span 2;margin-top:6px">
            <button class="btn primary" type="submit">Create my account</button>
        </div>
    </form>
@endsection
