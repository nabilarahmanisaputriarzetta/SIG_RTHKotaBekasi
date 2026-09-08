<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Baseline responsive safety-net - admin.css loaded right after
         can still override any of this since it comes later in the cascade. --}}
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        html, body {
            max-width: 100%;
            overflow-x: hidden; /* stop any stray wide element from causing page-level horizontal scroll */
        }
        img, svg {
            max-width: 100%;
            height: auto;
        }
        .admin-wrap {
            width: 100%;
            padding: clamp(0.75rem, 2vw + 0.5rem, 2rem);
        }
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            flex-wrap: wrap;
            word-break: break-word;
        }
        .alert svg {
            flex-shrink: 0;
        }
    </style>

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])

    @stack('styles')
</head>
<body>
<div class="admin-wrap">

    @if(session('success'))
        <div class="alert alert--success">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert--error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</div>
@stack('scripts')
</body>
</html>