@extends('layouts.app')
@section('title', 'Connect S3 Storage')
@section('content')
    <div class="max-w-3xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-3">S3 storage (AWS / Wasabi / MinIO)</h1>

        @if (session('status'))
            <div class="p-3 mb-4 rounded border border-green-200 bg-green-50">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="p-3 mb-4 rounded border border-red-200 bg-red-50">
                <ul class="list-disc ml-6">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form method="post" action="{{ route('practice.settings.s3.save', $practice->slug) }}" class="space-y-3">
            @csrf
            <label class="text-sm">Bucket</label>
            <input name="s3_bucket" required class="w-full border rounded p-2"
                   value="{{ old('s3_bucket', $practice->s3_bucket ?: $bucket) }}" placeholder="clearpractice-prod">

            <label class="text-sm">Base prefix (optional)</label>
            <input name="s3_prefix" class="w-full border rounded p-2"
                   value="{{ old('s3_prefix', $practice->s3_prefix) }}" placeholder="documents">

            <button class="px-4 py-2 rounded bg-black text-white">Save</button>
        </form>

        <p class="text-xs text-gray-600 mt-4">
            Company folders will live under
            <code>{{ $practice->s3_prefix ? $practice->s3_prefix.'/' : '' }}companies/{company-slug}</code>.
        </p>
    </div>
@endsection

