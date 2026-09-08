<nav class="navbar">
    <a href="{{ route('home') }}" class="navbar-brand">
        <svg width="26" height="26" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 2C14 2 8 8 8 14C8 17.3 10.7 20 14 20C17.3 20 20 17.3 20 14C20 8 14 2 14 2Z" fill="currentColor" opacity="0.9"/>
            <path d="M14 20V26M11 26H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
        <span>SIG RTH Bekasi</span>
    </a>

    {{-- Hamburger (mobile only) --}}
    <button id="navToggle" aria-label="Toggle navigation" aria-expanded="false" class="navbar-toggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    {{-- Drawer --}}
    <div class="navbar-drawer" id="navDrawer">
        {{-- Drawer Header --}}
        <div class="navbar-drawer-header">
            <div class="navbar-drawer-brand">
                <svg width="22" height="22" viewBox="0 0 28 28" fill="none">
                    <path d="M14 2C14 2 8 8 8 14C8 17.3 10.7 20 14 20C17.3 20 20 17.3 20 14C20 8 14 2 14 2Z" fill="currentColor" opacity="0.9"/>
                    <path d="M14 20V26M11 26H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <span>SIG RTH Bekasi</span>
            </div>
            <span class="navbar-drawer-label">Menu</span>
        </div>

        {{-- Nav Links --}}
        <ul class="navbar-nav" id="navMenu">
            <li>
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Beranda
                </a>
            </li>
            <li>
                <a href="{{ route('peta') }}" class="{{ request()->routeIs('peta') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                    Peta
                </a>
            </li>
            <li>
                <a href="{{ route('data') }}" class="{{ request()->routeIs('data') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Data
                </a>
            </li>
        </ul>

        {{-- Admin Button (pinned to bottom of drawer) --}}
        <div class="navbar-drawer-footer">
            @auth
                <a href="{{ route('admin.rth.index') }}" class="btn-admin {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Admin Panel
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-admin">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Masuk sebagai Admin
                </a>
            @endauth
        </div>
    </div>

    {{-- Desktop Nav (hidden on mobile) --}}
    <ul class="navbar-nav navbar-nav-desktop">
        <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a></li>
        <li><a href="{{ route('peta') }}" class="{{ request()->routeIs('peta') ? 'active' : '' }}">Peta</a></li>
        <li><a href="{{ route('data') }}" class="{{ request()->routeIs('data') ? 'active' : '' }}">Data</a></li>
        @auth
            <li><a href="{{ route('admin.rth.index') }}" class="btn-admin {{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a></li>
        @else
            <li><a href="{{ route('login') }}" class="btn-admin">Admin</a></li>
        @endauth
    </ul>

    <div id="navOverlay" class="navbar-overlay"></div>
</nav>

<script>
(function () {
    const toggle  = document.getElementById('navToggle');
    const drawer  = document.getElementById('navDrawer');
    const overlay = document.getElementById('navOverlay');

    function openMenu() {
        toggle.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        drawer.classList.add('is-open');
        overlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        toggle.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-visible');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => drawer.classList.contains('is-open') ? closeMenu() : openMenu());
    overlay.addEventListener('click', closeMenu);
    drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
    document.addEventListener('keydown', e => e.key === 'Escape' && closeMenu());
    window.addEventListener('resize', () => window.innerWidth >= 768 && closeMenu());
})();
</script>