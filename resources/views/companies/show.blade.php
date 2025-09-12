@extends('layouts.app')

@section('title', $company->name ?? $company->number)

@section('head')
    <style>
        :root{
            --fg:#111827; --muted:#6b7280; --line:#e5e7eb; --bg:#f9fafb; --card:#ffffff;
            --blue:#2563eb; --blue-100:#dbeafe;
        }

        /* Layout */
        .page-wrap{width:100%;max-width:none;}
        .page-inner{padding:16px 20px;}

        /* Header toolbar */
        .toolbar{display:flex;gap:12px;align-items:center;margin-bottom:8px}
        .btn{appearance:none;background:#fff;color:#111;border:1px solid var(--line);padding:8px 12px;border-radius:10px;text-decoration:none;cursor:pointer}
        .btn:hover{border-color:#cbd5e1}
        .btn.primary{background:var(--blue);color:#fff;border-color:var(--blue)}
        .btn.pill{border-radius:999px}

        .title{font-size:26px;font-weight:700;margin:8px 0 2px}
        .muted{color:#6b7280;font-size:13px}
        .badge{display:inline-block;padding:2px 8px;background:#f3f4f6;border-radius:999px;font-size:12px;color:#374151;margin-right:6px}

        /* Tabs */
        .tabs{display:flex;gap:6px;border-bottom:1px solid var(--line);margin:14px 0 18px;overflow:auto}
        .tab-link{appearance:none;background:transparent;border:0;border-bottom:2px solid transparent;padding:10px 12px;cursor:pointer;white-space:nowrap;color:#6b7280;font-weight:600}
        .tab-link.active{border-color:#111827;color:#111827}

        /* Panels */
        .tab-panel{display:none!important}
        .tab-panel.active{display:block!important}
        [hidden]{display:none!important}

        /* Cards */
        .card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:16px}
        .card h3{margin:0 0 12px;font-size:16px;font-weight:700}

        /* Dashboard bits */
        .metrics{display:grid;grid-template-columns:1fr 1fr 1fr;border:1px solid var(--line);border-radius:8px;overflow:hidden}
        .metric{padding:16px}
        .metric+.metric{border-left:1px solid var(--line)}
        .metric .label{color:#6b7280;font-weight:700}
        .metric .value{font-size:28px;font-weight:800;margin:8px 0 10px}
        .metric .sub{display:flex;gap:14px;color:#6b7280;font-size:12px}
        .metric .actions{margin-top:10px}

        .split{display:grid;grid-template-columns:1fr 380px;gap:16px;margin-top:14px}
        @media (max-width:1100px){.split{grid-template-columns:1fr}}

        .progress{height:18px;background:var(--blue-100);border-radius:999px;overflow:hidden}
        .progress>span{display:block;height:100%;background:var(--blue);width:0%}

        .dot{width:10px;height:10px;border-radius:999px;display:inline-block;margin-right:8px;vertical-align:middle}
        .dot.blue{background:var(--blue)} .dot.gray{background:#9ca3af}

        /* Key/value rows */
        .row{display:grid;grid-template-columns:220px 1fr;gap:10px;padding:8px 0;border-top:1px solid var(--line)}
        .row:first-child{border-top:none}
        .label{color:#6b7280}

        /* Generic table */
        .table-wrap{overflow:auto}
        table.table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:10px 12px;border-top:1px solid var(--line);text-align:left;vertical-align:top;white-space:nowrap}
        .table th{font-size:12px;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.02em}
        .table td{white-space:normal}
    </style>
@endsection

@section('content')
    <div class="page-wrap">
        <div class="page-inner">

            {{-- Header --}}
            <div class="toolbar">
                <a class="btn" href="{{ route('practice.companies.index', $practice->slug) }}">← Back to companies</a>
                @if(!empty($company->number))
                    <a class="btn" target="_blank" rel="noopener"
                       href="https://find-and-update.company-information.service.gov.uk/company/{{ urlencode($company->number) }}">
                        Open on Companies House ↗
                    </a>
                @endif
            </div>

            <div class="title">{{ $company->name ?? '—' }}</div>
            <div class="muted">
                @if(!empty($company->number)) <span class="badge">Number {{ $company->number }}</span>@endif
                @if(!empty($company->status)) <span class="badge">{{ $company->status }}</span>@endif
                @if(!empty($company->type))   <span class="badge">{{ $company->type }}</span>@endif
            </div>

            {{-- Tabs --}}
            <div class="tabs" role="tablist">
                <button id="tabbtn-dashboard"  class="tab-link active" data-tab="dashboard"  type="button">Dashboard</button>
                <button id="tabbtn-details"    class="tab-link"         data-tab="details"    type="button">Details</button>
                <button id="tabbtn-deadlines"  class="tab-link"         data-tab="deadlines"  type="button">Deadlines</button>
                <button id="tabbtn-comms"      class="tab-link"         data-tab="comms"      type="button">Communications</button>
                <button id="tabbtn-notes"      class="tab-link"         data-tab="notes"      type="button">Notes</button>
                <button id="tabbtn-docs"       class="tab-link"         data-tab="docs"       type="button">Documents</button>
                <button id="tabbtn-tasks"      class="tab-link"         data-tab="tasks"      type="button">Tasks</button>
                <button id="tabbtn-bills"      class="tab-link"         data-tab="bills"      type="button">Bills</button>
            </div>

            {{-- DASHBOARD --}}
            @php
                $dash = $dashboard ?? [];
                $wipAmount   = $dash['wip_amount']   ?? '£0.00';
                $unbilled    = $dash['unbilled']     ?? '£0.00';
                $draft       = $dash['draft']        ?? '£0.00';
                $outstanding = $dash['outstanding']  ?? '£0.00';
                $clientFunds = $dash['client_funds'] ?? '£0.00';
                $timeTotal   = $dash['time_total']   ?? '£0.00';
                $billableAmt = $dash['billable_amount'] ?? '£0.00';
                $billableHrs = $dash['billable_hours']  ?? '0.00h';
                $nonbillAmt  = $dash['nonbill_amount']  ?? '£0.00';
                $nonbillHrs  = $dash['nonbill_hours']   ?? '0.00h';
                $billablePct = $dash['billable_pct']    ?? 50;
                $expensesAmt = $dash['expenses']        ?? '£0.00';
            @endphp

            <section id="tab-dashboard" class="tab-panel active" aria-labelledby="tabbtn-dashboard">
                <div class="card">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <div style="font-weight:700">Financial</div>
                        <div><button class="btn primary">Manage payment methods</button></div>
                    </div>

                    <div class="metrics">
                        <div class="metric">
                            <div class="label">Work in progress</div>
                            <div class="value">{{ $wipAmount }}</div>
                            <div class="sub">
                                <div><span class="muted">Unbilled</span> {{ $unbilled }}</div>
                                <div><span class="muted">Draft</span> {{ $draft }}</div>
                            </div>
                            <div class="actions"><button class="btn pill">Quick bill</button></div>
                        </div>
                        <div class="metric">
                            <div class="label">Outstanding balance</div>
                            <div class="value">{{ $outstanding }}</div>
                            <div class="actions" style="margin-top:14px"><button class="btn pill">View bills</button></div>
                        </div>
                        <div class="metric">
                            <div class="label">Client funds (matter)</div>
                            <div class="value">{{ $clientFunds }}</div>
                            <div class="actions" style="margin-top:14px"><button class="btn pill">New request</button></div>
                        </div>
                    </div>

                    <div class="split">
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin:16px 0 8px">
                                <div style="font-weight:800">Time</div>
                                <button class="btn pill">Add time</button>
                            </div>

                            <div style="margin-bottom:10px"><div style="font-weight:700">{{ $timeTotal }}</div></div>

                            <div class="progress" aria-label="Billable percentage">
                                <span style="width: {{ max(0,min(100,$billablePct)) }}%"></span>
                            </div>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px">
                                <div>
                                    <span class="dot blue"></span><strong>Billable</strong>
                                    <span class="muted" style="margin-left:6px">{{ $billableAmt }}</span>
                                    <span class="muted" style="margin-left:8px">{{ $billableHrs }}</span>
                                </div>
                                <div>
                                    <span class="dot gray"></span><strong>Non-billable</strong>
                                    <span class="muted" style="margin-left:6px">{{ $nonbillAmt }}</span>
                                    <span class="muted" style="margin-left:8px">{{ $nonbillHrs }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin:16px 0 8px">
                                <div style="font-weight:800">Expenses</div>
                                <button class="btn pill">Add expense</button>
                            </div>
                            <div style="font-weight:700">{{ $expensesAmt }}</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- DETAILS --}}
            <section id="tab-details" class="tab-panel" hidden aria-labelledby="tabbtn-details">
                <div class="card">
                    <div style="display:flex;align-items:center;gap:12px;margin:0 0 12px">
                        <h3 style="margin:0">Details</h3>
                        <div style="flex:1"></div>
                        @include('companies._edit_button')
                        @if ($company->vat_number)
                            <a class="btn pill" href="{{ route('practice.hmrc.connect', [$practice->slug, $company->id]) }}">Connect HMRC VAT (MTD)</a>
                            <a class="btn" href="{{ route('practice.hmrc.obligations', [$practice->slug, $company->id]) }}">Fetch VAT obligations</a>
                        @endif
                    </div>

                    <div class="row"><div class="label">Email</div><div>{{ $company->email ?: '—' }}</div></div>
                    <div class="row"><div class="label">Telephone</div><div>{{ $company->telephone ?: '—' }}</div></div>
                    <div class="row"><div class="label">VAT Reg. number</div><div>{{ $company->vat_number ?: '—' }}</div></div>
                    <div class="row"><div class="label">Authentication code</div><div>{{ $company->authentication_code ?: '—' }}</div></div>
                    <div class="row"><div class="label">UTR</div><div>{{ $company->utr ?: '—' }}</div></div>
                    <div class="row"><div class="label">VAT period</div><div>{{ $company->vat_period ?: '—' }}</div></div>
                    <div class="row"><div class="label">VAT quarter end</div><div>{{ $company->vat_quarter_group ?: '—' }}</div></div>
                </div>
            </section>

            {{-- DEADLINES --}}
            <section id="tab-deadlines" class="tab-panel" hidden aria-labelledby="tabbtn-deadlines">
                @php
                    $fmt = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '—';
                    $pretty = fn($t) => $t === 'accounts'
                        ? 'Accounts'
                        : ($t === 'confirmation_statement' ? 'Confirmation statement' : ucwords(str_replace('_',' ',$t)));

                    $next = collect($nextDeadlines ?? []);
                    if ($next->isEmpty()) {
                        $rows = \App\Models\Deadline::where('company_id', $company->id)
                            ->whereIn('type', ['accounts','confirmation_statement'])
                            ->where(function($q){ $q->whereNull('status')->orWhereIn('status',['upcoming','overdue']); })
                            ->orderBy('type')->orderBy('due_on')->get();

                        $bucket = [];
                        foreach ($rows as $d) {
                            $key = $d->type;
                            if (!isset($bucket[$key])) $bucket[$key] = $d;
                            else {
                                $dDue = $d->due_on ? \Carbon\Carbon::parse($d->due_on) : null;
                                $cDue = $bucket[$key]->due_on ? \Carbon\Carbon::parse($bucket[$key]->due_on) : null;
                                if ($dDue && $cDue && $dDue->lt($cDue)) $bucket[$key] = $d;
                            }
                        }
                        $next = collect(array_values($bucket));
                    }
                @endphp

                <div class="card">
                    <h3>Deadlines (next)</h3>
                    @if($next->isEmpty())
                        <p class="muted">No upcoming deadlines.</p>
                    @else
                        <div class="table-wrap">
                            <table class="table">
                                <thead><tr><th>Type</th><th>Period end</th><th>Due</th><th>Status</th></tr></thead>
                                <tbody>
                                @foreach($next as $d)
                                    <tr>
                                        <td>{{ $pretty($d->type) }}</td>
                                        <td>{{ $fmt($d->period_end_on) }}</td>
                                        <td>{{ $fmt($d->due_on) }}</td>
                                        <td>{{ $d->status ?? 'upcoming' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- COMMUNICATIONS --}}
            <section id="tab-comms" class="tab-panel" hidden aria-labelledby="tabbtn-comms">
                <div class="card"><h3>Communications</h3><p class="muted">Hook to your email/CRM later.</p></div>
            </section>

            {{-- NOTES --}}
            <section id="tab-notes" class="tab-panel" hidden aria-labelledby="tabbtn-notes">
                <div class="card"><h3>Notes</h3><p class="muted">Internal notes placeholder.</p></div>
            </section>

            {{-- DOCUMENTS (S3 only) --}}
            <section id="tab-docs" class="tab-panel" hidden aria-labelledby="tabbtn-docs">
                <div class="card">
                    <h3>Documents</h3>
                    <p>
                        <a class="btn" href="{{ route('companies.documents', [$practice->slug, $company->id]) }}">
                            Open S3 Documents
                        </a>
                    </p>
                    <p class="muted" style="margin-top:8px">
                        Company documents are stored in S3. Use the button above to upload files or create folders.
                    </p>
                </div>
            </section>

            {{-- TASKS --}}
            <section id="tab-tasks" class="tab-panel" hidden aria-labelledby="tabbtn-tasks">
                <div class="card"><h3>Tasks</h3><p class="muted">Task list placeholder.</p></div>
            </section>

            {{-- BILLS --}}
            <section id="tab-bills" class="tab-panel" hidden aria-labelledby="tabbtn-bills">
                <div class="card"><h3>Bills</h3><p class="muted">Billing area placeholder.</p></div>
            </section>

        </div>
    </div>

    <script>
        // Tab toggling
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.tab-link');
            if (!btn) return;
            const tab = btn.dataset.tab;

            document.querySelectorAll('.tab-link').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.tab-panel').forEach(p => {
                p.classList.remove('active');
                p.setAttribute('hidden', 'hidden');
            });

            const panel = document.getElementById(`tab-${tab}`);
            if (panel) {
                panel.classList.add('active');
                panel.removeAttribute('hidden');
            }
        }, { passive:true });
    </script>
@endsection
