@extends('layouts.app')

@section('title', 'Companies')

@section('head')
    <style>
        /* minimal modal styling */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            display: none;
            align-items: center; justify-content: center;
            z-index: 1000;
        }
        .modal-backdrop.show { display: flex; }
        .modal-card {
            background: #fff; color: #222;
            width: min(800px, 92vw);
            max-height: 86vh; overflow: auto;
            border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,.25);
        }
        .modal-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; border-bottom: 1px solid #eee;
        }
        .modal-body { padding: 16px; }
        .btn-close {
            border: 0; background: transparent; font-size: 20px; line-height: 1;
            cursor: pointer;
        }

        /* list styling */
        ul.company-list { list-style: none; padding: 0; margin: 0; }
        ul.company-list li { padding: 8px 0; border-bottom: 1px solid #eee; }
        a.company-name-link { text-decoration: none; font-weight: 600; }
        a.company-name-link:hover { text-decoration: underline; }
        small.muted { color: #6b7280; }
    </style>
@endsection

@section('content')
    <h1>Companies</h1>

    @if(empty($companies) || !count($companies))
        <p>No companies yet.</p>
    @else
        <ul class="company-list">
            @foreach($companies as $co)
                @php
                    $num = $co->company_number ?? $co->number ?? '';
                    $name = $co->name ?? 'Company';
                @endphp
                <li>
                    {{-- MAIN LINK → full page --}}
                    <a href="{{ route('practice.companies.show', ['practice' => $practice->slug, 'companyParam' => rawurlencode($name)]) }}"
                       class="company-name-link">
                        {{ $name }}
                    </a>

                    {{-- Small "card" link that still opens the modal (optional) --}}
                    @if($num)
                        <small class="muted">
                            — {{ $num }}
                            &nbsp; <a href="#"
                                      class="js-company-card"
                                      data-number="{{ $num }}"
                                      title="Open quick card">[card]</a>
                        </small>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Modal --}}
    <div id="company-modal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="company-modal-title">
            <div class="modal-head">
                <strong id="company-modal-title">Company card</strong>
                <button type="button" class="btn-close" id="company-modal-close" aria-label="Close">×</button>
            </div>
            <div class="modal-body" id="company-modal-body">
                Loading…
            </div>
        </div>
    </div>

    <script>
        (function () {
            // Base URL for the card endpoint. We’ll append /{companyNumber} in JS.
            const cardBase = "{{ url('/p/'.$practice->slug.'/company-card') }}";

            const modal     = document.getElementById('company-modal');
            const modalBody = document.getElementById('company-modal-body');
            const btnClose  = document.getElementById('company-modal-close');

            function openModal(html) {
                modalBody.innerHTML = html || 'No details.';
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
            }
            function closeModal() {
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                modalBody.innerHTML = '';
            }
            btnClose.addEventListener('click', closeModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

            // Intercept clicks ONLY on the small [card] links
            document.addEventListener('click', async (ev) => {
                const a = ev.target.closest('a.js-company-card');
                if (!a) return;

                ev.preventDefault();

                const num = a.dataset.number;
                if (!num) return;

                try {
                    const res = await fetch(`${cardBase}/${encodeURIComponent(num)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    openModal(html);
                } catch (err) {
                    console.error('Company card fetch failed', err);
                    openModal('<p>Sorry—could not load the company card.</p>');
                }
            });
        })();
    </script>
@endsection
