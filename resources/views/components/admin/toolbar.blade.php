@props([
    'addLabel' => 'Tambah Data',
    'addModal' => null,
    'addRoute' => null,
])

<div class="toolbar">
    @if($addRoute)
        <a href="{{ $addRoute }}" class="toolbar-btn">
            <span class="toolbar-btn__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>{{ $addLabel }}</span>
        </a>
    @else
        <button
            type="button"
            class="toolbar-btn"
            data-modal-open="{{ $addModal }}">
            <span class="toolbar-btn__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span>{{ $addLabel }}</span>
        </button>
    @endif
</div>

@once
<style>
.toolbar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 16px;
}

.toolbar-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #2f6b3f;
    color: #fff;
    border: none;
    border-radius: var(--radius, 8px);
    padding: 0 18px;
    height: 38px;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .01em;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(47, 107, 63, .2), 0 3px 8px rgba(47, 107, 63, .12);
    white-space: nowrap;
}

.toolbar-btn__icon {
    display: inline-flex;
    flex-shrink: 0;
}

.toolbar-btn:hover {
    background: #255532;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(47, 107, 63, 0.3);
}

.toolbar-btn:hover .toolbar-btn__icon {
    transform: rotate(90deg);
}

.toolbar-btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(47, 107, 63, .25), 0 4px 12px rgba(47, 107, 63, 0.3);
}

.toolbar-btn:active {
    transform: translateY(0) scale(0.98);
    box-shadow: 0 1px 2px rgba(47, 107, 63, .2);
}

.toolbar-btn svg {
    flex-shrink: 0;
}

/* ── Tablet: keep right-aligned but allow the row above it to wrap first ── */
@media (max-width: 768px) {
    .toolbar { margin-bottom: 14px; }
}

/* ── Mobile: full-width button, easier to tap ──────────── */
@media (max-width: 576px) {
    .toolbar {
        justify-content: stretch;
    }
    .toolbar-btn {
        width: 100%;
        justify-content: center;
        height: 44px; /* taller tap target on touch screens */
        font-size: 13.5px;
    }
}
</style>
@endonce