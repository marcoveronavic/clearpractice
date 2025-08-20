@extends('layouts.minimal')
@section('title','Register')
@section('content')
<h1>Create account</h1>

@if ($errors->any())
    <div class="error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('register.post') }}">
    @csrf
    <div>
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Confirm password</label>
        <input type="password" name="password_confirmation" required>
    </div>
    <div>
        <button class="primary" type="submit">Create account</button>
    </div>
</form>

<p class="muted">Already have an account? <a href="{{ route('login') }}">Login</a>.</p>
@endsection
