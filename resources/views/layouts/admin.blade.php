<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - Rentalin</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('rentalin_theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    @include('partials.theme-engine')
    <style>
        #admin-spa-main {
            transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .page-leaving { opacity: 0; transform: translateY(8px); }
        .page-entering { opacity: 0; transform: translateY(-6px); }
        #admin-progress-bar { transition: width 0.3s ease, opacity 0.2s ease; }
    </style>
</head>
<body class="bg-brand-canvas text-brand-ink font-sans antialiased min-h-screen flex transition-colors duration-300">

    <!-- Progress Bar Atas SPA -->
    <div id="admin-progress-bar" class="fixed top-0 left-0 h-1 bg-brand-pink z-[70] w-0 opacity-0"></div>

    <!-- SIDEBAR ADMIN (DESKTOP) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-white border-r border-gray-100 fixed inset-y-0 left-0 z-30">
        <div class="h-20 px-6 flex items-center gap-3 border-b border-gray-100">
            <span class="w-9 h-9 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-base flex-shrink-0">
                <i class="bi bi-car-front-fill"></i>
            </span>
            <div>
                <span class="font-extrabold text-lg tracking-tight text-brand-ink block leading-none">Rentalin</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-pink">Admin Panel</span>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" data-admin-spa data-admin-nav="dashboard"
               class="admin-nav-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:bg-brand-canvas hover:text-brand-ink' }}">
                <i class="bi bi-grid-1x2-fill text-sm"></i> Ringkasan Dashboard
            </a>

            <a href="{{ route('admin.transactions') }}" data-admin-spa data-admin-nav="transactions"
               class="admin-nav-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.transactions', 'admin.scan') ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:bg-brand-canvas hover:text-brand-ink' }}">
                <i class="bi bi-qr-code-scan text-sm"></i> Transaksi &amp; Scan QR
            </a>

            <a href="{{ route('admin.vehicles.index') }}" data-admin-spa data-admin-nav="vehicles"
               class="admin-nav-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.vehicles.*') ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:bg-brand-canvas hover:text-brand-ink' }}">
                <i class="bi bi-car-front-fill text-sm"></i> Kelola Armada
            </a>

            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold text-gray-500 hover:bg-brand-canvas hover:text-brand-ink transition">
                <i class="bi bi-box-arrow-up-right text-sm"></i> Lihat Web Publik
            </a>
        </nav>

        <div class="p-4 border-t border-gray-100">
            @if(Route::has('logout'))
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-2xl bg-brand-canvas hover:bg-brand-pinkSoft hover:text-brand-pink text-xs font-extrabold text-gray-600 transition cursor-pointer">
                        <i class="bi bi-box-arrow-left"></i> Keluar Admin
                    </button>
                </form>
            @else
                <a href="{{ route('home') }}" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-2xl bg-brand-canvas hover:bg-brand-pinkSoft hover:text-brand-pink text-xs font-extrabold text-gray-600 transition">
                    <i class="bi bi-box-arrow-left"></i> Kembali ke Beranda
                </a>
            @endif
        </div>
    </aside>

    <!-- KONTEN UTAMA ADMIN -->
    <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
        <!-- NAVBAR ADMIN (MOBILE IDENTIK NAVBAR UTAMA TANPA CEK NOTA + DESKTOP TOPBAR) -->
        <header id="admin-topbar-header" class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-colors duration-300">
            <!-- TAMPILAN MOBILE (< lg) -->
            <div class="lg:hidden px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">
                <a href="{{ route('admin.dashboard') }}" data-admin-spa class="flex items-center gap-2.5 group">
                    <span class="w-9 h-9 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-base transition-transform duration-300 group-hover:scale-105">
                        <i class="bi bi-car-front-fill"></i>
                    </span>
                    <span class="font-extrabold text-lg sm:text-xl tracking-tight text-brand-ink">Rentalin</span>
                </a>

                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <button type="button" onclick="toggleThemeMode()" title="Ganti Mode Siang / Malam" aria-label="Ganti Tema"
                        class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-brand-ink hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                        <i class="bi bi-moon-stars-fill text-base absolute transition-all duration-300 ease-out opacity-100 rotate-0 scale-100 group-hover:-rotate-12 dark:opacity-0 dark:rotate-90 dark:scale-50"></i>
                        <i class="bi bi-sun-fill text-lg text-brand-yellow absolute transition-all duration-300 ease-out opacity-0 -rotate-90 scale-50 dark:opacity-100 dark:rotate-0 dark:scale-100 dark:group-hover:rotate-45"></i>
                    </button>

                    <button type="button" id="admin-mobile-menu-btn" onclick="toggleAdminMobileMenu()" aria-label="Buka Menu" aria-expanded="false"
                        class="w-10 h-10 rounded-xl flex items-center justify-center hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                        <div class="w-5 h-4 relative flex flex-col justify-between">
                            <span id="admin-ham-line-1" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                            <span id="admin-ham-line-2" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                            <span id="admin-ham-line-3" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- TAMPILAN DESKTOP (>= lg) -->
            <div class="hidden lg:flex h-20 px-8 items-center justify-between gap-3">
                <h2 id="admin-topbar-title" class="font-extrabold text-lg text-brand-ink truncate">@yield('page-title', 'Dashboard Admin')</h2>

                <div class="flex items-center gap-3 flex-shrink-0">
                    <button type="button" onclick="toggleThemeMode()" title="Ganti Mode Siang / Malam" aria-label="Ganti Tema"
                        class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-brand-ink hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                        <i class="bi bi-moon-stars-fill text-base absolute transition-all duration-300 ease-out opacity-100 rotate-0 scale-100 group-hover:-rotate-12 dark:opacity-0 dark:rotate-90 dark:scale-50"></i>
                        <i class="bi bi-sun-fill text-lg text-brand-yellow absolute transition-all duration-300 ease-out opacity-0 -rotate-90 scale-50 dark:opacity-100 dark:rotate-0 dark:scale-100 dark:group-hover:rotate-45"></i>
                    </button>

                    <div class="flex items-center gap-2 bg-brand-canvas px-3.5 py-1.5 rounded-full border border-gray-200/80">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-brand-ink">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </div>

            <!-- DRAWER MENU MOBILE (SLIDE DOWN IDENTIK HALAMAN UTAMA) -->
            <div id="admin-mobile-menu-drawer" class="lg:hidden max-h-0 opacity-0 -translate-y-2 pointer-events-none overflow-hidden bg-white transition-all duration-300 ease-out">
                <div class="px-4 pt-2 pb-5 space-y-1.5">
                    <a href="{{ route('admin.dashboard') }}" data-admin-spa data-admin-nav="dashboard" onclick="closeAdminMobileMenu()"
                       class="admin-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                        <i class="bi bi-grid-1x2-fill"></i> Ringkasan Dashboard
                    </a>

                    <a href="{{ route('admin.transactions') }}" data-admin-spa data-admin-nav="transactions" onclick="closeAdminMobileMenu()"
                       class="admin-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.transactions', 'admin.scan') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                        <i class="bi bi-qr-code-scan"></i> Transaksi &amp; Scan QR
                    </a>

                    <a href="{{ route('admin.vehicles.index') }}" data-admin-spa data-admin-nav="vehicles" onclick="closeAdminMobileMenu()"
                       class="admin-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.vehicles.*') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                        <i class="bi bi-car-front-fill"></i> Kelola Armada
                    </a>

                    <a href="{{ route('home') }}" target="_blank" onclick="closeAdminMobileMenu()"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-extrabold text-gray-600 hover:bg-brand-canvas transition">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-box-arrow-up-right"></i> Lihat Web Publik
                        </span>
                    </a>

                    <div class="pt-1">
                        @if(Route::has('logout'))
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold text-brand-pink hover:bg-brand-pinkSoft transition cursor-pointer">
                                    <i class="bi bi-box-arrow-left"></i> Keluar Admin
                                </button>
                            </form>
                        @else
                            <a href="{{ route('home') }}" onclick="closeAdminMobileMenu()"
                               class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold text-brand-pink hover:bg-brand-pinkSoft transition">
                                <i class="bi bi-box-arrow-left"></i> Kembali ke Beranda
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Kontainer SPA-Lite Admin -->
        <main id="admin-spa-main" class="flex-1 px-4 sm:px-8 pt-6 pb-12">
            @yield('content')
        </main>
    </div>

    <x-global-popup />

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.RentalinPopup?.show({
                    type: 'success',
                    title: 'Berhasil',
                    message: @json(session('success')),
                    duration: 4000,
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.RentalinPopup?.show({
                    type: 'failed',
                    title: 'Gagal',
                    message: @json(session('error')),
                    duration: 4000,
                });
            });
        </script>
    @endif

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    @include('partials.ui-scripts')
    <script>
        let isAdminMobileMenuOpen = false;

        function toggleAdminMobileMenu() {
            isAdminMobileMenuOpen = !isAdminMobileMenuOpen;
            syncAdminMobileMenuUI();
        }

        function closeAdminMobileMenu() {
            if (!isAdminMobileMenuOpen) return;
            isAdminMobileMenuOpen = false;
            syncAdminMobileMenuUI();
        }

        function syncAdminMobileMenuUI() {
            const btn    = document.getElementById('admin-mobile-menu-btn');
            const drawer = document.getElementById('admin-mobile-menu-drawer');
            const line1  = document.getElementById('admin-ham-line-1');
            const line2  = document.getElementById('admin-ham-line-2');
            const line3  = document.getElementById('admin-ham-line-3');

            if (!btn || !drawer) return;
            btn.setAttribute('aria-expanded', isAdminMobileMenuOpen ? 'true' : 'false');

            if (isAdminMobileMenuOpen) {
                line1.classList.add('translate-y-[7px]', 'rotate-45');
                line2.classList.add('opacity-0', 'scale-x-0');
                line3.classList.add('-translate-y-[7px]', '-rotate-45');

                drawer.classList.remove('max-h-0', 'opacity-0', '-translate-y-2', 'pointer-events-none');
                drawer.classList.add('max-h-96', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
            } else {
                line1.classList.remove('translate-y-[7px]', 'rotate-45');
                line2.classList.remove('opacity-0', 'scale-x-0');
                line3.classList.remove('-translate-y-[7px]', '-rotate-45');

                drawer.classList.add('max-h-0', 'opacity-0', '-translate-y-2', 'pointer-events-none');
                drawer.classList.remove('max-h-96', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
            }
        }

        function updateAdminActiveNav(url) {
            const path = new URL(url, window.location.origin).pathname;
            let activeKey = 'dashboard';
            if (path.includes('/vehicles')) activeKey = 'vehicles';
            else if (path.includes('/transactions') || path.includes('/scan')) activeKey = 'transactions';

            document.querySelectorAll('.admin-nav-link').forEach(link => {
                const isActive = link.dataset.adminNav === activeKey;
                link.classList.toggle('bg-brand-pink', isActive);
                link.classList.toggle('text-white', isActive);
                link.classList.toggle('shadow-xs', isActive);
                link.classList.toggle('text-gray-500', !isActive);
                link.classList.toggle('hover:bg-brand-canvas', !isActive);
                link.classList.toggle('hover:text-brand-ink', !isActive);
            });

            document.querySelectorAll('.admin-mob-link').forEach(link => {
                const isActive = link.dataset.adminNav === activeKey;
                link.classList.toggle('bg-brand-pink', isActive);
                link.classList.toggle('text-white', isActive);
                link.classList.toggle('text-gray-600', !isActive);
            });
        }

        async function navigateAdminSpa(url, pushHistory = true) {
            if (window.activeVideoStream) {
                window.activeVideoStream.getTracks().forEach(t => t.stop());
                window.activeVideoStream = null;
            }

            closeAdminMobileMenu();

            const mainEl = document.getElementById('admin-spa-main');
            const bar = document.getElementById('admin-progress-bar');

            bar.style.opacity = '1';
            bar.style.width = '65%';
            mainEl.classList.add('page-leaving');

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Load error');

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newMain = doc.getElementById('admin-spa-main');
                const newTitle = doc.getElementById('admin-topbar-title');

                if (!newMain) {
                    window.location.href = url;
                    return;
                }

                setTimeout(() => {
                    document.title = doc.title;
                    const curTitleEl = document.getElementById('admin-topbar-title');
                    if (newTitle && curTitleEl) curTitleEl.innerText = newTitle.innerText;
                    mainEl.innerHTML = newMain.innerHTML;

                    RentalinUI.initAll();

                    mainEl.querySelectorAll('script').forEach(oldScript => {
                        const s = document.createElement('script');
                        s.textContent = oldScript.textContent;
                        document.body.appendChild(s);
                        oldScript.remove();
                    });

                    if (pushHistory) window.history.pushState({}, '', url);
                    updateAdminActiveNav(url);
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    mainEl.classList.remove('page-leaving');
                    mainEl.classList.add('page-entering');
                    requestAnimationFrame(() => mainEl.classList.remove('page-entering'));

                    bar.style.width = '100%';
                    setTimeout(() => {
                        bar.style.opacity = '0';
                        bar.style.width = '0%';
                    }, 200);
                }, 160);
            } catch (e) {
                window.location.href = url;
            }
        }

        document.addEventListener('click', function(e) {
            if (isAdminMobileMenuOpen && !e.target.closest('#admin-topbar-header')) {
                closeAdminMobileMenu();
            }

            const link = e.target.closest('a[data-admin-spa]');
            if (link && link.href && !link.target && e.button === 0 && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (link.href !== window.location.href) {
                    navigateAdminSpa(link.href, true);
                }
            }
        });

        window.addEventListener('popstate', () => navigateAdminSpa(window.location.href, false));
    </script>
</body>
</html>