@extends('layouts.app')
@section('title', 'Clients')

@section('content')
    <div class="page">
        <h1 style="margin:10px 0 12px">Clients</h1>

        {{-- Add client --}}
        <div class="card" style="margin-bottom:14px">
            <form method="POST"
                  action="{{ route('practice.clients.store', $practice->slug) }}"
                  style="display:flex; gap:10px; align-items:center; flex-wrap:wrap">
                @csrf
                <div>
                    <label class="muted" style="display:block; font-size:12px; margin-bottom:4px">Client name</label>
                    <input type="text" name="name" placeholder="e.g. Jane Smith" value="{{ old('name') }}"
                           style="padding:6px 8px; border:1px solid #e5e7eb; border-radius:6px; width:220px">
                </div>
                <div>
                    <label class="muted" style="display:block; font-size:12px; margin-bottom:4px">Email</label>
                    <input type="email" name="email" placeholder="jane@example.com" value="{{ old('email') }}"
                           style="padding:6px 8px; border:1px solid #e5e7eb; border-radius:6px; width:220px">
                </div>
                <div style="align-self:flex-end; padding-bottom:2px">
                    <button class="btn" type="submit">Add client</button>
                </div>
            </form>
        </div>

        {{-- List --}}
        <div class="card">
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align:left; padding:10px">Name</th>
                    <th style="text-align:left; padding:10px; width:30%">Email</th>
                    <th style="text-align:right; padding:10px; width:1%">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td style="padding:10px; border-top:1px solid #e5e7eb;">
                            {{ $client->name ?? $client->company_name ?? $client->display ?? ('Client #'.$client->id) }}
                        </td>
                        <td style="padding:10px; border-top:1px solid #e5e7eb;">
                            {{ $client->email ?? '-' }}
                        </td>
                        <td style="padding:10px; border-top:1px solid #e5e7eb; text-align:right; white-space:nowrap;">
                            <form method="POST"
                                  action="{{ route('practice.clients.destroy', [$practice->slug, $client->id]) }}"
                                  onsubmit="return confirm('Remove this client from the practice?');"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn" type="submit"
                                        title="Remove from this practice"
                                        style="border-color:#fecaca; color:#991b1b; background:#fff;">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="padding:14px; text-align:center; color:#6b7280">No clients yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
