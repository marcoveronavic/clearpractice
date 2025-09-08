@extends('layouts.app')
@section('title', 'Documents — '.$company->name)

@section('content')
    <div class="max-w-7xl mx-auto py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-semibold">Documents — {{ $company->name }}</h1>
            <a class="text-sm underline" href="{{ route('practice.settings.s3', $practice->slug) }}">Settings → S3</a>
        </div>

        @if (session('status'))
            <div class="p-3 mb-4 rounded" style="border:1px solid #bbf7d0;background:#f0fdf4">
                {{ session('status') }}
                @if (session('share_url'))
                    <div class="mt-1"><a class="underline text-blue-600" target="_blank" href="{{ session('share_url') }}">Open share link</a></div>
                @endif
            </div>
        @endif
        @if ($errors->any())
            <div class="p-3 mb-4 rounded" style="border:1px solid #fecaca;background:#fef2f2">
                <ul class="list-disc ml-6">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        {{-- Actions --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div style="border:1px solid #e5e7eb;border-radius:8px;padding:16px">
                <h2 style="font-weight:600;margin:0 0 8px">Upload</h2>
                <form method="post" action="{{ route('practice.companies.docs.s3.upload', [$practice->slug, $company->id]) }}" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:center">
                    @csrf
                    <input type="file" name="file" required class="text-sm">
                    <button class="text-sm" style="padding:8px 12px;border-radius:6px;background:#111;color:#fff">Upload</button>
                </form>
            </div>

            <div style="border:1px solid #e5e7eb;border-radius:8px;padding:16px">
                <h2 style="font-weight:600;margin:0 0 8px">Create folder</h2>
                <form method="post" action="{{ route('practice.companies.docs.s3.folder', [$practice->slug, $company->id]) }}" style="display:flex;gap:12px;align-items:center">
                    @csrf
                    <input name="folder_name" required class="w-full" style="flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:8px" placeholder="NewFolder or nested/child/folder">
                    <button class="text-sm" style="padding:8px 12px;border-radius:6px;background:#111;color:#fff">Create</button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="rounded" style="border:1px solid #e5e7eb;overflow:hidden">
            <table class="w-full text-sm">
                <thead style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                <tr>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Type</th>
                    <th class="text-left p-2">Size</th>
                    <th class="text-left p-2">Actions</th>
                </tr>
                </thead>
                <tbody id="cp-doc-list">
                {{-- Folders --}}
                @forelse ($dirs as $d)
                    <tr style="border-bottom:1px solid #e5e7eb">
                        <td class="p-2">{{ $d }}</td>
                        <td class="p-2">Folder</td>
                        <td class="p-2">—</td>
                        <td class="p-2 text-right" style="color:#6b7280">—</td>
                    </tr>
                @empty
                    <tr style="border-bottom:1px solid #e5e7eb"><td class="p-2" colspan="4" style="color:#6b7280">No folders.</td></tr>
                @endforelse

                {{-- Files --}}
                @forelse ($files as $f)
                    @php
                        $encPath     = $enc($f);
                        $previewUrl  = route('practice.companies.docs.s3.preview',  [$practice->slug, $company->id, $encPath]);
                        $openUrl     = route('practice.companies.docs.s3.open',     [$practice->slug, $company->id, $encPath]);
                        $downloadUrl = route('practice.companies.docs.s3.download', [$practice->slug, $company->id, $encPath]);
                        $shareUrl    = route('practice.companies.docs.s3.share',    [$practice->slug, $company->id, $encPath]);
                        $ext         = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                        $isPrev      = in_array($ext, ['pdf','jpg','jpeg','png','gif','webp']);
                    @endphp
                    <tr style="border-bottom:1px solid #e5e7eb">
                        <td class="p-2">{{ $f }}</td>
                        <td class="p-2">File</td>
                        <td class="p-2">—</td>
                        <td class="p-2 text-right">
                            @if ($isPrev)
                                <button type="button"
                                        class="cp-preview-btn underline"
                                        data-name="{{ $f }}"
                                        data-preview="{{ $previewUrl }}"
                                        data-open="{{ $openUrl }}"
                                        data-download="{{ $downloadUrl }}">
                                    Preview
                                </button>
                            @endif
                            <a class="underline" target="_blank" href="{{ $openUrl }}">Open</a>
                            <a class="underline" href="{{ $downloadUrl }}">Download</a>
                            <form class="inline" method="post" action="{{ $shareUrl }}" style="display:inline">
                                @csrf
                                <button class="underline" type="submit">Share</button>
                            </form>
                            <form class="inline" method="post" action="{{ route('practice.companies.docs.s3.delete', [$practice->slug, $company->id, $encPath]) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="underline" style="color:#dc2626" onclick="return confirm('Delete this file?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="p-2" colspan="4" style="color:#6b7280">No files yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =========== MODAL PREVIEW (smaller) =========== --}}
    <style>
        #cp-modal{position:fixed;inset:0;display:none;z-index:9999;}
        #cp-modal.show{display:block;}
        #cp-modal .backdrop{position:absolute;inset:0;background:rgba(0,0,0,.55);}
        #cp-modal .panel{
            position:absolute;
            left:10%;          /* smaller and centered-ish */
            top:8%;
            width:80%;
            height:84%;
            max-width:1400px;  /* optional cap */
            background:#fff;
            border-radius:10px;
            box-shadow:0 20px 50px rgba(0,0,0,.25);
            display:flex;flex-direction:column;overflow:hidden;
        }
        #cp-modal .header{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid #e5e7eb}
        #cp-modal .header .title{font-weight:600;font-size:14px}
        #cp-modal .controls a,#cp-modal .controls button{
            border:1px solid #e5e7eb;border-radius:6px;padding:6px 10px;font-size:12px;background:#fff;cursor:pointer
        }
        #cp-modal .controls a.primary{background:#111;color:#fff;border-color:#111}
        #cp-modal .body{flex:1;overflow:hidden}
        #cp-modal iframe{width:100%;height:100%;border:0}
        #cp-modal .placeholder{padding:18px;color:#6b7280}
    </style>

    <div id="cp-modal" aria-hidden="true">
        <div class="backdrop" id="cp-modal-backdrop"></div>
        <div class="panel" role="dialog" aria-modal="true" aria-labelledby="cp-modal-title">
            <div class="header">
                <div>
                    <div style="color:#6b7280;font-size:12px;line-height:1">Previewing</div>
                    <div id="cp-modal-title" class="title">—</div>
                </div>
                <div class="controls">
                    <a id="cp-modal-open" class="primary" target="_blank" href="#">Open</a>
                    <a id="cp-modal-dl" href="#">Download</a>
                    <button id="cp-modal-close">Close</button>
                </div>
            </div>
            <div class="body">
                <iframe id="cp-modal-frame" src="about:blank"></iframe>
                <div id="cp-modal-placeholder" class="placeholder" style="display:none">
                    No inline preview for this file. Use <b>Open</b> or <b>Download</b>.
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const list     = document.getElementById('cp-doc-list');

            const modal    = document.getElementById('cp-modal');
            const backdrop = document.getElementById('cp-modal-backdrop');
            const closeBtn = document.getElementById('cp-modal-close');
            const titleEl  = document.getElementById('cp-modal-title');
            const frame    = document.getElementById('cp-modal-frame');
            const place    = document.getElementById('cp-modal-placeholder');
            const openBtn  = document.getElementById('cp-modal-open');
            const dlBtn    = document.getElementById('cp-modal-dl');

            function openModal(opts){
                titleEl.textContent = opts.name || 'Preview';
                openBtn.href = opts.open || '#';
                dlBtn.href   = opts.download || '#';

                if (opts.preview) {
                    place.style.display = 'none';
                    frame.style.display = 'block';
                    frame.src = opts.preview;
                } else {
                    frame.style.display = 'none';
                    place.style.display = 'block';
                }

                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeModal(){
                modal.classList.remove('show');
                document.body.style.overflow = '';
                frame.src = 'about:blank';
            }

            // ✅ Read data-* from the Preview button itself
            list.addEventListener('click', function(e){
                const btn = e.target.closest('.cp-preview-btn');
                if (!btn) return;

                openModal({
                    name:     btn.dataset.name,
                    preview:  btn.dataset.preview,
                    open:     btn.dataset.open,
                    download: btn.dataset.download
                });
            });

            backdrop.addEventListener('click', closeModal);
            closeBtn.addEventListener('click', closeModal);
            document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });
        })();
    </script>
@endsection
