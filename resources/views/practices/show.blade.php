@extends('layouts.minimal')
@section('title', $practice->name)
@section('content')
<h1>{{ $practice->name }}</h1>

<div class="muted">Slug: {{ $practice->slug }}</div>

<div style="margin-top:12px">
    @if($practice->address_line1) <div>{{ $practice->address_line1 }}</div> @endif
    @if($practice->address_line2) <div>{{ $practice->address_line2 }}</div> @endif
    @if($practice->city || $practice->postcode)
        <div>{{ $practice->city }} {{ $practice->postcode }}</div>
    @endif
    @if($practice->country) <div>{{ $practice->country }}</div> @endif
    @if($practice->email) <div>Email: {{ $practice->email }}</div> @endif
    @if($practice->phone) <div>Phone: {{ $practice->phone }}</div> @endif
</div>

<p class="muted" style="margin-top:16px">
    <a href="{{ route('practices.create') }}">Create another practice</a>
</p>
@endsection
