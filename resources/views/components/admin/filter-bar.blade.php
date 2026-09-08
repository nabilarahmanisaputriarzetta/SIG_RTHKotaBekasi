@props([
    'searchId'          => 'search',
    'searchPlaceholder' => 'Cari...',
    'searchAction'      => '',
    'filters'           => [],
])

<div class="filter-bar">

    {{-- Search --}}
    <div class="filter-bar__search">
        <span class="filter-bar__search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15">
                <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </span>
        <input
            type="text"
            id="{{ $searchId }}"
            placeholder="{{ $searchPlaceholder }}"
            autocomplete="off"
            @if($searchAction)
                oninput="{{ $searchAction }}"
            @endif
        >
    </div>

    {{-- Dropdowns --}}
    @if(count($filters))
        <div class="filter-bar__selects">
            @foreach($filters as $filter)
                <div class="filter-bar__select-wrap">
                    <select
                        id="{{ $filter['id'] }}"
                        onchange="{{ $filter['action'] ?? '' }}">
                        @foreach($filter['options'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="filter-bar__select-arrow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </span>
                </div>
            @endforeach
        </div>
    @endif

</div>

@once
<style>
.filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.filter-bar__search {
    position: relative;
    display: inline-flex;
    align-items: center;
    flex: 1 1 240px;
    min-width: 0;
}

.filter-bar__search-icon {
    position: absolute;
    left: 10px;
    color: var(--text-muted, #aaa);
    pointer-events: none;
    display: flex;
    align-items: center;
}

.filter-bar__search input[type="text"] {
    height: 36px;
    padding: 0 12px 0 34px;
    border: 0.5px solid var(--border-strong, #ccc);
    border-radius: var(--radius, 8px);
    font-size: 13px;
    background: var(--surface-2, #fff);
    color: var(--text-primary, #111);
    outline: none;
    width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.filter-bar__search input[type="text"]::placeholder {
    color: var(--text-muted, #aaa);
}

.filter-bar__search input[type="text"]:focus {
    border-color: var(--border-accent, #3b82f6);
    box-shadow: 0 0 0 2px var(--bg-accent, #eff6ff);
}

.filter-bar__selects {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-bar__select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.filter-bar__select-wrap select {
    height: 36px;
    padding: 0 30px 0 10px;
    border: 0.5px solid var(--border-strong, #ccc);
    border-radius: var(--radius, 8px);
    font-size: 13px;
    background: var(--surface-2, #fff);
    color: var(--text-primary, #111);
    appearance: none;
    -webkit-appearance: none;
    outline: none;
    cursor: pointer;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.filter-bar__select-wrap select:focus {
    border-color: var(--border-accent, #3b82f6);
    box-shadow: 0 0 0 2px var(--bg-accent, #eff6ff);
}

.filter-bar__select-wrap select:hover {
    border-color: var(--border-stronger, #bbb);
}

.filter-bar__select-arrow {
    position: absolute;
    right: 9px;
    pointer-events: none;
    color: var(--text-muted, #aaa);
    display: flex;
    align-items: center;
}

@media (max-width: 640px) {
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-bar__search {
        flex-basis: auto;
        width: 100%;
    }
    .filter-bar__selects {
        width: 100%;
    }
    .filter-bar__select-wrap {
        flex: 1 1 auto;
        width: 100%;
    }
    .filter-bar__select-wrap select {
        width: 100%;
    }
}
</style>
@endonce