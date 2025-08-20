@extends('layouts.minimal')
@section('title','Create Practice')
@section('content')
<h1>Create a practice</h1>

@if ($errors->any())
    <div class="error">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('practices.store') }}">
    @csrf
    <div>
        <label>Practice name *</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>

    <div class="row">
        <div style="flex:1">
            <label>Address line 1</label>
            <input type="text" name="address_line1" value="{{ old('address_line1') }}">
        </div>
        <div style="flex:1">
            <label>Address line 2</label>
            <input type="text" name="address_line2" value="{{ old('address_line2') }}">
        </div>
    </div>

    <div class="row">
        <div style="flex:1">
            <label>City</label>
            <input type="text" name="city" value="{{ old('city') }}">
        </div>
        <div style="flex:1">
            <label>Postcode</label>
            <input type="text" name="postcode" value="{{ old('postcode') }}">
        </div>
    </div>

    <div class="row">
        <div style="flex:1">
            <label>Country</label>
            <input type="text" name="country" value="{{ old('country') }}">
        </div>
        <div style="flex:1">
            <label>Contact email</label>
            <input type="email" name="email" value="{{ old('email') }}">
        </div>
    </div>

    <div>
        <label>Phone</label>
        <input type="text" name="phone" value="{{ old('phone') }}">
    </div>

    <div>
        <button class="primary" type="submit">Save practice</button>
    </div>
</form>
@endsection
