@props([
    'row'         => [],
    'editModal'   => 'modal-edit',
    'editFields'  => [],
    'deleteUrl'   => '#',
    'deleteLabel' => '',
])

<div class="table-actions">

    {{-- Edit Button --}}
    <button
        type="button"
        class="table-actions__btn table-actions__btn--edit"
        data-modal-open="{{ $editModal }}"
        data-edit-fields="{{ json_encode($editFields) }}"
        aria-label="Edit {{ $deleteLabel }}"
        title="Edit {{ $deleteLabel }}">
        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
        </svg>

        <span class="table-actions__label">Edit</span>
    </button>

    {{-- Delete Button --}}
    <button
        type="button"
        class="table-actions__btn table-actions__btn--delete"
        data-modal-open="modal-delete"
        data-delete-url="{{ $deleteUrl }}"
        data-delete-label="{{ $deleteLabel }}"
        aria-label="Hapus {{ $deleteLabel }}"
        title="Hapus {{ $deleteLabel }}">
        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true">
            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
        </svg>

        <span class="table-actions__label">Hapus</span>
    </button>

</div>

@once
<style>
.table-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
}

.table-actions__btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 12px;
    height: 30px;
    border-radius: var(--r-sm, 8px);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .01em;
    cursor: pointer;
    border: 1px solid var(--border, #e5e5e5);
    background: var(--surface, #fff);
    color: var(--text-secondary, #555);
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    transition: background .15s ease, color .15s ease, border-color .15s ease,
                transform .12s ease, box-shadow .15s ease;
    white-space: nowrap;
}

.table-actions__btn svg { flex-shrink: 0; transition: transform .18s ease; }

.table-actions__btn:hover svg { transform: scale(1.08); }

.table-actions__btn:active {
    transform: scale(0.96);
    box-shadow: none;
}

.table-actions__btn:focus-visible {
    outline: none;
}

.table-actions__btn--edit:hover,
.table-actions__btn--edit:focus-visible {
    background: var(--bg-accent, #eff6ff);
    color: var(--text-accent, #2563eb);
    border-color: var(--border-accent, #93c5fd);
    box-shadow: 0 2px 8px rgba(37, 99, 235, .15);
    transform: translateY(-1px);
}

.table-actions__btn--delete:hover,
.table-actions__btn--delete:focus-visible {
    background: var(--bg-danger, #fef2f2);
    color: var(--text-danger, #dc2626);
    border-color: var(--border-danger, #fca5a5);
    box-shadow: 0 2px 8px rgba(220, 38, 38, .15);
    transform: translateY(-1px);
}

.table-actions__btn--delete:active,
.table-actions__btn--edit:active { transform: scale(0.96); }

/* ── Tablet: tighten spacing a touch ── */
@media (max-width: 900px) {
    .table-actions__btn { padding: 0 10px; }
}

/* ── Mobile: collapse to compact icon-only pills so action
   columns never force horizontal scroll on small screens ── */
@media (max-width: 560px) {
    .table-actions { gap: 6px; }
    .table-actions__btn {
        width: 32px;
        height: 32px;
        padding: 0;
        justify-content: center;
        border-radius: var(--r-sm, 8px);
    }
    .table-actions__label {
        position: absolute;
        width: 1px; height: 1px;
        padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0,0,0,0);
        white-space: nowrap; border: 0;
    }
}

/* Larger, easier tap targets on touch devices */
@media (hover: none) and (pointer: coarse) {
    .table-actions__btn { min-height: 34px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // Populate delete modal label when delete button is clicked
    document.querySelectorAll('[data-delete-label]').forEach(btn => {
        btn.addEventListener('click', () => {
            const label = btn.dataset.deleteLabel;
            const url   = btn.dataset.deleteUrl;

            const labelEl = document.querySelector('[data-delete-label]:not(button)');
            if (labelEl) labelEl.textContent = label;

            const confirmBtn = document.querySelector('[data-delete-confirm]');
            if (confirmBtn && url) {
                confirmBtn.dataset.deleteUrl = url;
            }
        });
    });

    // Handle delete confirm — FALLBACK only. If a page defines its own
    // fetch()-based confirmDelete()/doDelete() and assigns it via
    // `btn.onclick`, skip this plain form-submit path to avoid sending
    // the delete request twice.
    document.querySelectorAll('[data-delete-confirm]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (typeof btn.onclick === 'function') return;

            const url = btn.dataset.deleteUrl;
            if (url && url !== '#') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const csrf = document.createElement('input');
                csrf.type  = 'hidden';
                csrf.name  = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                form.appendChild(csrf);

                const method = document.createElement('input');
                method.type  = 'hidden';
                method.name  = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Populate edit modal fields when edit button is clicked
    document.querySelectorAll('[data-edit-fields]').forEach(btn => {
        btn.addEventListener('click', () => {
            let fields = {};
            try { fields = JSON.parse(btn.dataset.editFields); } catch(e) {}

            Object.entries(fields).forEach(([name, value]) => {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.value = value;
            });
        });
    });

});
</script>
@endonce