@extends('layouts.minimal')
@section('title','Login')
@section('content')
<h1>Login</h1>

@if ($errors->any())
    <div class="error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('login.post') }}">
    @csrf
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div class="row" style="align-items:center">
        <label style="display:flex; gap:8px; align-items:center">
            <input type="checkbox" name="remember"> Remember me
        </label>
    </div>
    <div>
        <button class="primary" type="submit">Login</button>
    </div>
</form>

<p class="muted">No account? <a href="{{ route('register') }}">Create one</a>.</p>
@endsection
