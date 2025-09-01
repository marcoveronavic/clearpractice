<div class="cp-card">
    <h2 style="margin:0 0 6px 0">{{ $card['name'] ?? ('Company '.$card['number']) }}</h2>
    <div class="muted" style="margin-bottom:12px">
        No: <strong>{{ $card['number'] }}</strong>
        @if(!empty($card['status'])) — Status: {{ ucfirst($card['status']) }} @endif
        @if(!empty($card['incorporated'])) — Incorporated: {{ $card['incorporated'] }} @endif
    </div>

    @if(!empty($card['registered_office']))
        <div style="margin:6px 0"><strong>Registered office</strong><br>{{ $card['registered_office'] }}</div>
    @endif

    <div class="cp-grid">
        <div>
            <div class="muted">Year end</div>
            <div><strong>{{ $card['year_end'] ?? '–' }}</strong></div>
        </div>
        <div>
            <div class="muted">Accounts due</div>
            <div><strong>{{ $card['accounts_due'] ?? '–' }}</strong></div>
        </div>
        <div>
            <div class="muted">Confirmation due</div>
            <div><strong>{{ $card['confirmation_due'] ?? '–' }}</strong></div>
        </div>
    </div>

    <hr style="margin:14px 0">

    <h3 style="margin:0 0 8px 0">Directors</h3>
    <ul style="margin:0 0 14px 18px">
        @forelse($card['directors'] as $d)
            <li>
                {{ $d['name'] }}
                @if(!empty($d['role'])) <span class="muted">({{ $d['role'] }})</span>@endif
                @if(!empty($d['appointed'])) — appointed {{ $d['appointed'] }} @endif
                @if(!empty($d['resigned'])) — resigned {{ $d['resigned'] }} @endif
            </li>
        @empty
            <li class="muted">No officers reported.</li>
        @endforelse
    </ul>

    <h3 style="margin:0 0 8px 0">Persons with significant control (PSC)</h3>
    <ul style="margin:0 0 6px 18px">
        @forelse($card['psc'] as $p)
            <li>
                {{ $p['name'] }}
                @if(!empty($p['natures'])) — {{ implode(', ', $p['natures']) }} @endif
                @if(!empty($p['ceased_on'])) (ceased {{ $p['ceased_on'] }}) @endif
            </li>
        @empty
            <li class="muted">No PSC reported.</li>
        @endforelse
    </ul>
</div>

<style>
    .cp-card .muted { color:#6b7280 }
    .cp-card .cp-grid {
        display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px;
    }
</style>
