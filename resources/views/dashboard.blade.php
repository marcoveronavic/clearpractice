@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-2xl mx-auto py-10">
        <h1 class="text-2xl font-bold mb-4">You’re signed in ✅</h1>

        <div class="space-y-3">
            <a class="inline-block px-4 py-2 rounded bg-black text-white" href="{{ route('users.index') }}">
                Go to your workspace
            </a>
            <a class="inline-block px-4 py-2 rounded border" href="{{ url('/integrations/onedrive') }}">
                Settings → OneDrive
            </a>
        </div>
    </div>
@endsection

