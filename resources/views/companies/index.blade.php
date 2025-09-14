{{-- resources/views/companies/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <style>
        /* Layout + table visuals */
        .cp-card { background:#fff; border:1px solid #e5e7eb; border-radius:8px; width:100%; }
        .cp-table { width:100%; border-collapse:separate; border-spacing:0; font-size:14px; table-layout:auto; }
        /* was min-width:1400px; removed so it can expand/contract with the viewport */

        .cp-table thead th {
            position: sticky; top: 0; z-index: 2;
            background:#f7f7f8; color:#111827; font-weight:600;
            border-bottom:1px solid #e5e7eb; padding:10px 12px; text-align:left; white-space:nowrap;
        }
        .cp-table tbody td { border-bottom:1px solid #f1f5f9; padding:10px 12px; vertical-align:middle; }
        .cp-table tbody tr:hover { background:#fafafa; }

        .cp-badge { display:inline-block; padding:2px 8px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:12px; }
        .cp-link { color:#2563eb; text-decoration:underline; }
        .cp-muted { color:#6b7280; }
        .cp-right { text-align:right; }
        .cp-nowrap { white-space:nowrap; }
        .cp-actions a { margin-right:10px; }
        .cp-pill { display:inline-block; padding:2px 8px; border:1px solid #e5e7eb; border-radius:999px; font-size:12px; color:#374151; background:#fff; }
        .cp-sticky-wrap { overflow:auto; max-height: calc(100vh - 220px); }
        .cp-heading { font-size:22px; font-weight:700; margin:0 0 14px; }
        .cp-subtle { font-size:12px; color:#6b7280; }
        .is-hidden { display:none !important; }

        th.cp-sort { cursor:pointer; user-select:none; }
        th.cp-sort .arrow { display:inline-block; margin-left:6px; font-size:11px; color:#9ca3af; }
        th.cp-sort.asc .arrow::after { content:"▲"; }
        th.cp-sort.desc .arrow::after { content:"▼"; }

        .cp-btn { appearance:none; border:1px solid #e5e7eb; background:#fff; padding:8px 12px; border-radius:8px; cursor:pointer; font-size:14px; color:#111827; }
        .cp-btn:hover { background:#f9fafb; }
        .cp-btn.small { padding:6px 10px; font-size:13px; }
        .cp-btn.danger { border-color:#ef4444; color:#b91c1c; }

        body.cp-modal-open { overflow: hidden; }
        .cp-modal-backdrop { position:fixed; inset:0; background:rgba(17,24,39,.45); display:none; align-items:center; justify-content:center; z-index:9998; }
        .cp-modal { width:520px; max-width:92vw; background:#fff; border-radius:10px; border:1px solid #e5e7eb; box-shadow:0 10px 30px rgba(0,0,0,.15); z-index:9999; }
        .cp-modal header { padding:14px 16px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; }
        .cp-modal header h3 { margin:0; font-size:16px; font-weight:700; }
        .cp-modal .cp-close { border:none; background:transparent; font-size:18px; cursor:pointer; color:#6b7280; }
        .cp-modal .cp-body { padding:12px 16px; max-height:68vh; overflow:auto; }
        .cp-toggle { display:flex; align-items:center; justify-content:space-between; padding:10px 6px; border-bottom:1px dashed #f0f2f5; }
        .cp-toggle:last-child { border-bottom:none; }
        .cp-toggle .label { font-size:14px; color:#111827; }

        .switch { position:relative; width:44px; height:24px; display:inline-block; }
        .switch input { position:absolute; opacity:0; inset:0; width:100%; height:100%; cursor:pointer; }
        .slider { position:absolute; inset:0; background:#e5e7eb; transition:.2s; border-radius:999px; }
        .slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; top:3px; background:white; transition:.2s; border-radius:50%; box-shadow:0 1px 2px rgba(0,0,0,.1); }
        .switch input:checked + .slider { background:#22c55e; }
        .switch input:checked + .slider:before { transform:translateX(20px); }

        /* selects */
        .cp-select { min-width:180px; padding:6px 8px; border:1px solid #e5e7eb; border-radius:8px; background:#fff; font-size:14px; }
        .cp-select.saving { opacity:0.6; }
        .cp-select.saved { outline:2px solid #22c55e; transition: outline-color .8s ease; }

        /* Widen the page on desktop */
        @media (min-width: 1024px){
            .container { max-width: none !important; width: 100% !important; }
        }
    </style>

    @php
        $prefKey = 'cp:companies:cols:' . ($practice->id ?? $practice->slug ?? 'default');

        $membersList = isset($members)
            ? $members
            : ($practice->members()->orderBy('users.name')->get());

        $companyUrl = function($practice, $company) {
            try {
                if (\Illuminate\Support\Facades\Route::has('practice.companies.show')) {
                    return route('practice.companies.show', [$practice, $company->slug ?? $company->id]);
                }
                if (\Illuminate\Support\Facades\Route::has('companies.show')) {
                    return route('companies.show', [$practice, $company]);
                }
            } catch (\Throwable $e) {}
            $p = $practice->slug ?? $practice->id ?? 'practice';
            $c = $company->slug ?? $company->id ?? 'company';
            return url('/p/'.$p.'/companies/'.$c);
        };

        $companiesList = isset($companies) ? $companies : (isset($practice) ? ($practice->companies ?? []) : []);
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- full-width container (previously capped at 1200px) --}}
    <div class="container" style="max-width:none;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
            <div>
                <h1 class="cp-heading">Companies</h1>
                @isset($practice)
                    <div class="cp-subtle">Practice: <strong>{{ $practice->name ?? '—' }}</strong></div>
                @endisset
            </div>
            <div style="display:flex; gap:8px;">
                <button id="cp-columns-btn" class="cp-btn" type="button">Columns</button>
            </div>
        </div>

        <div class="cp-card">
            <div class="cp-sticky-wrap">
                <table class="cp-table" id="companies-table" data-pref-key="{{ $prefKey }}">
                    <thead>
                    <tr>
                        <th class="cp-sort" data-key="company" data-col="client"><span>Client</span><span class="arrow"></span></th>
                        <th class="cp-sort" data-key="contact" data-col="full_name"><span>Full Name</span><span class="arrow"></span></th>
                        <th class="cp-sort" data-key="type" data-col="client_type"><span>Client Type</span><span class="arrow"></span></th>
                        <th data-col="partner"><span>Partner</span></th>
                        <th data-col="services"><span>Services</span></th>
                        <th class="cp-sort" data-key="manager" data-col="manager"><span>Manager</span><span class="arrow"></span></th>
                        <th data-col="links"><span>Client Links</span></th>
                        <th class="cp-sort cp-right" data-key="aml" data-col="aml"><span>Money Laundering Complete</span><span class="arrow"></span></th>
                        <th data-col="status"><span>Company Status</span></th>
                        <th data-col="incorporation_date"><span>Incorporation Date</span></th>
                        <th data-col="trading_as"><span>Company Trading As</span></th>
                        <th data-col="registered_address"><span>Registered Address</span></th>
                        <th data-col="postal_address"><span>Company Postal Address</span></th>
                        <th data-col="invoice_address"><span>Invoice Address</span></th>
                        <th data-col="company_email"><span>Company Email</span></th>
                        <th data-col="email_domain"><span>Company Email Domain</span></th>
                        <th data-col="telephone"><span>Company Telephone</span></th>
                        <th data-col="turnover_currency"><span>Company Turnover (Currency)</span></th>
                        <th data-col="date_of_trading"><span>Date of Trading</span></th>
                        <th data-col="sic_code"><span>SIC Code</span></th>
                        <th data-col="nature_of_business"><span>Nature of Business</span></th>
                        <th data-col="corp_tax_office"><span>Corporation Tax Office</span></th>
                        <th data-col="utr"><span>Company UTR / Tax Ref</span></th>
                        <th data-col="ch_auth"><span>CH Auth Code</span></th>
                        <th data-col="trading_address"><span>Trading Address</span></th>
                        <th data-col="commenced_trading_date"><span>Commenced Trading (Date)</span></th>
                        <th data-col="vat_number"><span>VAT Number</span></th>
                        <th data-col="accountant"><span>Accountant</span></th>
                        <th data-col="bookkeeper"><span>Bookkeeper</span></th>
                        <th data-col="reviewer"><span>Reviewer</span></th>
                        <th data-col="payroll_prepared"><span>Payroll Prepared By</span></th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($companiesList as $company)
                        @php
                            $name       = $company->name ?? '—';
                            $number     = $company->company_number ?? null;
                            $ctype      = $company->client_type ?? ($company->type ?? null);
                            $services   = $company->services_list ?? $company->services ?? null; if (is_array($services)) $services = implode(', ', $services);
                            $contact    = $company->primary_contact_name ?? ($company->contact_name ?? null);
                            $partner    = $company->partner_name ?? ($company->partner->name ?? null);
                            $aml        = $company->aml_complete ?? $company->kyc_complete ?? false;

                            $managerId    = $company->manager_id    ?? ($company->manager->id ?? null);
                            $accountantId = $company->accountant_id ?? ($company->accountant->id ?? null);
                            $bookkeeperId = $company->bookkeeper_id ?? ($company->bookkeeper->id ?? null);
                            $reviewerId   = $company->reviewer_id   ?? ($company->reviewer->id ?? null);
                            $payrollId    = $company->payroll_prepared_by_id ?? ($company->payrollPreparer->id ?? null);

                            $vatNumber = $company->vat_number ?? $company->vat_no ?? $company->vat ?? null;

                            $status     = $company->company_status ?? $company->status ?? null;
                            $incDateRaw = $company->incorporation_date ?? $company->date_of_creation ?? $company->created_at ?? null;
                            $incorporationDate = $incDateRaw ? \Carbon\Carbon::parse($incDateRaw)->format('d/m/Y') : null;
                            $tradingAs  = $company->trading_as ?? $company->company_trading_as ?? $company->aka ?? null;

                            $fmtAddr = function ($addr) {
                                if (is_string($addr)) {
                                    $json = json_decode($addr, true);
                                    if (json_last_error() === JSON_ERROR_NONE) $addr = $json;
                                }
                                if (is_array($addr)) {
                                    return implode(', ', array_filter([
                                        $addr['address_line_1'] ?? $addr['address_line1'] ?? null,
                                        $addr['address_line_2'] ?? $addr['address_line2'] ?? null,
                                        $addr['locality'] ?? $addr['town'] ?? null,
                                        $addr['region'] ?? null,
                                        $addr['postal_code'] ?? $addr['postcode'] ?? null,
                                        $addr['country'] ?? null,
                                    ]));
                                }
                                return is_string($addr) ? $addr : null;
                            };
                            $registeredAddress = $fmtAddr($company->registered_office_address ?? $company->address ?? null);
                            $postalAddress     = $fmtAddr($company->postal_address ?? $company->company_postal_address ?? null);
                            $invoiceAddress    = $fmtAddr($company->invoice_address ?? null);
                            $tradingAddress    = $fmtAddr($company->trading_address ?? null);

                            $companyEmail = $company->company_email ?? $company->email ?? null;
                            $emailDomain  = $companyEmail && str_contains($companyEmail,'@') ? substr(strrchr($companyEmail,'@'),1) : ($company->email_domain ?? null);
                            $telephone   = $company->telephone ?? $company->phone ?? $company->phone_number ?? null;
                            $turnoverVal = $company->turnover ?? $company->company_turnover ?? null;
                            $turnoverCur = $company->turnover_currency ?? $company->currency ?? null;
                            $turnoverCurrency = $turnoverVal && $turnoverCur ? (number_format((float)$turnoverVal, 0).' '.$turnoverCur) : ($turnoverVal ?? $turnoverCur);
                            $dateTradingRaw = $company->date_of_trading ?? null;
                            $dateOfTrading  = $dateTradingRaw ? \Carbon\Carbon::parse($dateTradingRaw)->format('d/m/Y') : null;
                            $sicCodes = $company->sic_codes ?? $company->sic_code ?? null;
                            if (is_array($sicCodes)) $sicCodes = implode(', ', $sicCodes);
                            $natureOfBusiness = $company->nature_of_business ?? $company->business_nature ?? $company->sic_text ?? null;
                            $corpTaxOffice = $company->corporation_tax_office ?? $company->corp_tax_office ?? null;
                            $utr       = $company->utr ?? $company->company_tax_reference ?? $company->tax_reference ?? $company->company_utr ?? null;
                            $chAuth    = $company->ch_auth_code ?? $company->cro_auth_code ?? $company->companies_house_auth_code ?? null;
                            $commencedRaw = $company->commenced_trading_date ?? $company->commenced_trading ?? null;
                            $commencedTradingDate = $commencedRaw ? \Carbon\Carbon::parse($commencedRaw)->format('d/m/Y') : null;

                            $showHref   = $companyUrl($practice ?? null, $company);
                            $chHref     = $number ? ("https://find-and-update.company-information.service.gov.uk/company/".urlencode($number)) : null;
                            $assignUrl  = route('practice.companies.assignUser', [$practice, $company->slug ?? $company->id]);
                        @endphp
                        <tr
                            data-company="{{ \Illuminate\Support\Str::lower($name) }}"
                            data-contact="{{ \Illuminate\Support\Str::lower($contact ?? '') }}"
                            data-type="{{ \Illuminate\Support\Str::lower($ctype ?? '') }}"
                            data-manager="{{ \Illuminate\Support\Str::lower(optional($membersList->firstWhere('id',$managerId))->name ?? '') }}"
                            data-aml="{{ $aml ? '1' : '0' }}"
                        >
                            <td class="cp-nowrap" data-col="client">
                                <a href="{{ $showHref }}" class="cp-link">{{ $name }}</a>
                                @if($number)<div class="cp-subtle">No. {{ $number }}</div>@endif
                            </td>
                            <td data-col="full_name">{{ $contact ?? '—' }}</td>
                            <td data-col="client_type">@if($ctype)<span class="cp-pill">{{ $ctype }}</span>@else — @endif</td>
                            <td data-col="partner">{{ $partner ?? '—' }}</td>
                            <td data-col="services">{{ $services ?? '—' }}</td>

                            <td data-col="manager">
                                <select class="cp-select cp-user-select"
                                        data-field="manager"
                                        data-endpoint="{{ $assignUrl }}">
                                    <option value="">—</option>
                                    @foreach ($membersList as $m)
                                        <option value="{{ $m->id }}" {{ (string)$m->id === (string)$managerId ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Links + Remove (inline) --}}
                            <td class="cp-nowrap cp-actions" data-col="links">
                                @if($chHref)<a href="{{ $chHref }}" target="_blank" class="cp-link">Companies House</a>@endif
                                @if(\Illuminate\Support\Facades\Route::has('companies.documents'))
                                    <a href="{{ route('companies.documents', [$practice, $company->slug ?? $company->id]) }}" class="cp-link">Documents</a>
                                @elseif(\Illuminate\Support\Facades\Route::has('practice.companies.docs.s3'))
                                    <a href="{{ route('practice.companies.docs.s3', [$practice, $company->slug ?? $company->id]) }}" class="cp-link">Documents</a>
                                @endif

                                <form method="POST"
                                      action="{{ url('/p/'.$practice->slug.'/companies/'.$company->id.'/detach') }}"
                                      onsubmit="return confirm('Remove this company from your practice?');"
                                      style="display:inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Detach</button>
                                </form>
                            </td>

                            <td class="cp-right" data-col="aml">@if($aml)<span class="cp-badge">Yes</span>@else<span class="cp-muted">No</span>@endif</td>

                            {{-- Toggleable extras --}}
                            <td data-col="status">{{ $status ?? '—' }}</td>
                            <td data-col="incorporation_date">{{ $incorporationDate ?? '—' }}</td>
                            <td data-col="trading_as">{{ $tradingAs ?? '—' }}</td>
                            <td data-col="registered_address">{{ $registeredAddress ?? '—' }}</td>
                            <td data-col="postal_address">{{ $postalAddress ?? '—' }}</td>
                            <td data-col="invoice_address">{{ $invoiceAddress ?? '—' }}</td>
                            <td data-col="company_email">
                                @if(!empty($companyEmail)) <a class="cp-link" href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a> @else — @endif
                            </td>
                            <td data-col="email_domain">{{ $emailDomain ?? '—' }}</td>
                            <td data-col="telephone">{{ $telephone ?? '—' }}</td>
                            <td data-col="turnover_currency">{{ $turnoverCurrency ?? '—' }}</td>
                            <td data-col="date_of_trading">{{ $dateOfTrading ?? '—' }}</td>
                            <td data-col="sic_code">{{ $sicCodes ?? '—' }}</td>
                            <td data-col="nature_of_business">{{ $natureOfBusiness ?? '—' }}</td>
                            <td data-col="corp_tax_office">{{ $corpTaxOffice ?? '—' }}</td>
                            <td data-col="utr">{{ $utr ?? '—' }}</td>
                            <td data-col="ch_auth">{{ $chAuth ?? '—' }}</td>
                            <td data-col="trading_address">{{ $tradingAddress ?? '—' }}</td>
                            <td data-col="commenced_trading_date">{{ $commencedTradingDate ?? '—' }}</td>
                            <td data-col="vat_number">{{ $vatNumber ?? '—' }}</td>

                            {{-- Role selects --}}
                            <td data-col="accountant">
                                <select class="cp-select cp-user-select"
                                        data-field="accountant"
                                        data-endpoint="{{ $assignUrl }}">
                                    <option value="">—</option>
                                    @foreach ($membersList as $m)
                                        <option value="{{ $m->id }}" {{ (string)$m->id === (string)$accountantId ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-col="bookkeeper">
                                <select class="cp-select cp-user-select"
                                        data-field="bookkeeper"
                                        data-endpoint="{{ $assignUrl }}">
                                    <option value="">—</option>
                                    @foreach ($membersList as $m)
                                        <option value="{{ $m->id }}" {{ (string)$m->id === (string)$bookkeeperId ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-col="reviewer">
                                <select class="cp-select cp-user-select"
                                        data-field="reviewer"
                                        data-endpoint="{{ $assignUrl }}">
                                    <option value="">—</option>
                                    @foreach ($membersList as $m)
                                        <option value="{{ $m->id }}" {{ (string)$m->id === (string)$reviewerId ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td data-col="payroll_prepared">
                                <select class="cp-select cp-user-select"
                                        data-field="payroll_prepared"
                                        data-endpoint="{{ $assignUrl }}">
                                    <option value="">—</option>
                                    @foreach ($membersList as $m)
                                        <option value="{{ $m->id }}" {{ (string)$m->id === (string)$payrollId ? 'selected' : '' }}>
                                            {{ $m->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="32" class="cp-muted">No companies yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Columns Modal -->
    <div id="cp-columns-modal" class="cp-modal-backdrop" aria-hidden="true">
        <div class="cp-modal" role="dialog" aria-modal="true" aria-labelledby="cp-columns-title">
            <header>
                <h3 id="cp-columns-title">Columns</h3>
                <button class="cp-close" type="button" aria-label="Close" id="cp-columns-close">×</button>
            </header>
            <div class="cp-body">
                {{-- Core --}}
                @foreach ([
                    ['client','Client',true],
                    ['full_name','Full Name',true],
                    ['client_type','Client Type',true],
                    ['partner','Partner',false],
                    ['services','Services',true],
                    ['manager','Manager',true],
                    ['links','Client Links',true],
                    ['aml','Money Laundering Complete',true],
                ] as [$col,$label,$on])
                    <div class="cp-toggle">
                        <span class="label">{{ $label }}</span>
                        <label class="switch"><input type="checkbox" data-col="{{ $col }}" {{ $on ? 'checked' : '' }}><span class="slider"></span></label>
                    </div>
                @endforeach

                {{-- Extra --}}
                @foreach ([
                    ['status','Company Status',false],
                    ['incorporation_date','Incorporation Date',false],
                    ['trading_as','Company Trading As',false],
                    ['registered_address','Registered Address',false],
                    ['postal_address','Company Postal Address',false],
                    ['invoice_address','Invoice Address',false],
                    ['company_email','Company Email',false],
                    ['email_domain','Company Email Domain',false],
                    ['telephone','Company Telephone',false],
                    ['turnover_currency','Company Turnover (Currency)',false],
                    ['date_of_trading','Date of Trading',false],
                    ['sic_code','SIC Code',false],
                    ['nature_of_business','Nature of Business',false],
                    ['corp_tax_office','Corporation Tax Office',false],
                    ['utr','Company UTR / Tax Ref',false],
                    ['ch_auth','CH Auth Code',false],
                    ['trading_address','Trading Address',false],
                    ['commenced_trading_date','Commenced Trading (Date)',false],
                    ['vat_number','VAT Number',false],
                    ['accountant','Accountant',false],
                    ['bookkeeper','Bookkeeper',false],
                    ['reviewer','Reviewer',false],
                    ['payroll_prepared','Payroll Prepared By',false],
                ] as [$col,$label,$on])
                    <div class="cp-toggle">
                        <span class="label">{{ $label }}</span>
                        <label class="switch"><input type="checkbox" data-col="{{ $col }}" {{ $on ? 'checked' : '' }}><span class="slider"></span></label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function () {
            const tbl = document.getElementById('companies-table');
            if (!tbl) return;

            // sorting
            const getRows = () => Array.from(tbl.tBodies[0].rows);
            function sortBy(key, th){
                const rows = getRows();
                const current = th.classList.contains('asc') ? 'asc' : (th.classList.contains('desc') ? 'desc' : null);
                const nextDir = current === 'asc' ? 'desc' : 'asc';
                Array.from(tbl.tHead.rows[0].cells).forEach(c => c.classList.remove('asc','desc'));
                rows.sort((a,b)=>{
                    let av=(a.dataset[key]||'').toString(), bv=(b.dataset[key]||'').toString();
                    if (key==='aml'){ av=parseInt(av||'0',10); bv=parseInt(bv||'0',10); return nextDir==='asc'?(av-bv):(bv-av); }
                    return nextDir==='asc'? av.localeCompare(bv) : bv.localeCompare(av);
                }).forEach(r=>tbl.tBodies[0].appendChild(r));
                th.classList.add(nextDir);
            }
            Array.from(tbl.tHead.rows[0].cells).forEach(th=>{
                if (th.classList.contains('cp-sort')) th.addEventListener('click', ()=>sortBy(th.dataset.key, th));
            });

            // modal
            const openBtn=document.getElementById('cp-columns-btn'),
                modal=document.getElementById('cp-columns-modal'),
                closeBtn=document.getElementById('cp-columns-close');
            function open(){ modal.style.display='flex'; document.body.classList.add('cp-modal-open'); }
            function close(){ modal.style.display='none'; document.body.classList.remove('cp-modal-open'); }
            openBtn&&openBtn.addEventListener('click', open);
            closeBtn&&closeBtn.addEventListener('click', close);
            modal&&modal.addEventListener('click', e=>{ if(e.target===modal) close(); });

            // column prefs
            const storageKey = tbl.dataset.prefKey || 'cp:companies:cols:default';
            function setVisible(col, on){ tbl.querySelectorAll('[data-col="'+col+'"]').forEach(el=>el.classList.toggle('is-hidden', !on)); }
            function load(){ try{const r=localStorage.getItem(storageKey); return r?JSON.parse(r):{};}catch{return{}} }
            function save(p){ localStorage.setItem(storageKey, JSON.stringify(p)); }

            const defaults = {
                client:true, full_name:true, client_type:true, partner:false, services:true, manager:true, links:true, aml:true,
                status:false, incorporation_date:false, trading_as:false, registered_address:false, postal_address:false,
                invoice_address:false, company_email:false, email_domain:false, telephone:false, turnover_currency:false,
                date_of_trading:false, sic_code:false, nature_of_business:false, corp_tax_office:false, utr:false, ch_auth:false,
                trading_address:false, commenced_trading_date:false, vat_number:false, accountant:false, bookkeeper:false,
                reviewer:false, payroll_prepared:false
            };
            const prefs = Object.assign({}, defaults, load());
            Object.keys(defaults).forEach(col => setVisible(col, prefs[col] !== false));

            document.querySelectorAll('#cp-columns-modal .switch input[data-col]').forEach(cb=>{
                const col=cb.dataset.col; cb.checked=prefs[col]!==false;
                cb.addEventListener('change', ()=>{ prefs[col]=cb.checked; save(prefs); setVisible(col, cb.checked); });
            });

            // AJAX assign user on selects
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            document.querySelectorAll('.cp-user-select').forEach(sel=>{
                sel.addEventListener('change', async ()=>{
                    const url   = sel.dataset.endpoint;
                    const field = sel.dataset.field;
                    const user_id = sel.value || null;

                    sel.classList.add('saving');
                    try{
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ field, user_id })
                        });
                        if(!res.ok) throw new Error('Save failed');

                        if (field === 'manager') {
                            const row = sel.closest('tr');
                            const name = sel.options[sel.selectedIndex]?.text || '';
                            if (row) row.dataset.manager = (name || '').toLowerCase();
                        }

                        sel.classList.remove('saving');
                        sel.classList.add('saved');
                        setTimeout(()=>sel.classList.remove('saved'), 900);
                    }catch(e){
                        sel.classList.remove('saving');
                        alert('Could not save. Please try again.');
                    }
                });
            });
        })();
    </script>
@endsection
