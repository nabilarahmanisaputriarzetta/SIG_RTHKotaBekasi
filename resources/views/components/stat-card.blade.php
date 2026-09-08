{{--
    Usage: @include('components.stat-card', [
        'icon'  => '<svg>...</svg>',   // optional
        'label' => 'Total Luas RTH',
        'value' => '3.28 km²',
        'sub'   => 'dari 231.82 km² total',
        'color' => 'green|red|orange',  // optional
    ])
--}}
@php
    $valueColor = ($color ?? '') === 'red'
        ? 'text-[#dc2626]'
        : (($color ?? '') === 'orange' ? 'text-[#d97706]' : 'text-ink');
@endphp
<div class="bg-white border border-line rounded-[12px] px-4 py-3 sm:px-5 sm:py-4 lg:px-6 lg:py-5">
    @if(!empty($icon))
        <div class="text-green-500 mb-1.5 sm:mb-2 [&>svg]:w-5 [&>svg]:h-5 sm:[&>svg]:w-6 sm:[&>svg]:h-6">{!! $icon !!}</div>
    @endif
    <div class="text-[0.7rem] sm:text-[0.8rem] text-ink-light mb-[0.3rem] sm:mb-[0.4rem]">{{ $label }}</div>
    <div class="text-[1.25rem] sm:text-[1.5rem] lg:text-[1.75rem] font-bold leading-[1.1] {{ $valueColor }} break-words">
        {!! $value !!}
    </div>
    @if(!empty($sub))
        <div class="text-[0.65rem] sm:text-[0.75rem] text-ink-light mt-[0.25rem] sm:mt-[0.3rem]">{{ $sub }}</div>
    @endif
</div>
