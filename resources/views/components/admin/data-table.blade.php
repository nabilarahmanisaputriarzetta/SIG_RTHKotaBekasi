@props([
    'id' => 'data-table',
    'title' => null,
])

<div class="table-card">

    @if($title)
        <h3>{{ $title }}</h3>
    @endif

    {{ $toolbar ?? '' }}

    <div class="table-wrap">
        <table id="{{ $id }}" class="data-table">
            {{ $slot }}
        </table>
    </div>

    {{ $footer ?? '' }}

</div>