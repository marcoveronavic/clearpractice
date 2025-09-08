@php
    // expects: $practice, $company, $connected, $items, $webUrl, $folderRel
@endphp

@if (! $connected)
    <div class="p-4 rounded border bg-yellow-50 border-yellow-200">
        <div class="font-medium mb-1">OneDrive not connected for this practice.</div>
        <p class="text-sm text-gray-700">
            Go to <a class="underline" href="{{ route('onedrive.landing') }}">Settings → OneDrive</a>,
            sign in and choose a base folder, then return here.
        </p>
    </div>
@else
    <div class="p-4 rounded border mb-6">
        <div class="flex items-center justify-between">
            <div>
                <div class="font-medium">Company folder</div>
                <div class="text-sm text-gray-600"><code>{{ $folderRel }}</code></div>
            </div>
            <div class="flex items-center gap-2">
                <form method="post" action="{{ route('practice.companies.documents.folder', [$practice->slug, $company->id]) }}">
                    @csrf
                    <button class="px-3 py-2 rounded bg-black text-white text-sm">Create folder</button>
                </form>
                @if ($webUrl)
                    <a class="px-3 py-2 rounded border text-sm" href="{{ $webUrl }}" target="_blank" rel="noopener">Open in OneDrive</a>
                @endif
            </div>
        </div>

        <div class="mt-4">
            <form class="flex items-center gap-3" method="post" action="{{ route('practice.companies.documents.upload', [$practice->slug, $company->id]) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" required class="text-sm">
                <button class="px-3 py-2 rounded bg-black text-white text-sm">Upload</button>
            </form>
        </div>
    </div>

    <div class="rounded border">
        <table class="w-full text-sm">
            <thead>
            <tr class="bg-gray-50 border-b">
                <th class="text-left p-2">Name</th>
                <th class="text-left p-2">Type</th>
                <th class="text-left p-2">Size</th>
                <th class="text-left p-2">Modified</th>
                <th class="p-2"></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $it)
                @php
                    $isFolder = isset($it['folder']);
                    $pathRel  = $folderRel . '/' . $it['name'];
                    $encoded  = rtrim(strtr(base64_encode($pathRel), '+/', '-_'), '=');
                @endphp
                <tr class="border-b">
                    <td class="p-2">{{ $it['name'] }}</td>
                    <td class="p-2">{{ $isFolder ? 'Folder' : 'File' }}</td>
                    <td class="p-2">{{ $isFolder ? '—' : number_format(($it['size'] ?? 0) / 1024, 1) . ' KB' }}</td>
                    <td class="p-2">
                        @if(isset($it['lastModifiedDateTime']))
                            {{ \Illuminate\Support\Carbon::parse($it['lastModifiedDateTime'])->tz(config('app.timezone'))->format('Y-m-d H:i') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="p-2 text-right">
                        @if (! $isFolder)
                            <a class="text-blue-600 hover:underline mr-2"
                               href="{{ route('practice.companies.documents.download', [$practice->slug, $company->id, $encoded]) }}">
                                Download
                            </a>
                            <form method="post" action="{{ route('practice.companies.documents.delete', [$practice->slug, $company->id, $encoded]) }}" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline" onclick="return confirm('Delete this file?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td class="p-3 text-gray-600" colspan="5">No files yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endif
