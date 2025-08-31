@extends('layouts.app')

@section('title', $practice->name)

@section('content')
<h1>{{ $practice->name }}</h1>

<div class="card" style="max-width:720px">
    <p><strong>Slug:</strong> {{ $practice->slug }}</p>
    <p><strong>Owner ID:</strong> {{ $practice->owner_id }}</p>
</div>

<p style="margin-top:12px">
    <a class="btn" href="{{ route('practices.index') }}">Back to practices</a>
</p>
@endsection
