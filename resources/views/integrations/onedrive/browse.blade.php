@extends('layouts.app')

@section('title', 'OneDrive Browser')

@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">OneDrive — Browser</h1>
            @if ($webUrl)
                <a class="px-3 py-2 rounded border" href="{{ $webUrl }}" target="_blank" rel="noopener">Open current folder</a>
            @endif
        </div>

        {{-- Breadcrumb --}}
        <nav class="text-sm mb-4">
            @foreach ($crumbs as $i => $c)
                @if ($i) <span class="mx-1 text-gray-400">/</span> @endif
                @if ($c['encoded'])
                    <a class="underline" href="{{ route('onedrive.browse', $c['encoded']) }}">{{ $c['name'] }}</a>
                @else
                    <a class="underline" href="{{ route('onedrive.browse') }}">Root</a>
                @endif
            @endforeach
        </nav>

        <div class="rounded border">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Type</th>
                    <th class="text-left p-2">Size</th>
                    <th class="text-left p-2">Modified</th>
                    <th class="p-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($children as $it)
                    @php
                        $isFolder = isset($it['folder']);
                        $childPath = trim($pathRel === '' ? $it['name'] : $pathRel.'/'.$it['name'], '/');
                        $enc = $encode($childPath);
                    @endphp
                    <tr class="border-b">
                        <td class="p-2">{{ $it['name'] }}</td>
                        <td class="p-2">{{ $isFolder ? 'Folder' : 'File' }}</td>
                        <td class="p-2">{{ $isFolder ? '—' : number_format(($it['size'] ?? 0)/1024, 1) . ' KB' }}</td>
                        <td class="p-2">
                            @if(isset($it['lastModifiedDateTime']))
                                {{ \Illuminate\Support\Carbon::parse($it['lastModifiedDateTime'])->tz(config('app.timezone'))->format('Y-m-d H:i') }}
                            @else — @endif
                        </td>
                        <td class="p-2 text-right space-x-2">
                            @if ($isFolder)
                                <a class="underline" href="{{ route('onedrive.browse', $enc) }}">Browse</a>
                            @endif
                            <a class="underline" href="{{ route('onedrive.open', $enc) }}" target="_blank" rel="noopener">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-gray-600" colspan="5">No items.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
