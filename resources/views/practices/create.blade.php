@extends('layouts.app')

@section('title','Create practice')

@section('content')
    <h1>Create your practice</h1>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash err">
            @foreach ($errors->all() as $e) {{ $e }}<br> @endforeach
        </div>
    @endif

    <div class="card" style="max-width:720px">
        <form method="POST" action="{{ route('practices.store') }}"
              style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end">
            @csrf
            <div>
                <label class="muted">Practice name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="muted">Slug (optional)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="auto from name if blank">
            </div>
            <div>
                <button class="btn primary" type="submit" style="margin-top:22px">Create</button>
            </div>
        </form>
    </div>
@endsection
