@props([
    'data'
])

@if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())

<div class="pagination">

    <span class="pagination__info">
        Menampilkan
        <strong>{{ $data->firstItem() }}</strong>
        -
        <strong>{{ $data->lastItem() }}</strong>
        dari
        <strong>{{ $data->total() }}</strong>
        entri
    </span>

    <div class="pagination__pages">

        {{-- Prev --}}
        @if($data->onFirstPage())
            <span class="page-btn page-btn--disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </span>
        @else
            <a class="page-btn" href="{{ $data->previousPageUrl() }}" aria-label="Halaman sebelumnya">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </a>
        @endif

        {{-- Page numbers with ellipsis --}}
        @php
            $current  = $data->currentPage();
            $last     = $data->lastPage();
            $window   = 2;
            $pages    = [];
            for ($i = 1; $i <= $last; $i++) {
                if ($i === 1 || $i === $last || abs($i - $current) <= $window) {
                    $pages[] = $i;
                }
            }
            $prev = null;
        @endphp

        @foreach($pages as $page)
            @if($prev !== null && $page - $prev > 1)
                <span class="page-btn page-btn--ellipsis" aria-hidden="true">…</span>
            @endif
            <a
                class="page-btn {{ $page == $current ? 'page-btn--active' : '' }}"
                href="{{ $data->url($page) }}"
                aria-label="Halaman {{ $page }}"
                @if($page == $current) aria-current="page" @endif>
                {{ $page }}
            </a>
            @php $prev = $page; @endphp
        @endforeach

        {{-- Next --}}
        @if($data->hasMorePages())
            <a class="page-btn" href="{{ $data->nextPageUrl() }}" aria-label="Halaman berikutnya">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </a>
        @else
            <span class="page-btn page-btn--disabled" aria-disabled="true" aria-label="Halaman berikutnya">
                <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </span>
        @endif

    </div>

</div>

@once
<style>
.pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 0.5px solid var(--border, #e5e5e5);
}

.pagination__info {
    font-size: 13px;
    color: var(--text-muted, #aaa);
}

.pagination__info strong {
    color: var(--text-secondary, #555);
    font-weight: 500;
}

.pagination__pages {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 6px;
    border-radius: var(--radius, 8px);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary, #555);
    border: 0.5px solid transparent;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.1s;
    cursor: pointer;
}

.page-btn:hover:not(.page-btn--active):not(.page-btn--disabled):not(.page-btn--ellipsis) {
    background: var(--surface-1, #f5f5f5);
    border-color: var(--border, #e5e5e5);
    color: var(--text-primary, #111);
}

.page-btn:active:not(.page-btn--active):not(.page-btn--disabled) {
    transform: scale(0.95);
}

.page-btn--active {
    background: var(--fill-accent, #3b82f6);
    color: var(--on-accent, #fff);
    border-color: transparent;
    cursor: default;
}

.page-btn--disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

.page-btn--ellipsis {
    cursor: default;
    color: var(--text-muted, #aaa);
    pointer-events: none;
}

/* ── Mobile: stack info + pages, bigger tap targets ──────── */
@media (max-width: 640px) {
    .pagination {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .pagination__info {
        order: 2;
    }
    .pagination__pages {
        order: 1;
        width: 100%;
    }
}

@media (max-width: 400px) {
    .page-btn {
        min-width: 36px;
        height: 36px; /* easier to tap than the default 32px on small phones */
    }
}
</style>
@endonce

@endif