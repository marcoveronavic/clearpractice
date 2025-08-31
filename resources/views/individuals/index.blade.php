@extends('layouts.app')

@section('title','Individuals')

@section('content')
    <h1>Individuals</h1>

    @php $list = collect($individuals ?? []); @endphp

    @if($list->isEmpty())
        <p class="muted">No individuals yet.</p>
    @else
        <div class="card">
            {{-- your table/list goes here --}}
        </div>
    @endif
@endsection
