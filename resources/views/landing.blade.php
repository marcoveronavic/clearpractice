@extends('layouts.minimal')
@section('title','ClearPractice — Simple client management for small firms')
@section('content')
<h1>ClearPractice</h1>
<p class="muted">
    ClearPractice helps you keep clients, tasks, and deadlines tidy — in one place.
    Fast search, quick data from Companies House, and clean workflows for small teams.
</p>

<div style="display:flex; gap:10px; margin:16px 0;">
    <a href="{{ route('login') }}" 
       style="display:inline-block; padding:10px 14px; background:#111827; color:#fff; border-radius:8px; text-decoration:none;">
        Login
    </a>
    <a href="{{ route('register') }}" 
       style="display:inline-block; padding:10px 14px; background:#e5e7eb; color:#111827; border-radius:8px; text-decoration:none;">
        Create an account
    </a>
</div>

<ul style="margin-top:16px; padding-left:18px;">
    <li>Companies House lookups with autofill</li>
    <li>Tasks you can assign to your team</li>
    <li>Simple CRM for companies & individuals</li>
</ul>

<p class="muted" style="margin-top:16px">
    Already inside? Go straight to <a href="{{ route('practices.create') }}">Create a practice</a>.
</p>
@endsection
