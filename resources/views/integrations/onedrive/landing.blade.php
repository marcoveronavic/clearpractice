@extends('layouts.app')

@section('title', 'Connect OneDrive')

@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-2">Connect OneDrive</h1>

        @if (session('status'))
            <div class="p-3 mb-4 rounded border border-green-200 bg-green-50">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 mb-4 rounded border border-red-200 bg-red-50">
                <ul class="list-disc ml-6">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        {{-- Sign-in / status --}}
        <div class="mb-6 border rounded p-4">
            @if ($driveId ?? false)
                <div class="mb-2 font-medium">Status: Connected to OneDrive ({{ $driveType ?? 'unknown' }})</div>
                <div class="text-sm text-gray-600">Drive ID: <code>{{ $driveId }}</code></div>
            @else
                <div class="mb-2 font-medium">Status: Not connected</div>
                <a href="{{ route('msgraph.oauth') }}" class="inline-block mt-2 px-4 py-2 rounded bg-black text-white">
                    Sign in with Microsoft
                </a>
                <p class="mt-2 text-xs text-gray-600">After signing in, this page will show your OneDrive and folders.</p>
            @endif
        </div>

        @if ($driveId ?? false)
            <div class="grid md:grid-cols-2 gap-6">
                <div class="border rounded p-4">
                    <h2 class="font-semibold mb-2">1) (Optional) Create a folder</h2>
                    <form method="post" action="{{ route('onedrive.createFolder') }}" class="space-y-2">
                        @csrf
                        <input name="folder_name" required class="w-full border rounded p-2" placeholder="ClearPractice">
                        <button class="px-4 py-2 rounded bg-black text-white">Create in OneDrive root</button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">We’ll auto-rename if it exists.</p>
                </div>

                <div class="border rounded p-4">
                    <h2 class="font-semibold mb-2">2) Choose & save your base folder</h2>

                    @if (!empty($practice?->id))
                        <form method="post" action="{{ route('onedrive.save') }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="practice_id" value="{{ $practice->id }}">
                            <input type="hidden" name="drive_id" value="{{ $driveId }}">
                            <input type="hidden" name="drive_type" value="{{ $driveType ?? '' }}">
                            <label class="text-sm text-gray-700">Base path (e.g. <code>ClearPractice</code> or <code>Documents/ClearPractice</code>)</label>
                            <input name="base_path" class="w-full border rounded p-2" value="{{ old('base_path', $practice->onedrive_base_path ?? '') }}" placeholder="ClearPractice">
                            <button class="px-4 py-2 rounded bg-black text-white">Save to practice</button>
                        </form>
                        <p class="mt-2 text-xs text-gray-500">Leave empty to use your OneDrive root.</p>
                    @else
                        <p class="text-sm text-gray-700">
                            Open this page from inside a workspace to save the base folder to a practice.
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Example: go to your practice area, then Settings → OneDrive.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Root folders with Browse/Open --}}
            <div class="mt-8 border rounded p-4">
                <h2 class="font-semibold mb-2">Root folders (quick pick)</h2>
                @php $children = $children ?? []; @endphp
                @if (empty($children))
                    <p class="text-sm text-gray-600">No items found.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($children as $c)
                            @if (isset($c['folder']))
                                @php
                                    $enc = rtrim(strtr(base64_encode($c['name']), '+/', '-_'), '=');
                                @endphp
                                <div class="flex items-center justify-between border rounded p-2">
                                    <div>
                                        <div class="font-medium">{{ $c['name'] }}</div>
                                        <div class="text-xs text-gray-500">folder • childCount: {{ $c['folder']['childCount'] ?? 0 }}</div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a class="px-3 py-1 rounded border text-sm" href="{{ route('onedrive.browse', $enc) }}">Browse</a>
                                        <a class="px-3 py-1 rounded border text-sm" href="{{ route('onedrive.open', $enc) }}" target="_blank" rel="noopener">Open</a>

                                        @if (!empty($practice?->id))
                                            <form method="post" action="{{ route('onedrive.save') }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="practice_id" value="{{ $practice->id }}">
                                                <input type="hidden" name="drive_id" value="{{ $driveId }}">
                                                <input type="hidden" name="drive_type" value="{{ $driveType ?? '' }}">
                                                <input type="hidden" name="base_path" value="{{ $c['name'] }}">
                                                <button class="px-3 py-1 rounded bg-black text-white text-sm">Use this</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Smoke test upload --}}
            @if (!empty($practice?->id))
                <div class="mt-8 border rounded p-4">
                    <h2 class="font-semibold mb-2">Smoke test upload</h2>
                    <form method="post" action="{{ route('onedrive.uploadTest') }}">
                        @csrf
                        <input type="hidden" name="practice_id" value="{{ $practice->id }}">
                        <button class="px-4 py-2 rounded bg-black text-white">Upload “clearpractice-hello.txt”</button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">File is created under the saved base path.</p>
                </div>
            @endif
        @endif
    </div>
@endsection
