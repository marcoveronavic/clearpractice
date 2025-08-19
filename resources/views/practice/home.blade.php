@extends('layouts.app')

@section('content')
  <div class="max-w-5xl mx-auto py-8 px-4">
    @if(session('ok'))
      <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800">
        {{ session('ok') }}
      </div>
    @endif

    <h1 class="text-2xl font-bold mb-3">{{ $lead->practice }}</h1>
    <p class="text-slate-600 mb-6">New practice workspace (your left menu stays as before).</p>

    <div class="space-x-2">
      <a class="btn btn-primary" href="/lead/add-users?t={{ $lead->token }}">Add users</a>
      <a class="btn btn-ghost" href="/landing/companies">Companies (old list)</a>
    </div>
  </div>
@endsection
