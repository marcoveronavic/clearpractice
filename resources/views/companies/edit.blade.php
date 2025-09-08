@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-3">Edit {{ $company->name }}</h1>

        @if (session('status'))
            <div style="background:#e7f5ff;border:1px solid #a5d8ff;padding:10px;border-radius:6px;margin-bottom:12px">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background:#ffe3e3;border:1px solid #ffb3b3;padding:10px;border-radius:6px;margin-bottom:12px">
                <strong>Fix the following:</strong>
                <ul style="margin:6px 0 0 16px">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- IMPORTANT: pass the PRACTICE SLUG to match /p/{practice:slug} --}}
        <form method="POST" action="{{ route('practice.companies.update', [$practice->slug, $company->id]) }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="vat_number" class="form-label">VAT Reg. number (VRN)</label>
                <input id="vat_number" name="vat_number" type="text"
                       value="{{ old('vat_number', $company->vat_number) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label for="utr" class="form-label">UTR</label>
                <input id="utr" name="utr" type="text"
                       value="{{ old('utr', $company->utr) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label for="authentication_code" class="form-label">Authentication code</label>
                <input id="authentication_code" name="authentication_code" type="text"
                       value="{{ old('authentication_code', $company->authentication_code) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label for="vat_period" class="form-label">VAT period</label>
                <input id="vat_period" name="vat_period" type="text"
                       value="{{ old('vat_period', $company->vat_period) }}" class="form-control" placeholder="Monthly / Quarterly">
            </div>

            {{-- MATCH DB COLUMN NAME --}}
            <div class="mb-3">
                <label for="vat_quarter_group" class="form-label">VAT quarter end</label>
                <input id="vat_quarter_group" name="vat_quarter_group" type="text"
                       value="{{ old('vat_quarter_group', $company->vat_quarter_group) }}" class="form-control" placeholder="e.g. Mar / Jun / Sep / Dec">
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Telephone</label>
                <input id="telephone" name="telephone" type="text"
                       value="{{ old('telephone', $company->telephone) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $company->email) }}" class="form-control">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Save</button>
                <a href="{{ route('practice.companies.show', [$practice->slug, $company->id]) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
