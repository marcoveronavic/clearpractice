/* Minimal client to post a CH company_number and show a toast */
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
        const payload = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = (payload && payload.message) ? payload.message : res.statusText;
            throw new Error(msg);
        }
        return payload;
    }

    function toast(msg, isError = false) {
        // Simple toast; replace with your own
        const el = document.createElement('div');
        el.textContent = msg;
        el.style.position = 'fixed';
        el.style.right = '16px';
        el.style.bottom = '16px';
        el.style.padding = '10px 14px';
        el.style.borderRadius = '6px';
        el.style.background = isError ? '#b00020' : '#2e7d32';
        el.style.color = 'white';
        el.style.zIndex = 99999;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    document.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('#btn-add-company');
        if (!btn) return;

        const companyNumber = btn.dataset.companyNumber;
        if (!companyNumber) {
            toast('No company number on the modal button', true);
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Adding...';

        try {
            const res = await postJSON('/companies/from-ch', { company_number: companyNumber });
            toast(res.message || 'Company added');
            btn.textContent = 'Added';
        } catch (e) {
            console.error(e);
            toast('Failed to add company: ' + e.message, true);
            btn.textContent = 'Add to my companies';
            btn.disabled = false;
        }
    });
})();
