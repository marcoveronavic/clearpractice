@extends('layouts.app')
@section('title','Edit practice')

@section('content')
    <h1>Edit practice</h1>

    @if (session('status')) <div class="flash ok">{{ session('status') }}</div> @endif
    @if ($errors->any())
        <div class="flash err">@foreach ($errors->all() as $e) {{ $e }}<br>@endforeach</div>
    @endif

    <div class="card" style="max-width:720px">
        <form method="POST" action="{{ route('practices.update', $practice) }}"
              style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
            @csrf @method('PUT')
            <div>
                <label class="muted">Practice name</label>
                <input type="text" name="name" value="{{ old('name', $practice->name) }}" required>
            </div>
            <div>
                <label class="muted">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $practice->slug) }}">
            </div>
            <div>
                <button class="btn primary" type="submit" style="margin-top:22px">Save</button>
            </div>
        </form>
    </div>
@endsection
