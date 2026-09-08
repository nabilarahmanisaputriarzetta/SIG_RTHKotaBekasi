<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - SIG RTH Publik Kota Bekasi')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .tab-nav{
            display:flex;
            gap:8px;
            margin-bottom:20px;
        }
    
        .tab-nav__item{
            display:flex;
            align-items:center;
            gap:8px;
            padding:10px 14px;
            border:1px solid #e5e7eb;
            border-radius:8px;
            text-decoration:none;
            color:#374151;
            background:#fff;
        }
    
        .tab-nav__item svg{
            width:16px;
            height:16px;
        }
    
        .stat-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:16px;
            margin-bottom:20px;
        }
    
        .stat-card{
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:16px;
        }
    
        .stat-card__header{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:10px;
        }
    
        .stat-card__icon{
            width:32px;
            height:32px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#ecfdf5;
            border-radius:8px;
            flex-shrink:0;
        }
    
        .stat-card__icon svg{
            width:17px;
            height:17px;
        }
    
        .stat-card__value{
            font-size:1.5rem;
            font-weight:700;
        }
    
        .stat-card__sub{
            font-size:.8rem;
            color:#6b7280;
        }

        /* FILTER BAR */
        .filter-bar{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin-bottom:20px;
        }

        .filter-bar__search{
            position:relative;
            flex:1;
            min-width:240px;
        }

        .filter-bar__search input{
            width:100%;
            height:40px;
            padding:0 12px 0 40px;
            border:1px solid #d1d5db;
            border-radius:8px;
        }

        .filter-bar__search svg{
            position:absolute;
            left:12px;
            top:50%;
            transform:translateY(-50%);
            width:16px !important;
            height:16px !important;
            pointer-events:none;
        }

        .select-wrap{
            position:relative;
        }

        .select-input{
            appearance:none;
            -webkit-appearance:none;
            -moz-appearance:none;

            background:#fff;

            height:40px;
            min-width:220px;

            padding:0 40px 0 12px;

            border:1px solid #d1d5db;
            border-radius:8px;

            font-size:14px;
            color:#111827;

            cursor:pointer;
        }

        .select-input:focus{
            outline:none;
            border-color:#22c55e;
        }

        .select-wrap svg{
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            width:16px !important;
            height:16px !important;
            pointer-events:none;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --green-900:#1a3a2a; --green-800:#1e4d33; --green-700:#256840;
            --green-600:#2d7a4e; --green-500:#3a9c64; --green-400:#4db87a;
            --green-100:#e8f5ee; --green-50:#f2faf5;
            --text-dark:#111827; --text-mid:#374151; --text-light:#6b7280;
            --border:#e5e7eb; --white:#ffffff; --red:#dc2626; --orange:#d97706;
            --sidebar-w: 240px;
        }
        html { -webkit-text-size-adjust: 100%; }
        body { font-family:'Plus Jakarta Sans',sans-serif; background:#f8fafc; color:var(--text-dark); display:flex; min-height:100vh; overflow-x:hidden; }

        /* ── Sidebar toggle (hamburger) & overlay - tablet/mobile saja ── */
        .sidebar-toggle {
            display: none; align-items: center; justify-content: center;
            width: 38px; height: 38px; flex-shrink: 0;
            background: transparent; border: 1px solid var(--border); border-radius: 8px;
            color: var(--text-mid); cursor: pointer; margin-right: 0.5rem;
        }
        .sidebar-toggle:hover { background: var(--green-50); border-color: var(--green-500); color: var(--green-700); }
        .sidebar-toggle svg { width: 18px; height: 18px; }

        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(17,24,39,0.45); z-index: 90;
            opacity: 0; transition: opacity 0.2s ease;
        }
        .sidebar-overlay.is-open { display: block; opacity: 1; }

        /* ── Sidebar ── */
        .admin-sidebar {
            width: var(--sidebar-w); background: var(--green-900); color: white;
            display: flex; flex-direction: column; flex-shrink: 0;
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
            transition: transform 0.25s ease;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1);
            font-weight: 700; font-size: 0.95rem; color: white; text-decoration: none;
        }
        .sidebar-brand svg { color: var(--green-400); }
        .sidebar-nav { padding: 0.75rem 0; flex: 1; }
        .nav-section-label {
            padding: 0.75rem 1.5rem 0.3rem;
            font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; color: rgba(255,255,255,0.35);
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 0.6rem 1.5rem; color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 0.858rem; font-weight: 500;
            transition: all 0.15s; border-left: 3px solid transparent;
        }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.1); color: white; font-weight: 600; border-left-color: var(--green-400); }
        .nav-divider { border: none; border-top: 1px solid rgba(255,255,255,0.08); margin: 0.5rem 0; }

        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-user { font-size: 0.78rem; color: rgba(255,255,255,0.5); margin-bottom: 0.75rem; }
        .sidebar-user strong { display: block; color: rgba(255,255,255,0.9); font-size: 0.875rem; margin-bottom: 0.15rem; }
        .btn-logout {
            display: flex; align-items: center; gap: 6px; width: 100%;
            padding: 0.5rem 0.75rem; border-radius: 6px;
            background: rgba(220,38,38,0.12); color: #c03434;
            border: 1px solid rgba(220,38,38,0.2);
            font-family: inherit; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; transition: all 0.15s;
        }
        .btn-logout:hover { background: rgba(220,38,38,0.22); }

        /* ── Main ── */
        .admin-main { margin-left: var(--sidebar-w); flex: 1; min-width: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .admin-topbar {
            background: white; border-bottom: 1px solid var(--border);
            padding: 0.9rem 2rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50; gap: 0.75rem;
        }
        .admin-topbar__left { display: flex; align-items: center; min-width: 0; }
        .admin-topbar h1 { font-size: 1.05rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .admin-topbar__date { font-size:0.78rem; color:var(--text-light); flex-shrink: 0; }
        .admin-content { padding: 1.75rem 2rem; flex: 1; min-width: 0; overflow-x: hidden; }

        /* ── Tabs ── */
        .admin-tabs {
            display:flex; gap:0; border:1.5px solid var(--border); border-radius:10px;
            overflow-x:auto; -webkit-overflow-scrolling:touch;
            width:fit-content; max-width:100%; margin-bottom:1.75rem;
        }
        .tab-btn { display:flex; align-items:center; gap:6px; padding:0.6rem 1.25rem; border:none; background:white; font-family:inherit; font-size:0.85rem; font-weight:600; color:var(--text-light); cursor:pointer; transition:all 0.15s; white-space:nowrap; flex-shrink:0; }
        .tab-btn:not(:last-child) { border-right:1.5px solid var(--border); }
        .tab-btn.active { background:var(--green-700); color:white; }

        /* ── Stats grid ── */
        .admin-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.75rem; }
        .astat { background:white; border:1px solid var(--border); border-radius:12px; padding:1.1rem 1.25rem; }
        .astat .as-label { font-size:0.73rem; color:var(--text-light); display:flex; align-items:center; gap:5px; margin-bottom:0.4rem; }
        .astat .as-val { font-size:1.55rem; font-weight:800; color:var(--text-dark); line-height:1.05; }
        .astat .as-sub { font-size:0.7rem; color:var(--text-light); margin-top:0.2rem; }

        /* ── Table card ── */
        .table-card{ background:#fff; border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.06); padding:20px; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .table-toolbar { display:flex; align-items:center; gap:0.75rem; padding:1rem 1.25rem; border-bottom:1px solid var(--border); flex-wrap:wrap; }
        .search-input { flex:1; min-width:180px; padding:0.5rem 0.85rem 0.5rem 2.2rem; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:0.85rem; outline:none; background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='%236b7280'%3E%3Ccircle cx='11' cy='11' r='6' stroke='%236b7280' stroke-width='2' fill='none'/%3E%3Cline x1='16.5' y1='16.5' x2='21' y2='21' stroke='%236b7280' stroke-width='2'/%3E%3C/svg%3E") no-repeat 0.65rem center; }
        .search-input:focus { border-color:var(--green-500); }
        .filter-select { padding:0.5rem 0.75rem; border:1.5px solid var(--border); border-radius:8px; font-family:inherit; font-size:0.82rem; outline:none; cursor:pointer; }
        .ml-auto { margin-left:auto; }

        table { width:100%; min-width:640px; border-collapse:collapse; font-size:0.855rem; }
        table th { text-align:left; padding:0.65rem 1rem; font-size:0.73rem; font-weight:600; color:var(--text-light); border-bottom:1px solid var(--border); background:#fafafa; white-space:nowrap; }
        table td { padding:0.8rem 1rem; border-bottom:1px solid var(--border); color:var(--text-mid); }
        table tr:last-child td { border-bottom:none; }
        table tr:hover td { background:var(--green-50); }

        /* ── Action btns ── */
        .act-btn { background:none; border:none; cursor:pointer; padding:4px; border-radius:6px; transition:background 0.15s; line-height:1; }
        .act-btn:hover { background:var(--green-50); }
        .act-btn.del:hover { background:#fee2e2; }

        /* ── Badges ── */
        .badge { display:inline-flex; align-items:center; padding:0.2rem 0.6rem; border-radius:20px; font-size:0.71rem; font-weight:600; }
        .badge-kritis   { background:#fee2e2; color:#dc2626; }
        .badge-kurang   { background:#fef3c7; color:#d97706; }
        .badge-cukup    { background:#fef9c3; color:#ca8a04; }
        .badge-memenuhi { background:#dcfce7; color:#16a34a; }
        .badge-sangat   { background:#d1fae5; color:#059669; }

        /* ── Buttons ── */
        .btn { display:inline-flex; align-items:center; gap:6px; padding:0.55rem 1.2rem; border-radius:8px; border:none; font-family:inherit; font-size:0.82rem; font-weight:600; cursor:pointer; transition:all 0.2s; text-decoration:none; }
        .btn-primary { background:var(--green-700); color:white; }
        .btn-primary:hover { background:var(--green-800); }
        .btn-outline { background:transparent; color:var(--text-mid); border:1.5px solid var(--border); }
        .btn-outline:hover { border-color:var(--green-500); color:var(--green-700); }
        .btn-sm { padding:0.35rem 0.75rem; font-size:0.78rem; }
        .btn-link { background:none; color:var(--red); border:none; font-size:0.8rem; font-weight:600; cursor:pointer; padding:0; font-family:inherit; }

        /* ── Toast ── */
        #toast { position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999; display:flex; align-items:center; gap:0.75rem; background:var(--green-800); color:white; padding:0.85rem 1.25rem; border-radius:10px; font-size:0.875rem; transform:translateY(100px); opacity:0; transition:all 0.3s; pointer-events:none; min-width:200px; }
        #toast.show { transform:translateY(0); opacity:1; }
        #toast.error { background:var(--red); }

        /* ── Responsive ── */

        /* Tablet ke bawah: sidebar jadi drawer, konten mepet ke kiri */
        @media (max-width: 1024px) {
            .sidebar-toggle { display: flex; }

            .admin-sidebar { transform: translateX(-100%); box-shadow: 12px 0 32px rgba(0,0,0,0.18); }
            .admin-sidebar.is-open { transform: translateX(0); }

            .admin-main { margin-left: 0; }

            .admin-stats { grid-template-columns: repeat(2,1fr); }
        }

        /* Tablet kecil / mobile besar */
        @media (max-width: 768px) {
            .admin-topbar { padding: 0.75rem 1.25rem; }
            .admin-content { padding: 1.25rem 1rem; }
            .admin-topbar__date { display: none; }

            .form-row { grid-template-columns: 1fr; }
        }

        /* Mobile */
        @media (max-width: 520px) {
            .admin-stats { grid-template-columns: 1fr; }
            .admin-tabs { width: 100%; }
            .table-toolbar { padding: 0.85rem 1rem; }
            .table-card { padding: 14px; border-radius: 12px; }
            #toast { left: 1rem; right: 1rem; bottom: 1rem; min-width: 0; }
        }
    </style>
    
    @yield('extra-styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar">
        <a href="{{ route('home') }}" class="sidebar-brand">
            <svg width="22" height="22" viewBox="0 0 28 28" fill="currentColor">
                <path d="M14 2C14 2 8 8 8 14C8 17.3 10.7 20 14 20C17.3 20 20 17.3 20 14C20 8 14 2 14 2Z"/>
                <path d="M14 20V26M11 26H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none"/>
            </svg>
            Admin RTH
        </a>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Manajemen</div>
            <a href="{{ route('admin.rth.index') }}"
               class="nav-item {{ request()->routeIs('admin.rth.*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C12 2 6 8 6 14C6 17.3 8.7 20 12 20C15.3 20 18 17.3 18 14C18 8 12 2 12 2Z"/><path d="M12 20V23" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                Data RTH
            </a>
            <a href="{{ route('admin.kepadatan.index') }}"
               class="nav-item {{ request()->routeIs('admin.kepadatan.*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05C17.03 14.02 18 15.21 18 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                Kepadatan Penduduk
            </a>

            <hr class="nav-divider">
            <div class="nav-section-label">Lainnya</div>
            <a href="{{ route('home') }}" class="nav-item" target="_blank">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6v2H5v11h11v-5h2v7H3V6h7zm11-3v8h-2V6.413l-7.793 7.794-1.414-1.414L17.585 5H13V3z"/></svg>
                Lihat Website
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                {{ auth()->user()->email ?? '' }}
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <div class="admin-topbar__left">
                <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Buka menu" aria-controls="adminSidebar" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1>@yield('page-title', 'Dashboard Admin')</h1>
            </div>
            <span class="admin-topbar__date">{{ now()->isoFormat('dddd, D MMMM Y') }}</span>
        </div>
        <div class="admin-content">
            @yield('admin-content')
        </div>
    </div>

    <div id="toast"></div>

    <script>
        (function () {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarToggle');

            function openSidebar() {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-open');
                toggleBtn.setAttribute('aria-expanded', 'true');
            }
            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
                toggleBtn.setAttribute('aria-expanded', 'false');
            }

            toggleBtn?.addEventListener('click', () => {
                sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
            });
            overlay?.addEventListener('click', closeSidebar);

            // Tutup drawer otomatis saat memilih menu (mobile) atau resize ke desktop
            sidebar?.addEventListener('click', (e) => {
                if (e.target.closest('.nav-item')) closeSidebar();
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth > 1024) closeSidebar();
            });
        })();

        function showToast(msg, isError = false) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = isError ? 'error show' : 'show';
            setTimeout(() => { t.className = ''; }, 3000);
        }
        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }
    </script>
    @yield('scripts')
</body>
</html>