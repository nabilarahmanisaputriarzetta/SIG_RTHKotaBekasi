{{--
    Usage: @include('components.badge', ['status' => $row['status']])
    Works for RTH status and Kepadatan status.
--}}
@php
    $map = [
        'Kritis'        => 'bg-[#fee2e2] text-[#dc2626]',
        'Kurang'        => 'bg-[#fef3c7] text-[#d97706]',
        'Cukup'         => 'bg-[#fef9c3] text-[#ca8a04]',
        'Memenuhi'      => 'bg-[#dcfce7] text-[#16a34a]',
        'Sangat Baik'   => 'bg-[#d1fae5] text-[#059669]',
        'Sangat Padat'  => 'bg-[#fee2e2] text-[#dc2626]',
        'Padat'         => 'bg-[#fef3c7] text-[#d97706]',
        'Sedang'        => 'bg-[#fef9c3] text-[#ca8a04]',
        'Kurang Padat'  => 'bg-[#dcfce7] text-[#16a34a]',
        'Rendah'        => 'bg-[#d1fae5] text-[#059669]',
    ];
    $cls = $map[$status] ?? 'bg-[#fef9c3] text-[#ca8a04]';
@endphp
<span class="inline-flex items-center px-[0.6rem] py-[0.2rem] rounded-full text-[0.72rem] font-semibold {{ $cls }}">{{ $status }}</span>
