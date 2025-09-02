<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name', 'clearpractice') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu; background:#fff; color:#111; }
        header { height:52px; display:flex; align-items:center; justify-content:space-between; padding:0 16px; border-bottom:1px solid #eee; }
        .wrap { display:flex; }
        .sidebar {
            width:180px; border-right:1px solid #eee; min-height:calc(100dvh - 52px);
            padding:12px 8px; display:flex; flex-direction:column; gap:6px;
        }
        .sidebar a { display:block; padding:8px 10px; border-radius:6px; text-decoration:none; color:#111; }
        .sidebar a.active, .sidebar a:hover { background:#f3f4f6; }
        main { flex:1; padding:22px; }
        .card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#fff; }
        .btn { border:1px solid #d1d5db; border-radius:6px; padding:6px 10px; background:#fff; cursor:pointer; }
        .btn.primary { background:#111827; color:#fff; border-color:#111827; }
        .pill { font-size:12px; padding:2px 8px; border-radius:999px; border:1px solid #d1d5db; }
        .muted { color:#6b7280; }
        .flash { padding:10px 12px; border-radius:8px; margin:8px 0; }
        .flash.ok { background:#ecfdf5; border:1px solid #10b98133; }
        .flash.err { background:#fef2f2; border:1px solid #ef444433; }
        .flash.info { background:#eff6ff; border:1px solid #3b82f633; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:10px; border-bottom:1px solid #f1f5f9; }

        /* user chip at bottom-left */
        .user-chip { margin-top:auto; padding:10px 8px; border-top:1px solid #eee; display:flex; gap:8px; align-items:center; }
        .avatar { width:28px; height:28px; border-radius:50%; display:grid; place-items:center; font-size:12px; font-weight:600; color:#fff; background:#111827; }
        .user-info { min-width:0 }
        .user-name { font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-email { font-size:12px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>
</head>
<body>
<header>
    <div>
        <strong>{{ config('app.name', 'clearpractice') }}</strong>
        @isset($practice)
            <span class="muted" style="margin-left:8px">— {{ $practice->name }}</span>
        @endisset
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn" type="submit">Logout</button>
    </form>
</header>

<div class="wrap">
    <aside class="sidebar">
        @isset($practice)
            {{-- Practice‑scoped nav --}}
            <a href="{{ route('practice.ch.page', $practice->slug) }}" class="{{ request()->routeIs('practice.ch.page') ? 'active' : '' }}">CH Search</a>
            <a href="{{ route('practice.companies.index', $practice->slug) }}" class="{{ request()->routeIs('practice.companies.*') ? 'active' : '' }}">Companies</a>
            <a href="{{ route('practice.clients.index', $practice->slug) }}" class="{{ request()->routeIs('practice.clients.*') ? 'active' : '' }}">Clients</a>
            <a href="{{ route('practice.tasks.index', $practice->slug) }}" class="{{ request()->routeIs('practice.tasks.*') ? 'active' : '' }}">Tasks</a>
            <a href="{{ route('practice.users.index', $practice->slug) }}" class="{{ request()->routeIs('practice.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('practice.deadlines.index', $practice->slug) }}" class="{{ request()->routeIs('practice.deadlines.*') ? 'active' : '' }}">Deadlines</a>

            {{-- Signed-in user chip (bottom) --}}
            @auth
                @php
                    $initials = strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1));
                @endphp
                <div class="user-chip" title="{{ auth()->user()->name ?? auth()->user()->email }}">
                    <div class="avatar">{{ $initials }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? '—' }}</div>
                        <div class="user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            @endauth
        @else
            {{-- Fallback nav when no practice is in context (e.g. landing/login) --}}
            <a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'active' : '' }}">Home</a>
            @auth
                <a href="{{ route('users.index') }}">Users</a>
                <a href="{{ route('companies.index') }}">Companies</a>
                <a href="{{ route('clients.index') }}">Clients</a>
                <a href="{{ route('tasks.index') }}">Tasks</a>
                <a href="{{ route('deadlines.index') }}">Deadlines</a>
            @endauth
        @endisset
    </aside>

    <main>
        @yield('content')
    </main>
</div>

{{-- ******************************************************************
   Only addition: robust script that injects the two buttons.
   - Scopes strictly to the open modal (centered, wide container)
   - Places "Add to my companies" right after the title (or before "No:" as fallback)
******************************************************************* --}}
<script>
    (function () {
        // --- helpers ---
        function csrf() {
            const m = document.querySelector('meta[name="csrf-token"]'); return m ? m.content : '';
        }
        async function postJSON(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify(data || {}), credentials: 'same-origin'
            });
            let payload = {}; try { payload = await res.json(); } catch(e) {}
            if (!res.ok) throw new Error((payload && payload.message) || res.statusText);
            return payload;
        }
        function toast(msg, bad) {
            const el = document.createElement('div');
            el.textContent = msg;
            Object.assign(el.style, {
                position:'fixed', right:'16px', bottom:'16px', padding:'10px 14px',
                borderRadius:'6px', background: bad ? '#b00020' : '#2e7d32', color:'#fff',
                zIndex: 2147483647, boxShadow:'0 4px 12px rgba(0,0,0,.2)', fontSize:'14px'
            });
            document.body.appendChild(el); setTimeout(()=>el.remove(), 3200);
        }

        // --- find the correct CH modal card (wide, centered, contains the unique labels) ---
        function locateCard() {
            const nodes = Array.from(document.querySelectorAll('div,section,article')).filter(el => {
                const t = el.textContent || '';
                if (!(t.includes('No:') && t.includes('Accounts due') && t.includes('Confirmation due') && t.includes('Directors'))) return false;
                const r = el.getBoundingClientRect();
                return r.width > 480 && r.height > 200; // avoid sidebar/mini blocks
            });
            if (!nodes.length) return null;

            const cx = window.innerWidth / 2, cy = window.innerHeight / 2;
            nodes.sort((a,b) => {
                const ra = a.getBoundingClientRect(), rb = b.getBoundingClientRect();
                const da = Math.hypot((ra.left+ra.right)/2 - cx, (ra.top+ra.bottom)/2 - cy);
                const db = Math.hypot((rb.left+rb.right)/2 - cx, (rb.top+rb.bottom)/2 - cy);
                return da - db;
            });
            return nodes[0];
        }

        function companyNumberFrom(card) {
            const t = card.textContent || '';
            const m = t.match(/\bNo:\s*([A-Z0-9]+)\b/i);
            return m ? m[1] : '';
        }

        // Prefer a heading that is in the same block as the "No:" line; else the element before "No:"; else first element
        function findTitleNode(card) {
            const headings = card.querySelectorAll('h1,h2,h3,h4,h5,h6');
            for (const h of headings) {
                const block = h.parentElement?.textContent || '';
                if (/\bNo:\s*[A-Z0-9]+/i.test(block)) return h;
            }
            const noEl = Array.from(card.querySelectorAll('*')).find(el => /\bNo:\s*[A-Z0-9]+/i.test(el.textContent || ''));
            if (noEl && noEl.previousElementSibling) return noEl.previousElementSibling;
            return card.firstElementChild || card;
        }

        function ensureButtons(card) {
            // Remove any stray button outside the modal from previous runs
            document.querySelectorAll('#cp-btn-add-company').forEach(b => { if (!card.contains(b)) b.remove(); });

            // --- Add to my companies ---
            if (!card.querySelector('#cp-btn-add-company')) {
                const titleNode = findTitleNode(card);
                const btn = document.createElement('button');
                btn.id = 'cp-btn-add-company';
                btn.type = 'button';
                btn.className = 'btn primary';
                btn.textContent = 'Add to my companies';
                btn.style.marginLeft = '12px';
                btn.style.display = 'inline-block';
                btn.style.verticalAlign = 'middle';

                if (titleNode && titleNode.insertAdjacentElement) {
                    titleNode.insertAdjacentElement('afterend', btn); // place right after the title line
                } else {
                    // last resort: before the "No:" line
                    const noEl = Array.from(card.querySelectorAll('*')).find(el => /\bNo:\s*[A-Z0-9]+/i.test(el.textContent || ''));
                    if (noEl && noEl.parentNode) noEl.parentNode.insertBefore(btn, noEl);
                    else card.insertBefore(btn, card.firstChild);
                }
            }

            // --- Add to your clients next to each director li ---
            const heads = card.querySelectorAll('h1,h2,h3,h4,h5,h6,strong,b');
            let list = null;
            for (const h of heads) {
                if (/^\s*Directors\s*$/i.test(h.textContent.trim())) {
                    let n = h.nextElementSibling;
                    while (n && !/^(UL|OL)$/i.test(n.tagName)) n = n.nextElementSibling;
                    list = n; break;
                }
            }
            if (list) {
                list.querySelectorAll('li').forEach(li => {
                    if (li.querySelector('.cp-btn-add-client')) return;
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'btn cp-btn-add-client';
                    b.textContent = 'Add to your clients';
                    b.style.marginLeft = '8px'; b.style.fontSize = '12px'; b.style.padding = '4px 8px';
                    li.appendChild(b);
                });
            }
        }

        function enhance() {
            const card = locateCard();
            if (card) ensureButtons(card);
        }

        // Run now and on DOM mutations (covers opening the modal)
        enhance();
        const obs = new MutationObserver(enhance);
        obs.observe(document.body, { childList:true, subtree:true });

        // --- click handlers ---
        document.addEventListener('click', async (ev) => {
            // Add company
            const addCo = ev.target.closest('#cp-btn-add-company');
            if (addCo) {
                const card = locateCard(); const cn = card ? companyNumberFrom(card) : '';
                if (!cn) { toast('Could not detect company number.', true); return; }
                addCo.disabled = true; const t = addCo.textContent; addCo.textContent = 'Adding...';
                try {
                    const res = await postJSON('/companies/from-ch', { company_number: cn });
                    toast(res.message || 'Company added'); addCo.textContent = 'Added';
                } catch(e) {
                    toast('Failed: ' + e.message, true); addCo.textContent = t; addCo.disabled = false;
                }
                return;
            }

            // Add director as client
            const addCl = ev.target.closest('.cp-btn-add-client');
            if (addCl) {
                const li = addCl.closest('li');
                let name = '';
                if (li) {
                    const raw = (li.firstChild && li.firstChild.textContent) ? li.firstChild.textContent : li.textContent;
                    const m = raw.match(/^\s*([^—(]+)/); name = (m ? m[1] : raw).trim();
                }
                const card = locateCard();
                const cn = card ? companyNumberFrom(card) : '';
                const titleNode = card ? findTitleNode(card) : null;
                const coName = titleNode ? (titleNode.textContent || '').trim() : null;

                if (!name) { toast('Could not detect the director name.', true); return; }

                addCl.disabled = true; const t = addCl.textContent; addCl.textContent = 'Adding...';
                try {
                    const res = await postJSON('/clients/from-ch', { name, company_number: cn || null, company_name: coName || null });
                    toast(res.message || 'Client added'); addCl.textContent = 'Added';
                } catch(e) {
                    toast('Failed: ' + e.message, true); addCl.textContent = t; addCl.disabled = false;
                }
            }
        });
    })();
</script>
</body>
</html>
