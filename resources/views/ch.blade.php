@extends('layouts.app')

@section('title','CH Search')

@section('content')
    <h1>CH Search</h1>

    {{-- Search bar --}}
    <form id="ch-search-form" class="cp-search" action="javascript:void(0)">
        <input id="ch-q" type="text" placeholder="Enter company name or number…" autocomplete="off">
        <button id="ch-btn" type="submit">Search</button>
    </form>

    <div id="ch-hint" class="muted" style="margin:8px 0 12px">
        Type a company name or number and press Enter.
    </div>

    {{-- Results --}}
    <div id="ch-results" class="cp-results"></div>

    {{-- Modal --}}
    <div id="company-card-modal" class="cp-modal-overlay" hidden>
        <div class="cp-modal">
            <button class="cp-modal-close" type="button" data-close aria-label="Close">&times;</button>
            <div id="company-card-body">Loading…</div>
        </div>
    </div>

    <style>
        .muted { color:#6b7280 }
        .cp-search { display:flex; gap:8px; margin:10px 0 16px }
        .cp-search input { flex:1; padding:10px 12px; border:1px solid #d1d5db; border-radius:8px }
        .cp-search button { padding:10px 14px; border:0; background:#111827; color:#fff; border-radius:8px; cursor:pointer }
        .cp-results { margin-top:6px }
        .cp-item { padding:12px 10px; border-bottom:1px solid #eee; }
        .cp-item a { text-decoration:none; color:#111827; font-weight:600 }
        .cp-item .sub { margin-top:4px; font-size:13px; color:#6b7280 }
        .cp-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.45);
            display: flex; align-items: flex-start; justify-content: center;
            padding: 4rem 1rem; z-index: 9999; opacity: 0; pointer-events: none; transition: opacity .15s ease;
        }
        .cp-modal-overlay.show { opacity: 1; pointer-events: auto; }
        .cp-modal {
            background:#fff; width:100%; max-width:840px; max-height:80vh;
            border-radius:10px; box-shadow:0 10px 40px rgba(0,0,0,.25);
            overflow:auto; padding:18px 22px;
        }
        .cp-modal-close { border:0; background:transparent; font-size:28px; line-height:1; float:right; cursor:pointer }
    </style>

    <script>
        (function () {
            const searchUrl = `{{ url('/p/'.$practice->slug.'/ch/search') }}`;
            const cardUrl   = `{{ url('/p/'.$practice->slug.'/company-card') }}`;

            const form   = document.getElementById('ch-search-form');
            const input  = document.getElementById('ch-q');
            const btn    = document.getElementById('ch-btn');
            const hint   = document.getElementById('ch-hint');
            const list   = document.getElementById('ch-results');

            const modal  = document.getElementById('company-card-modal');
            const body   = document.getElementById('company-card-body');

            function setLoading(on) {
                btn.disabled = !!on;
                btn.textContent = on ? 'Searching…' : 'Search';
            }

            function escapeHtml(s) {
                return (''+s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
            }

            function render(items) {
                if (!items || !items.length) {
                    list.innerHTML = '<div class="muted">No results.</div>';
                    return;
                }
                list.innerHTML = items.map(i => `
                    <div class="cp-item">
                        <a href="#" class="company-link" data-company-number="${i.number}">${escapeHtml(i.name || '')}</a>
                        <div class="sub">
                            ${escapeHtml(i.number || '')}
                            ${i.status ? ' — ' + escapeHtml(i.status) : ''}
                            ${i.date ? ' — ' + escapeHtml(i.date) : ''}
                            ${i.address ? '<br>'+escapeHtml(i.address) : ''}
                        </div>
                    </div>
                `).join('');
            }

            async function search(q) {
                if (!q) return;
                setLoading(true);
                if (!list.innerHTML) list.innerHTML = '';
                try {
                    const res  = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, {
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const json = await res.json();

                    if (json?.error === 'no-key') {
                        list.innerHTML = '<div class="muted">Set CH_API_KEY in your .env to enable search.</div>';
                        return;
                    }
                    render(json.items || []);
                } catch (e) {
                    console.error(e);
                    list.innerHTML = '<div class="muted">Error searching. Try again.</div>';
                } finally {
                    setLoading(false);
                }
            }

            // Submit still works (kept existing behaviour)
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                hint.textContent = '';
                search(input.value.trim());
            });

            // --- New: debounced "autocomplete" while typing (≥ 2 chars) ---
            function debounce(fn, wait = 300) {
                let t;
                return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
            }
            const live = debounce(() => {
                const q = input.value.trim();
                if (q.length < 2) {
                    list.innerHTML = '';
                    hint.textContent = 'Type a company name or number and press Enter.';
                    return;
                }
                hint.textContent = '';
                search(q);
            }, 300);
            input.addEventListener('input', live);

            // Modal helpers
            function show(html) {
                body.innerHTML = html;
                modal.hidden = false;
                modal.classList.add('show');
            }
            function hide() {
                modal.classList.remove('show');
                modal.hidden = true;
                body.innerHTML = '';
            }
            modal.addEventListener('click', (e) => {
                if (e.target === modal || e.target.closest('[data-close]')) hide();
            });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hide(); });

            // Open company card
            async function openCompanyCard(number) {
                if (!number) return;
                try {
                    const res  = await fetch(`${cardUrl}/${encodeURIComponent(number)}`, {
                        headers: {'X-Requested-With': 'XMLHttpRequest'}
                    });
                    const html = await res.text();
                    show(html);
                } catch (err) {
                    console.error(err);
                    show('<div class="cp-card"><p>Could not load company details.</p></div>');
                }
            }

            // Event delegation for clicks on results
            document.addEventListener('click', (e) => {
                const el = e.target.closest('.company-link, [data-company-number], [data-number], [data-company]');
                if (!el) return;
                e.preventDefault();
                let number = el.dataset.companyNumber || el.dataset.number;
                if (!number && el.dataset.company) {
                    try { number = JSON.parse(el.dataset.company)?.number; } catch(_) {}
                }
                openCompanyCard(number);
            });

            // Focus input on load
            input.focus();
        })();
    </script>
@endsection
