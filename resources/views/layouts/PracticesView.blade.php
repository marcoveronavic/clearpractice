@extends('layouts.app')
@section('title','Practices')

@section('content')
    <h1>Practices</h1>
    @if ($practices->count())
        <ul>
            @foreach ($practices as $practice)
                <li>
                    <a href="{{ route('practices.show', $practice) }}">{{ $practice->name }}</a>
                </li>
            @endforeach
        </ul>
        {{ $practices->links() }}
    @else
        <p>No practices yet</p>
    @endif
@endsection
