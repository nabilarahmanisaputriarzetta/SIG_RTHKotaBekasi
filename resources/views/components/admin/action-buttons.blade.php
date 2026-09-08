@props([
    'editModal'   => 'modal-edit',
    'editFields'  => [],
    'deleteModal' => 'modal-delete',
    'deleteUrl'   => '#',
    'deleteLabel' => '',
])

<div class="action-cell__group">

    {{-- Edit --}}
    <button
        type="button"
        class="btn btn--icon btn-edit"
        data-tooltip="Edit"
        aria-label="Edit {{ $deleteLabel }}"
        onclick="openEditModal('{{ $editModal }}', {{ json_encode($editFields) }})">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
        </svg>
    </button>

    {{-- Delete --}}
    <button
        type="button"
        class="btn btn--icon btn-delete"
        data-tooltip="Hapus"
        aria-label="Hapus {{ $deleteLabel }}"
        onclick="confirmDelete('{{ $deleteModal }}','{{ $deleteUrl }}','{{ addslashes($deleteLabel) }}')">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
        </svg>
    </button>

</div>

@once
<style>
.action-cell__group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
}

.btn-icon {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: var(--r-sm, 9px);
    border: 1px solid var(--border, #e5e5e5);
    background: var(--surface, #fff);
    cursor: pointer;
    color: var(--text-secondary, #555);
    box-shadow: 0 1px 2px rgba(0,0,0,.04);

    /* HAPUS ANIMASI */
    transition: none;
    transform: none;
}

.btn-icon svg {
    transition: none;
    transform: none;
}


/* Hover icon tanpa gerakan */
.btn-icon:hover svg {
    transform: none;
}


/* Tidak ada efek klik */
.btn-icon:active {
    transform: none;
    box-shadow: none;
}


.btn-icon:focus-visible {
    outline: none;
    transform: none;
}


/* =====================
   EDIT BUTTON
===================== */

.btn-edit:hover,
.btn-edit:focus-visible {
    background: #eff6ff;
    color: #2563eb;
    border-color: #93c5fd;
    box-shadow: none;
    transform: none;
}


.btn-edit:hover svg {
    transform: none;
}



/* =====================
   DELETE BUTTON
===================== */

.btn-delete:hover,
.btn-delete:focus-visible {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fca5a5;
    box-shadow: none;
    transform: none;
}


.btn-delete:hover svg {
    animation: none;
    transform: none;
}


/* HAPUS DELETE SHAKE */
@keyframes btnDeleteShake {
    from,
    to {
        transform: none;
    }
}



/* =====================
   TOOLTIP TANPA ANIMASI
===================== */

.btn-icon[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 7px);
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .01em;
    padding: 4px 8px;
    border-radius: 6px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: none;
    z-index: 20;
}


.btn-icon[data-tooltip]::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 3px);
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: #1f2937;
    opacity: 0;
    pointer-events: none;
    transition: none;
    z-index: 20;
}


.btn-icon[data-tooltip]:hover::after,
.btn-icon[data-tooltip]:focus-visible::after {
    opacity: 1;
    transform: translateX(-50%);
}


.btn-icon[data-tooltip]:hover::before,
.btn-icon[data-tooltip]:focus-visible::before {
    opacity: 1;
}


@media (prefers-reduced-motion: reduce) {
    .btn-icon,
    .btn-icon svg {
        transition: none !important;
        animation: none !important;
    }
}
</style>
@endonce