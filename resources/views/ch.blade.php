<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Companies House Search</title>
    <style>
        :root {
            --bg: #0b0d10;
            --panel: #15181d;
            --panel-2: #1b2027;
            --text: #e9eef3;
            --muted: #a7b0bb;
            --accent: #6ea8fe;
            --border: #2a323b;
            --pill: #ecf2ff;
            --pilltext: #264a9b;
        }
        html, body { height: 100%; }
        body {
            margin: 0; background: var(--bg); color: var(--text);
            font: 16px/1.45 system-ui, -apple-system, Segoe UI, Roboto, Inter, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
        }
        .wrap { max-width: 1200px; margin: 28px auto; padding: 0 16px; }
        h1 { margin: 0 0 16px; font-weight: 800; letter-spacing: .2px; }
        .search {
            width: 100%; border-radius: 12px; padding: 16px 18px; color: var(--text);
            background: var(--panel); border: 1px solid var(--border);
            outline: none; font-size: 18px; box-sizing: border-box;
        }
        .list { margin-top: 18px; display: grid; gap: 10px; }
        .item {
            background: var(--panel); border: 1px solid var(--border);
            border-radius: 14px; padding: 16px 18px; cursor: pointer;
            display: grid; grid-template-columns: 1fr auto; align-items: center;
        }
        .item:hover { background: var(--panel-2); }
        .name { font-weight: 700; margin-bottom: 4px; }
        .addr { color: var(--muted); font-size: 14px; }
        .badge {
            background: var(--pill); color: var(--pilltext);
            padding: 6px 10px; border-radius: 999px; font-weight: 700; font-size: 13px;
        }
        .empty {
            margin-top: 18px; color: var(--muted);
            border: 1px dashed var(--border); border-radius: 14px;
            padding: 16px 18px; background: var(--panel);
        }

        /* Modal */
        .modal-backdrop {
            position: fixed; inset: 0; background: rgba(0,0,0,.55);
            display: none; align-items: center; justify-content: center; z-index: 1000;
        }
        .modal {
            width: min(1100px, calc(100vw - 32px));
            max-height: calc(100vh - 32px);
            background: var(--panel); color: var(--text);
            border: 1px solid var(--border); border-radius: 18px; overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        .modal-hd { padding: 16px 18px; border-bottom: 1px solid var(--border); display: flex; gap: 12px; align-items: center; justify-content: space-between; }
        .modal-ttl { font-weight: 800; }
        .modal-bd { padding: 0; max-height: calc(100vh - 32px - 56px); overflow: auto; }
        .section { padding: 16px 18px; border-top: 1px solid var(--border); }
        .grid2 { display: grid; grid-template-columns: 280px 1fr; gap: 10px; }
        .key { color: var(--muted); font-size: 14px; }
        .val { font-weight: 600; }
        .h3 { font-weight: 800; margin: 10px 0 12px; }
        .rows { display: grid; gap: 10px; }
        .row {
            background: #12161b; border: 1px solid var(--border); border-radius: 12px;
            padding: 10px 12px; display: grid; gap: 3px; font-size: 14px;
        }
        .closebtn { background: transparent; color: var(--text); border: 1px solid var(--border); padding: 8px 10px; border-radius: 10px; cursor: pointer; }
        .link { color: var(--accent); text-decoration: none; }
        .muted { color: var(--muted); }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Companies House Search</h1>
    <input id="q" class="search" type="text" placeholder="Type a company name or number…" autocomplete="off" />

    <div id="results" class="list" aria-live="polite"></div>
    <div id="empty" class="empty" style="display:none;">Start typing to search Companies House…</div>
</div>

<!-- Modal -->
<div id="backdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="m-title">
    <div class="modal">
        <div class="modal-hd">
            <div class="modal-ttl" id="m-title">Company</div>
            <div style="display:flex; gap:8px;">
                <a id="m-open-ch" class="closebtn link" target="_blank" rel="noopener">Open on Companies House ↗</a>
                <button class="closebtn" data-close>Close</button>
            </div>
        </div>
        <div class="modal-bd" id="m-body">
            <!-- filled by JS -->
        </div>
    </div>
</div>

<script>
(function () {
    const qEl = document.getElementById('q');
    const listEl = document.getElementById('results');
    const emptyEl = document.getElementById('empty');

    const backdrop = document.getElementById('backdrop');
    const mBody    = document.getElementById('m-body');
    const mTitle   = document.getElementById('m-title');
    const mOpenCH  = document.getElementById('m-open-ch');

    // ---------- helpers ----------
    const debounce = (fn, ms=300) => {
        let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
    };
    const esc = (s) => (s ?? '').toString()
        .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');

    const none = (arr) => !arr || !Array.isArray(arr) || arr.length === 0;

    // Render a person row (officer or psc)
    function personRow(p) {
        const bits = [];
        if (p.role) bits.push(`<span class="muted">Role:</span> ${esc(p.role)}`);
        if (p.appointed) bits.push(`<span class="muted">Appointed:</span> ${esc(p.appointed)}`);
        if (p.resigned) bits.push(`<span class="muted">Resigned:</span> ${esc(p.resigned)}`);
        if (p.nationality) bits.push(`<span class="muted">Nationality:</span> ${esc(p.nationality)}`);
        if (p.occupation) bits.push(`<span class="muted">Occupation:</span> ${esc(p.occupation)}`);
        if (p.country) bits.push(`<span class="muted">Country:</span> ${esc(p.country)}`);

        if (p.natures_of_control && p.natures_of_control.length) {
            bits.push(`<span class="muted">Control:</span> ${esc(p.natures_of_control.join(', '))}`);
        } else if (p.control && p.control.length) {
            bits.push(`<span class="muted">Control:</span> ${esc(p.control.join(', '))}`);
        }

        return `
          <div class="row">
            <div style="font-weight:700">${esc(p.name || '')}</div>
            ${bits.map(b => `<div>${b}</div>`).join('')}
          </div>`;
    }

    function section(title, contentHtml) {
        return `
            <div class="section">
                <div class="h3">${esc(title)}</div>
                ${contentHtml}
            </div>`;
    }

    function keyvalGrid(map) {
        return `
         <div class="section grid2">
           ${Object.entries(map).map(([k,v]) =>
             `<div class="key">${esc(k)}</div><div class="val">${v ? esc(v) : '<span class="muted">—</span>'}</div>`
           ).join('')}
         </div>`;
    }

    function openModal() { backdrop.style.display = 'flex'; }
    function closeModal() { backdrop.style.display = 'none'; }

    // ---------- search ----------
    async function doSearch(q) {
        if (!q) {
            listEl.innerHTML = '';
            emptyEl.style.display = 'block';
            return;
        }
        emptyEl.style.display = 'none';
        listEl.innerHTML = '<div class="empty">Searching…</div>';

        const r = await fetch(`/api/ch?q=${encodeURIComponent(q)}`);
        if (!r.ok) {
            listEl.innerHTML = `<div class="empty">Search failed (${r.status}).</div>`;
            return;
        }
        const json = await r.json();
        const items = json.data || [];

        if (!items.length) {
            listEl.innerHTML = `<div class="empty">No results.</div>`;
            return;
        }

        listEl.innerHTML = items.map(it => `
            <div class="item result" data-number="${esc(it.number || '')}">
                <div>
                    <div class="name">${esc(it.name || '')}</div>
                    <div class="addr">${esc(it.address || '')}</div>
                </div>
                <div class="badge">${esc(it.number || '')}</div>
            </div>
        `).join('');
    }

    qEl.addEventListener('input', debounce(e => doSearch(e.target.value.trim()), 300));
    // Trigger empty state initially
    doSearch('');

    // ---------- click handling (EVENT DELEGATION!) ----------
    document.addEventListener('click', async (e) => {
        // close buttons
        if (e.target.closest('[data-close]')) {
            closeModal(); return;
        }
        if (e.target === backdrop) { closeModal(); return; }

        // result row
        const row = e.target.closest('.result[data-number]');
        if (!row) return;

        const number = row.getAttribute('data-number');
        if (!number) return;

        // fetch full
        mTitle.textContent = 'Company';
        mBody.innerHTML = '<div class="section">Loading…</div>';
        mOpenCH.href = `https://find-and-update.company-information.service.gov.uk/company/${encodeURIComponent(number)}`;
        openModal();

        try {
            const r = await fetch(`/api/ch?q=${encodeURIComponent(number)}&full=1`);
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const json = await r.json();
            const d = json.data || {};

            mTitle.textContent = d.name || 'Company';

            // Top stuff
            const top = keyvalGrid({
                'Number': d.number,
                'Status': d.status,
                'Type': d.type,
                'Created': d.created,
                'Registered address': d.address,
                'Accounts — next due': d.accounts?.next_due,
                'Confirmation stmt — next made up to': d.confirmation_statement?.next_made_up_to,
                'Confirmation stmt — overdue': d.confirmation_statement?.overdue ? 'true' : 'false'
            });

            // Officers
            const offActive = d.officers_active || [];
            const offRes    = d.officers_resigned || [];

            const sOffActive = section(
                'Directors & Officers — Active',
                none(offActive) ? `<div class="muted">None</div>` : `<div class="rows">${offActive.map(personRow).join('')}</div>`
            );

            const sOffRes = section(
                'Directors & Officers — Resigned',
                none(offRes) ? `<div class="muted">None</div>` : `<div class="rows">${offRes.map(personRow).join('')}</div>`
            );

            // PSCs
            const pscC = d.pscs_current || [];
            const pscF = d.pscs_former || [];

            const sPscC = section(
                'Persons with Significant Control — Current',
                none(pscC) ? `<div class="muted">None</div>` : `<div class="rows">${pscC.map(personRow).join('')}</div>`
            );

            const sPscF = section(
                'Persons with Significant Control — Former',
                none(pscF) ? `<div class="muted">None</div>` : `<div class="rows">${pscF.map(personRow).join('')}</div>`
            );

            mBody.innerHTML = top + sOffActive + sOffRes + sPscC + sPscF;
        } catch (err) {
            mBody.innerHTML = `<div class="section">Failed to load company details. ${esc(err.message)}</div>`;
        }
    });
})();
</script>
</body>
</html>
