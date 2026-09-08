<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIG RTH Publik Kota Bekasi')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @yield('extra-styles')
    @yield('head')
</head>
<body class="font-sans text-ink bg-white leading-[1.6]">
    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.footer')

    <div id="toast"
         class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 bg-green-800 text-white px-5 py-[0.85rem] rounded-[10px] text-sm translate-y-[100px] opacity-0 transition-all duration-300 pointer-events-none"></div>

    <script>
        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.remove('bg-green-800', 'bg-[#dc2626]');
            t.classList.add(isError ? 'bg-[#dc2626]' : 'bg-green-800');
            t.classList.remove('translate-y-[100px]', 'opacity-0');
            t.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                t.classList.remove('translate-y-0', 'opacity-100');
                t.classList.add('translate-y-[100px]', 'opacity-0');
            }, 3000);
        }
        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
