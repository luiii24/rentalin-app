<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rentalin - Sewa Mobil & Motor Praktis')</title>
    @include('partials.theme-engine')
</head>
<body class="bg-brand-canvas text-brand-ink font-sans antialiased min-h-screen flex flex-col transition-colors duration-300">

    <!-- Progress Bar SPA-Lite -->
    <div id="public-progress-bar" class="fixed top-0 left-0 h-1 bg-brand-pink z-[70] w-0 opacity-0"></div>

    <!-- NAVBAR PUBLIK -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" data-public-spa class="flex items-center gap-2.5 group">
                <span class="w-9 h-9 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-base transition-transform duration-300 group-hover:scale-105">
                    <i class="bi bi-car-front-fill"></i>
                </span>
                <span class="font-extrabold text-lg sm:text-xl tracking-tight text-brand-ink">Rentalin</span>
            </a>

            <!-- Menu Desktop (Beranda - Cara Pesan - Cari Armada) -->
            <nav class="hidden md:flex items-center gap-7">
                <a href="{{ route('home') }}" data-public-spa data-nav-key="home"
                   class="public-nav-link text-xs font-extrabold transition {{ request()->routeIs('home') ? 'text-brand-pink' : 'text-gray-500 hover:text-brand-ink' }}">
                    Beranda
                </a>
                <a href="{{ route('how-to-order') }}" data-public-spa data-nav-key="howto"
                   class="public-nav-link text-xs font-extrabold transition {{ request()->routeIs('how-to-order') ? 'text-brand-pink' : 'text-gray-500 hover:text-brand-ink' }}">
                    Cara Pesan
                </a>
                <a href="{{ route('vehicles.catalog') }}" data-public-spa data-nav-key="catalog"
                   class="public-nav-link text-xs font-extrabold transition {{ request()->routeIs('vehicles.catalog') ? 'text-brand-pink' : 'text-gray-500 hover:text-brand-ink' }}">
                    Cari Armada
                </a>
            </nav>

            <!-- Aksi Kanan: Cek Nota + Switch Tema + Hamburger 3 Garis -->
            <div class="flex items-center gap-1.5 sm:gap-2.5">
                <button type="button" onclick="openPublicModal('checkInvoiceModal')"
                    class="inline-flex items-center gap-1.5 bg-brand-yellowSoft hover:bg-brand-yellow/40 active:scale-95 text-brand-ink text-xs font-extrabold px-3.5 sm:px-4 py-2 rounded-full transition-all duration-200 cursor-pointer">
                    <i class="bi bi-receipt text-brand-pink"></i>
                    <span>Cek Nota</span>
                </button>

                <!-- Tombol Mode Gelap / Terang -->
                <button type="button" onclick="toggleThemeMode()" title="Ganti Mode Siang / Malam" aria-label="Ganti Tema"
                    class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-brand-ink hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                    <i class="bi bi-moon-stars-fill text-base absolute transition-all duration-300 ease-out opacity-100 rotate-0 scale-100 group-hover:-rotate-12 dark:opacity-0 dark:rotate-90 dark:scale-50"></i>
                    <i class="bi bi-sun-fill text-lg text-brand-yellow absolute transition-all duration-300 ease-out opacity-0 -rotate-90 scale-50 dark:opacity-100 dark:rotate-0 dark:scale-100 dark:group-hover:rotate-45"></i>
                </button>

                <!-- Tombol Menu Mobile (3 Garis Animasi Otomatis ke X) -->
                <button type="button" id="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Buka Menu" aria-expanded="false"
                    class="md:hidden w-10 h-10 rounded-xl flex items-center justify-center hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                    <div class="w-5 h-4 relative flex flex-col justify-between">
                        <span id="ham-line-1" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                        <span id="ham-line-2" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                        <span id="ham-line-3" class="block w-full h-0.5 bg-brand-ink rounded-full transition-all duration-300 ease-in-out origin-center"></span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Drawer Menu Mobile -->
        <div id="mobile-menu-drawer" class="md:hidden max-h-0 opacity-0 -translate-y-2 pointer-events-none overflow-hidden bg-white transition-all duration-300 ease-out">
            <div class="px-4 pt-2 pb-5 space-y-1.5">
                <a href="{{ route('home') }}" data-public-spa data-nav-key="home" onclick="closeMobileMenu()"
                   class="public-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('home') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                    <i class="bi bi-house-door-fill"></i> Beranda
                </a>
                <a href="{{ route('how-to-order') }}" data-public-spa data-nav-key="howto" onclick="closeMobileMenu()"
                   class="public-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('how-to-order') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                    <i class="bi bi-journal-check"></i> Cara Pesan
                </a>
                <a href="{{ route('vehicles.catalog') }}" data-public-spa data-nav-key="catalog" onclick="closeMobileMenu()"
                   class="public-mob-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('vehicles.catalog') ? 'bg-brand-pink text-white' : 'text-gray-600 hover:bg-brand-canvas' }}">
                    <i class="bi bi-car-front-fill"></i> Cari Armada
                </a>
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA (SPA-LITE) -->
    <main id="public-spa-main" class="flex-1">
        @yield('content')
    </main>

    <!-- FOOTER RINGKAS -->
    <footer class="bg-white py-8 mt-auto transition-colors duration-300">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-[11px]">
                    <i class="bi bi-car-front-fill"></i>
                </span>
                <span class="font-extrabold text-brand-ink">Rentalin</span>
                <span>&copy; {{ date('Y') }} Sewa Kendaraan Praktis.</span>
            </div>
            <div class="flex items-center gap-5 font-bold">
                <a href="{{ route('how-to-order') }}" data-public-spa class="hover:text-brand-pink transition">Cara Pesan</a>
                <a href="{{ route('vehicles.catalog') }}" data-public-spa class="hover:text-brand-pink transition">Katalog Unit</a>
                <a href="{{ route('login') }}" class="hover:text-brand-pink transition">Portal Admin</a>
            </div>
        </div>
    </footer>

    <!-- MODAL CEK NOTA DIGITAL -->
    <div id="checkInvoiceModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-base">
                        <i class="bi bi-qr-code-scan"></i>
                    </span>
                    <div>
                        <h3 class="font-extrabold text-base text-brand-ink">Cek Nota Digital</h3>
                        <p class="text-[11px] text-gray-500">Masukkan kode booking Anda</p>
                    </div>
                </div>
                <button type="button" onclick="closePublicModal('checkInvoiceModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form onsubmit="submitInvoiceLookup(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Kode Booking</label>
                    <input type="text" id="public-invoice-code" required placeholder="Contoh: RNTL-DEMO01"
                        class="w-full rounded-2xl px-4 py-3 font-mono text-xs font-bold uppercase">
                </div>
                <button type="submit" class="w-full py-3.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold shadow-xs transition-all">
                    Buka Nota Sekarang
                </button>
            </form>
        </div>
    </div>

    <!-- WIDGET BANTUAN CS 24/7 (DRAGGABLE + ANIMASI POPUP) -->
    <div id="cs-floating-widget" class="fixed bottom-5 right-5 z-40 w-12 h-12 select-none">
        <div id="cs-popup-box"
            class="absolute bottom-16 right-0 w-72 bg-white rounded-3xl shadow-xl p-4 origin-bottom-right transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] opacity-0 scale-75 translate-y-2 pointer-events-none invisible">
            <div class="flex items-center justify-between pb-2.5 mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-extrabold text-brand-ink">CS Rentalin 24/7</span>
                </div>
                <button type="button" onclick="toggleCpPopup(false)" class="w-6 h-6 rounded-full hover:bg-brand-canvas text-gray-400 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-[11px]"></i>
                </button>
            </div>
            <p class="text-[11px] text-gray-500 leading-relaxed mb-3">
                Ada pertanyaan soal ketersediaan unit atau perpanjangan sewa? Hubungi admin kami langsung.
            </p>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Rentalin,%20saya%20ingin%20tanya%20sewa%20kendaraan" target="_blank"
               class="w-full py-2.5 px-4 rounded-full bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white text-xs font-extrabold flex items-center justify-center gap-2 transition-all">
                <i class="bi bi-whatsapp text-sm"></i> Chat WhatsApp Admin
            </a>
        </div>

        <button type="button" id="cs-toggle-btn" title="Bantuan CS (Geser untuk memindahkan)" aria-label="Bantuan CS"
            class="relative w-12 h-12 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white shadow-lg flex items-center justify-center text-lg transition-all duration-200 cursor-grab active:cursor-grabbing touch-none focus:outline-none">
            <i id="cs-icon-headset" class="bi bi-headset absolute transition-all duration-300 opacity-100 rotate-0 scale-100"></i>
            <i id="cs-icon-close" class="bi bi-x-lg text-base absolute transition-all duration-300 opacity-0 -rotate-90 scale-50"></i>
        </button>
    </div>

    <script>
        let isMobileMenuOpen = false;

        function toggleMobileMenu() {
            isMobileMenuOpen = !isMobileMenuOpen;
            syncMobileMenuUI();
        }

        function closeMobileMenu() {
            if (!isMobileMenuOpen) return;
            isMobileMenuOpen = false;
            syncMobileMenuUI();
        }

        function syncMobileMenuUI() {
            const btn = document.getElementById('mobile-menu-btn');
            const drawer = document.getElementById('mobile-menu-drawer');
            const line1 = document.getElementById('ham-line-1');
            const line2 = document.getElementById('ham-line-2');
            const line3 = document.getElementById('ham-line-3');

            if (!btn || !drawer) return;
            btn.setAttribute('aria-expanded', isMobileMenuOpen ? 'true' : 'false');

            if (isMobileMenuOpen) {
                line1.classList.add('translate-y-[7px]', 'rotate-45');
                line2.classList.add('opacity-0', 'scale-x-0');
                line3.classList.add('-translate-y-[7px]', '-rotate-45');

                drawer.classList.remove('max-h-0', 'opacity-0', '-translate-y-2', 'pointer-events-none');
                drawer.classList.add('max-h-80', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
            } else {
                line1.classList.remove('translate-y-[7px]', 'rotate-45');
                line2.classList.remove('opacity-0', 'scale-x-0');
                line3.classList.remove('-translate-y-[7px]', '-rotate-45');

                drawer.classList.add('max-h-0', 'opacity-0', '-translate-y-2', 'pointer-events-none');
                drawer.classList.remove('max-h-80', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
            }
        }

        function openPublicModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        }

        function closePublicModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function submitInvoiceLookup(e) {
            e.preventDefault();
            const code = (document.getElementById('public-invoice-code')?.value || '').trim().toUpperCase();
            if (!code) return;
            closePublicModal('checkInvoiceModal');
            window.location.href = `/nota/${encodeURIComponent(code)}`;
        }

        let isCsPopupOpen = false;

        function updateCsPopupPosition() {
            const widget = document.getElementById('cs-floating-widget');
            const box = document.getElementById('cs-popup-box');
            if (!widget || !box) return;

            const rect = widget.getBoundingClientRect();
            const openRight = rect.left < window.innerWidth / 2;
            const openDown = rect.top < 240;

            box.classList.toggle('left-0', openRight);
            box.classList.toggle('right-0', !openRight);
            box.classList.toggle('top-16', openDown);
            box.classList.toggle('bottom-16', !openDown);

            box.classList.remove('origin-bottom-right', 'origin-bottom-left', 'origin-top-right', 'origin-top-left');
            if (openDown && openRight) box.classList.add('origin-top-left');
            else if (openDown && !openRight) box.classList.add('origin-top-right');
            else if (!openDown && openRight) box.classList.add('origin-bottom-left');
            else box.classList.add('origin-bottom-right');
        }

        function toggleCpPopup(forceState) {
            const box = document.getElementById('cs-popup-box');
            const iconHeadset = document.getElementById('cs-icon-headset');
            const iconClose = document.getElementById('cs-icon-close');
            if (!box) return;

            isCsPopupOpen = typeof forceState === 'boolean' ? forceState : !isCsPopupOpen;

            if (isCsPopupOpen) {
                updateCsPopupPosition();
                box.classList.remove('opacity-0', 'scale-75', 'translate-y-2', 'pointer-events-none', 'invisible');
                box.classList.add('opacity-100', 'scale-100', 'translate-y-0', 'pointer-events-auto', 'visible');

                iconHeadset?.classList.add('opacity-0', 'rotate-90', 'scale-50');
                iconHeadset?.classList.remove('opacity-100', 'rotate-0', 'scale-100');
                iconClose?.classList.remove('opacity-0', '-rotate-90', 'scale-50');
                iconClose?.classList.add('opacity-100', 'rotate-0', 'scale-100');
            } else {
                box.classList.add('opacity-0', 'scale-75', 'translate-y-2', 'pointer-events-none', 'invisible');
                box.classList.remove('opacity-100', 'scale-100', 'translate-y-0', 'pointer-events-auto', 'visible');

                iconHeadset?.classList.remove('opacity-0', 'rotate-90', 'scale-50');
                iconHeadset?.classList.add('opacity-100', 'rotate-0', 'scale-100');
                iconClose?.classList.add('opacity-0', '-rotate-90', 'scale-50');
                iconClose?.classList.remove('opacity-100', 'rotate-0', 'scale-100');
            }
        }

        (function initDraggableCsWidget() {
            const widget = document.getElementById('cs-floating-widget');
            const btn = document.getElementById('cs-toggle-btn');
            if (!widget || !btn) return;

            let isPointerDown = false;
            let hasDragged = false;
            let startX = 0, startY = 0;
            let origLeft = 0, origTop = 0;

            btn.addEventListener('pointerdown', (e) => {
                if (e.button !== 0 && e.pointerType === 'mouse') return;
                isPointerDown = true;
                hasDragged = false;
                startX = e.clientX;
                startY = e.clientY;

                const rect = widget.getBoundingClientRect();
                origLeft = rect.left;
                origTop = rect.top;

                btn.setPointerCapture(e.pointerId);
            });

            btn.addEventListener('pointermove', (e) => {
                if (!isPointerDown) return;
                const dx = e.clientX - startX;
                const dy = e.clientY - startY;

                if (!hasDragged && Math.hypot(dx, dy) > 5) {
                    hasDragged = true;
                    btn.classList.add('scale-110', 'shadow-2xl');
                }

                if (hasDragged) {
                    const pad = 12;
                    const maxLeft = window.innerWidth - widget.offsetWidth - pad;
                    const maxTop = window.innerHeight - widget.offsetHeight - pad;

                    const clampedLeft = Math.min(Math.max(pad, origLeft + dx), maxLeft);
                    const clampedTop = Math.min(Math.max(pad, origTop + dy), maxTop);

                    widget.style.left = clampedLeft + 'px';
                    widget.style.top = clampedTop + 'px';
                    widget.style.right = 'auto';
                    widget.style.bottom = 'auto';

                    if (isCsPopupOpen) updateCsPopupPosition();
                }
            });

            const finishDrag = (e) => {
                if (!isPointerDown) return;
                isPointerDown = false;
                btn.classList.remove('scale-110', 'shadow-2xl');
                if (btn.hasPointerCapture(e.pointerId)) {
                    btn.releasePointerCapture(e.pointerId);
                }

                if (!hasDragged) {
                    toggleCpPopup();
                }
            };

            btn.addEventListener('pointerup', finishDrag);
            btn.addEventListener('pointercancel', finishDrag);

            window.addEventListener('resize', () => {
                if (!widget.style.left) return;
                const pad = 12;
                const maxLeft = window.innerWidth - widget.offsetWidth - pad;
                const maxTop = window.innerHeight - widget.offsetHeight - pad;
                widget.style.left = Math.min(Math.max(pad, parseFloat(widget.style.left)), maxLeft) + 'px';
                widget.style.top = Math.min(Math.max(pad, parseFloat(widget.style.top)), maxTop) + 'px';
            });
        })();

        window.RentalinUI = {
            monthShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            monthFull: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            dayFull: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],

            formatHumanDate(isoDate) {
                if (!isoDate) return 'Pilih tanggal';
                const d = new Date(isoDate + 'T00:00:00');
                if (isNaN(d)) return isoDate;
                return `${d.getDate()} ${this.monthShort[d.getMonth()]} ${d.getFullYear()}, ${this.dayFull[d.getDay()]}`;
            },

            closeAllDropdowns() {
                document.querySelectorAll('.custom-select-group').forEach(group => {
                    const trigger = group.querySelector('.custom-select-trigger');
                    const menu = group.querySelector('.custom-select-menu');
                    const chevron = trigger?.querySelector('.bi-chevron-down');
                    if (menu) menu.classList.add('hidden');
                    if (trigger) trigger.classList.remove('ring-4', 'ring-brand-pink/15');
                    if (chevron) chevron.classList.remove('rotate-180', 'text-brand-pink');
                });
            },

            closeAllDatePickers() {
                document.querySelectorAll('.custom-datepicker-group').forEach(group => {
                    const card = group.querySelector('.custom-calendar-card');
                    const trigger = group.querySelector('.datepicker-trigger');
                    const chevron = group.querySelector('.datepicker-chevron');
                    if (card) card.classList.add('hidden');
                    if (trigger) trigger.classList.remove('ring-4', 'ring-brand-pink/15');
                    if (chevron) chevron.classList.remove('rotate-180', 'text-brand-pink');
                });
            },

            toggleDropdown(e, triggerBtn) {
                if (e) e.stopPropagation();
                const group = triggerBtn.closest('.custom-select-group');
                const menu = group.querySelector('.custom-select-menu');
                const chevron = triggerBtn.querySelector('.bi-chevron-down');
                const isOpening = menu.classList.contains('hidden');

                this.closeAllDropdowns();
                this.closeAllDatePickers();

                if (isOpening) {
                    menu.classList.remove('hidden');
                    triggerBtn.classList.add('ring-4', 'ring-brand-pink/15');
                    chevron.classList.add('rotate-180', 'text-brand-pink');
                }
            },

            selectOption(optionBtn, value, label) {
                const group = optionBtn.closest('.custom-select-group');
                const targetInput = document.getElementById(group.dataset.target);
                const labelSpan = group.querySelector('.custom-select-label');

                if (targetInput) targetInput.value = value;
                if (labelSpan) labelSpan.innerText = label;

                group.querySelectorAll('.custom-option').forEach(opt => {
                    opt.classList.remove('text-brand-pink', 'bg-brand-pinkSoft/40', 'font-bold');
                    opt.classList.add('text-brand-ink');
                    const check = opt.querySelector('.bi-check2');
                    if (check) check.classList.add('hidden');
                });

                optionBtn.classList.remove('text-brand-ink');
                optionBtn.classList.add('text-brand-pink', 'bg-brand-pinkSoft/40', 'font-bold');
                const activeCheck = optionBtn.querySelector('.bi-check2');
                if (activeCheck) activeCheck.classList.remove('hidden');

                this.closeAllDropdowns();

                const callbackName = group.dataset.onchange;
                if (callbackName && typeof window[callbackName] === 'function') {
                    window[callbackName](value);
                }
            },

            setSelectValue(inputId, value) {
                const input = document.getElementById(inputId);
                if (!input) return;
                const group = input.closest('.custom-select-group');
                const targetOpt = group?.querySelector(`.custom-option[data-value="${value}"]`);
                if (targetOpt) this.selectOption(targetOpt, value, targetOpt.dataset.label);
            },

            toggleDatePicker(e, triggerBtn) {
                if (e) e.stopPropagation();
                const group = triggerBtn.closest('.custom-datepicker-group');
                const card = group.querySelector('.custom-calendar-card');
                const chevron = group.querySelector('.datepicker-chevron');
                const isOpening = card.classList.contains('hidden');

                this.closeAllDropdowns();
                this.closeAllDatePickers();

                if (isOpening) {
                    const input = document.getElementById(group.dataset.target);
                    const currentVal = input?.value || new Date().toISOString().slice(0, 10);
                    group.dataset.tempDate = currentVal;
                    group.dataset.viewDate = currentVal;
                    this.renderCalendar(group);

                    card.classList.remove('hidden');
                    triggerBtn.classList.add('ring-4', 'ring-brand-pink/15');
                    chevron.classList.add('rotate-180', 'text-brand-pink');
                }
            },

            renderCalendar(group) {
                const viewIso = group.dataset.viewDate || new Date().toISOString().slice(0, 10);
                const tempIso = group.dataset.tempDate || viewIso;
                const minIso = group.dataset.min || '';

                const viewObj = new Date(viewIso + 'T00:00:00');
                const year = viewObj.getFullYear();
                const month = viewObj.getMonth();

                group.querySelector('.calendar-month-label').innerText = `${this.monthFull[month]} ${year}`;
                const grid = group.querySelector('.calendar-days-grid');
                grid.innerHTML = '';

                const firstDayIndex = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();

                for (let i = 0; i < firstDayIndex; i++) grid.innerHTML += `<div></div>`;

                for (let day = 1; day <= daysInMonth; day++) {
                    const mm = String(month + 1).padStart(2, '0');
                    const dd = String(day).padStart(2, '0');
                    const cellIso = `${year}-${mm}-${dd}`;
                    const isDisabled = minIso !== '' && cellIso < minIso;
                    const isSelected = cellIso === tempIso;
                    const isSunday = new Date(year, month, day).getDay() === 0;

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = day;

                    if (isDisabled) {
                        btn.disabled = true;
                        btn.className = 'w-8 h-8 mx-auto rounded-full text-gray-300 dark:text-gray-700 cursor-not-allowed flex items-center justify-center';
                    } else if (isSelected) {
                        btn.className = 'w-8 h-8 mx-auto rounded-full bg-brand-pink text-white font-extrabold shadow-sm flex items-center justify-center transition transform scale-105';
                    } else {
                        btn.className = `w-8 h-8 mx-auto rounded-full hover:bg-brand-pinkSoft/70 flex items-center justify-center transition ${isSunday ? 'text-brand-pink' : 'text-brand-ink'}`;
                        btn.onclick = () => {
                            group.dataset.tempDate = cellIso;
                            this.renderCalendar(group);
                        };
                    }
                    grid.appendChild(btn);
                }
            },

            changeMonth(navBtn, delta) {
                const group = navBtn.closest('.custom-datepicker-group');
                const currentView = new Date((group.dataset.viewDate || new Date().toISOString().slice(0, 10)) + 'T00:00:00');
                currentView.setMonth(currentView.getMonth() + delta);
                group.dataset.viewDate = `${currentView.getFullYear()}-${String(currentView.getMonth() + 1).padStart(2, '0')}-01`;
                this.renderCalendar(group);
            },

            confirmDate(doneBtn) {
                const group = doneBtn.closest('.custom-datepicker-group');
                const input = document.getElementById(group.dataset.target);
                const chosenIso = group.dataset.tempDate;

                if (input && chosenIso) {
                    input.value = chosenIso;
                    group.querySelector('.datepicker-display-text').innerText = this.formatHumanDate(chosenIso);
                }
                this.closeAllDatePickers();

                const callbackName = group.dataset.onchange;
                if (callbackName && typeof window[callbackName] === 'function') window[callbackName](chosenIso);
            },

            setDateValue(inputId, isoDate) {
                const input = document.getElementById(inputId);
                if (!input || !isoDate) return;
                input.value = isoDate;
                const group = input.closest('.custom-datepicker-group');
                if (group) {
                    group.dataset.tempDate = isoDate;
                    group.dataset.viewDate = isoDate;
                    const labelEl = group.querySelector('.datepicker-display-text');
                    if (labelEl) labelEl.innerText = this.formatHumanDate(isoDate);
                }
            },

            initAll() {
                document.querySelectorAll('.custom-datepicker-group').forEach(group => {
                    const input = document.getElementById(group.dataset.target);
                    const labelEl = group.querySelector('.datepicker-display-text');
                    if (input && labelEl && input.value) {
                        labelEl.innerText = this.formatHumanDate(input.value);
                    }
                });
            }
        };

        function updatePublicActiveNav(url) {
            const path = new URL(url, window.location.origin).pathname;
            let activeKey = 'home';
            if (path.includes('/cara-pesan')) activeKey = 'howto';
            else if (path.includes('/cari-kendaraan')) activeKey = 'catalog';

            document.querySelectorAll('.public-nav-link').forEach(link => {
                const isActive = link.dataset.navKey === activeKey;
                link.classList.toggle('text-brand-pink', isActive);
                link.classList.toggle('text-gray-500', !isActive);
            });

            document.querySelectorAll('.public-mob-link').forEach(link => {
                const isActive = link.dataset.navKey === activeKey;
                link.classList.toggle('bg-brand-pink', isActive);
                link.classList.toggle('text-white', isActive);
                link.classList.toggle('text-gray-600', !isActive);
            });
        }

        async function navigatePublicSpa(url, pushHistory = true) {
            const mainEl = document.getElementById('public-spa-main');
            const bar = document.getElementById('public-progress-bar');
            closeMobileMenu();

            bar.style.opacity = '1';
            bar.style.width = '65%';
            mainEl.classList.add('page-leaving');

            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Load error');

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newMain = doc.getElementById('public-spa-main');

                if (!newMain) {
                    window.location.href = url;
                    return;
                }

                setTimeout(() => {
                    document.title = doc.title;
                    mainEl.innerHTML = newMain.innerHTML;

                    RentalinUI.initAll();

                    mainEl.querySelectorAll('script').forEach(oldScript => {
                        const s = document.createElement('script');
                        s.textContent = oldScript.textContent;
                        document.body.appendChild(s);
                        oldScript.remove();
                    });

                    if (pushHistory) window.history.pushState({}, '', url);
                    updatePublicActiveNav(url);
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    mainEl.classList.remove('page-leaving');
                    mainEl.classList.add('page-entering');
                    requestAnimationFrame(() => mainEl.classList.remove('page-entering'));

                    bar.style.width = '100%';
                    setTimeout(() => {
                        bar.style.opacity = '0';
                        bar.style.width = '0%';
                    }, 200);
                }, 150);
            } catch (err) {
                window.location.href = url;
            }
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-select-group')) RentalinUI.closeAllDropdowns();
            if (!e.target.closest('.custom-datepicker-group')) RentalinUI.closeAllDatePickers();

            const link = e.target.closest('a[data-public-spa]');
            if (link && link.href && !link.target && e.button === 0 && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (link.href !== window.location.href) {
                    navigatePublicSpa(link.href, true);
                }
            }
        });

        window.addEventListener('popstate', () => navigatePublicSpa(window.location.href, false));

        RentalinUI.initAll();
    </script>
</body>
</html>