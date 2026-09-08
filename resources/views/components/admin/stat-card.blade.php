@props([
    'icon'  => 'database',
    'label' => '',
    'value' => '',
    'sub'   => ''
])

@php
$icons = [
    'database' => '<path d="M20 13H4v-2h16v2zm0-4H4V7h16v2zm0 8H4v-2h16v2z"/>',
    'tree'     => '<path d="M12 2C12 2 6 8 6 14C6 17.3 8.7 20 12 20C15.3 20 18 17.3 18 14C18 8 12 2 12 2Z"/>',
    'growth'   => '<path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>',
    'calendar' => '<path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/>',
    'users'    => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
];

$accents = [
    'database' => ['bg' => 'var(--bg-pro, #eef2ff)',     'fg' => 'var(--text-pro, #4f46e5)'],
    'tree'     => ['bg' => 'var(--bg-success, #ecfdf5)', 'fg' => 'var(--text-success, #15803d)'],
    'growth'   => ['bg' => 'var(--bg-warning, #fff7ed)', 'fg' => 'var(--text-warning, #c2410c)'],
    'calendar' => ['bg' => '#fdf4ff',                    'fg' => '#a21caf'],
    'users'    => ['bg' => 'var(--bg-accent, #eff6ff)',  'fg' => 'var(--text-accent, #2563eb)'],
];
$accent = $accents[$icon] ?? $accents['database'];
@endphp

@once
<style>
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
    align-items: stretch;
}

.stat-card {
    background: var(--surface-2, #fff);
    border: 0.5px solid var(--border, #e5e5e5);
    border-radius: 12px;
    padding: 18px 20px;
    transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    cursor: default;
}

.stat-card:hover {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.07);
    transform: translateY(-2px);
    border-color: var(--border-strong, #ccc);
}

.stat-card .as-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted, #aaa);
    margin: 0 0 12px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.stat-card .as-icon {
    flex: 0 0 auto;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card .as-val {
    font-size: 1.65rem;
    font-weight: 500;
    color: var(--text-primary, #111);
    line-height: 1.2;
    word-break: break-word;
}

.stat-card .as-sub {
    font-size: 12px;
    color: var(--text-muted, #aaa);
    margin-top: 6px;
}

@media (max-width: 640px) {
    .admin-stats {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }
    .stat-card { padding: 14px 16px; }
    .stat-card .as-val { font-size: 1.4rem; }
}

@media (max-width: 400px) {
    .admin-stats { grid-template-columns: 1fr; }
}
</style>
@endonce

<div class="stat-card">
    <div class="as-label">
        <span class="as-icon" style="background: {{ $accent['bg'] }}; color: {{ $accent['fg'] }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                {!! $icons[$icon] ?? '' !!}
            </svg>
        </span>
        <span>{{ $label }}</span>
    </div>

    <div class="as-val">{{ $value }}</div>

    @if($sub)
        <div class="as-sub">{{ $sub }}</div>
    @endif
</div>