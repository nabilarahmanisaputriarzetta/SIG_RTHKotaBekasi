@props(['status' => ''])

@php
$map = [
    'Kritis'        => 'badge--kritis',
    'Kurang'        => 'badge--kurang',
    'Cukup'         => 'badge--cukup',
    'Memenuhi'      => 'badge--memenuhi',
    'Belum memenuhi' => 'badge--kritis',
    'Kurang Padat'  => 'badge--kurang',
    'Rendah'        => 'badge--memenuhi',
    'Sedang'        => 'badge--cukup',
    'Padat'         => 'badge--kurang',
    'Tinggi'        => 'badge--kurang',
    'Sangat Padat'  => 'badge--kritis',
];
$class = $map[$status] ?? 'badge--cukup';
@endphp

<span class="badge {{ $class }}">{{ $status }}</span>

@once
<style>
.badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
    border: 0.5px solid transparent;
}

.badge--kritis {
    background: var(--bg-danger, #fef2f2);
    color: var(--text-danger, #dc2626);
    border-color: var(--border-danger, #fca5a5);
}

.badge--kurang {
    background: var(--bg-warning, #fff7ed);
    color: var(--text-warning, #c2410c);
    border-color: var(--border-warning, #fdba74);
}

.badge--cukup {
    background: var(--bg-accent, #eff6ff);
    color: var(--text-accent, #2563eb);
    border-color: var(--border-accent, #93c5fd);
}

.badge--memenuhi {
    background: var(--bg-success, #f0fdf4);
    color: var(--text-success, #16a34a);
    border-color: var(--border-success, #86efac);
}
</style>
@endonce