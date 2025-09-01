/* resources/js/ch-modal.js
   - Injects "Add to my companies" near the company title inside the CH modal
   - Injects "Add to your clients" next to each director
   - Posts to backend endpoints without changing existing views
*/

(function () {
    function csrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    async function postJSON(url = '', data = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data),
            credentials: 'same-origin'
        });
        let payload = {};
        try { payload = await res.json(); } catch (_) {}
        if (!res.ok) {
            const msg = (payload && payload.message) ? payload.message : res.statusText;
            throw new Error(msg);
        }
        return payload;
    }

    function toast(msg, isError = false) {
        const el = document.createElement('div');
        el.textContent = msg;
        Object.assign(el.style, {
            position: 'fixed',
            right: '16px',
            bottom: '16px',
            padding: '10px 14px',
            borderRadius: '6px',
            background: isError ? '#b00020' : '#2e7d32',
            color: '#fff',
            zIndex: 99999,
            boxShadow: '0 4px 12px rgba(0,0,0,.2)',
            fontSize: '14px'
        });
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3200);
    }

    // --- Helpers to read details from the modal card ---
    function extractCompanyInfo(root) {
        // Title is the big company name (first h1..h6)
        const titleEl = root.querySelector('h1,h2,h3,h4,h5,h6');
        const companyName = titleEl ? titleEl.textContent.trim() : '';

        // Look for "No: 01234567" anywhere in text
        const text = root.innerText || '';
        const m = text.match(/\bNo:\s*([A-Z0-9]+)\b/i);
        const companyNumber = m ? m[1] : '';

        return { companyName, companyNumber, titleEl };
    }

    function findDirectorLists(root) {
        // Find any heading that says "Directors" and the next <ul>/<ol>
        const lists = [];
        const candidates = root.querySelectorAll('h1,h2,h3,h4,h5,h6,strong,b');
        candidates.forEach(h => {
            if (/^\s*Directors\s*$/i.test(h.textContent.trim())) {
                let n = h.nextElementSibling;
                while (n && !/^(UL|OL)$/i.test(n.tagName)) n = n.nextElementSibling;
                if (n) lists.push(n);
            }
        });
        return lists;
    }

    function inferPersonNameFromLI(li) {
        // Read the LI text and take the portion before " (" or " —"
        const raw = (li.firstChild && li.firstChild.textContent) ? li.firstChild.textContent : li.textContent;
        const m = raw.match(/^\s*([^—(]+)/);
        return (m ? m[1] : raw).trim();
    }

    // --- Inject buttons into the CH card (idempotent) ---
    function enhanceCompanyCard(root) {
        if (!root || root.dataset.chEnhanced === '1') return;

        const { companyName, companyNumber, titleEl } = extractCompanyInfo(root);
        if (!companyName) return;

        // 1) Add "Add to my companies" button near the title
        if (titleEl && !root.querySelector('#btn-add-company')) {
            const btn = document.createElement('button');
            btn.id = 'btn-add-company';
            btn.type = 'button';
            btn.textContent = 'Add to my companies';
            btn.className = 'btn btn-sm btn-primary';
            btn.style.marginLeft = '12px';
            btn.dataset.companyNumber = companyNumber;
            btn.dataset.companyName = companyName;
            titleEl.insertAdjacentElement('afterend', btn);
        }

        // 2) Add "Add to your clients" button next to each director item
        const directorLists = findDirectorLists(root);
        directorLists.forEach(ul => {
            ul.querySelectorAll('li').forEach(li => {
                if (li.querySelector('.btn-add-client')) return; // already added
                const name = inferPersonNameFromLI(li);
                if (!name) return;

                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = 'Add to your clients';
                b.className = 'btn btn-xs btn-outline-secondary btn-add-client';
                b.style.marginLeft = '8px';
                b.dataset.clientName = name;
                b.dataset.companyNumber = companyNumber || '';
                b.dataset.companyName = companyName || '';
                li.appendChild(b);
            });
        });

        root.dataset.chEnhanced = '1';
    }

    // Try to enhance immediately for already open modal,
    // and also observe DOM mutations (works no matter how the modal content is injected)
    function tryEnhanceNow() {
        const modals = Array.from(document.querySelectorAll('.modal-content, .modal, .card'))
            .filter(el => /Directors/i.test(el.textContent || ''));
        modals.forEach(enhanceCompanyCard);
    }
    tryEnhanceNow();

    const obs = new MutationObserver((mutations) => {
        for (const m of mutations) {
            m.addedNodes.forEach(node => {
                if (node.nodeType !== 1) return;
                // Look only at reasonably small subtrees
                const container = node.matches?.('.modal-content, .modal, .card') ? node : node.querySelector?.('.modal-content, .modal, .card');
                if (!container) return;
                if (/Directors/i.test((container.textContent || ''))) {
                    enhanceCompanyCard(container);
                }
            });
        }
    });
    obs.observe(document.body, { childList: true, subtree: true });

    // --- Click handlers ---

    // Add company (existing endpoint)
    document.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('#btn-add-company');
        if (!btn) return;

        const companyNumber = btn.dataset.companyNumber || '';
        if (!companyNumber) {
            toast('No company number found on this card.', true);
            return;
        }

        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Adding...';

        try {
            const res = await postJSON('/companies/from-ch', { company_number: companyNumber });
            toast(res.message || 'Company added');
            btn.textContent = 'Added';
        } catch (e) {
            console.error(e);
            toast('Failed to add company: ' + e.message, true);
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });

    // Add client (director) — new endpoint
    document.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.btn-add-client');
        if (!btn) return;

        const payload = {
            name: btn.dataset.clientName,
            company_number: btn.dataset.companyNumber || null,
            company_name: btn.dataset.companyName || null
        };

        if (!payload.name) {
            toast('Could not detect the director name.', true);
            return;
        }

        btn.disabled = true;
        const original = btn.textContent;
        btn.textContent = 'Adding...';

        try {
            const res = await postJSON('/clients/from-ch', payload);
            toast(res.message || 'Client added');
            btn.textContent = 'Added';
        } catch (e) {
            console.error(e);
            toast('Failed to add client: ' + e.message, true);
            btn.textContent = original;
            btn.disabled = false;
        }
    });
})();
