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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @include('partials.theme-engine')
    <style>
        #admin-spa-main {
            transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .page-leaving { opacity: 0; transform: translateY(8px); }
        .page-entering { opacity: 0; transform: translateY(-6px); }
        #admin-progress-bar { transition: width 0.3s ease, opacity 0.2s ease; }

        html.dark body { background-color: #121316; color: #F3F4F6; }
        html.dark .bg-white { background-color: #1B1D22 !important; }
        html.dark .bg-brand-canvas { background-color: #16181D !important; }
        html.dark .bg-brand-canvas\/50,
        html.dark .bg-brand-canvas\/40 { background-color: rgba(22, 24, 29, 0.65) !important; }
        html.dark .bg-brand-yellowSoft { background-color: rgba(243, 198, 79, 0.14) !important; }
        html.dark .text-brand-ink { color: #F9FAFB !important; }
        html.dark .text-gray-600 { color: #D1D5DB !important; }
        html.dark .text-gray-500 { color: #9CA3AF !important; }
        html.dark .border-gray-200,
        html.dark .border-gray-200\/80 { border-color: #2A2D35 !important; }
        html.dark .border-gray-100,
        html.dark .border-gray-100\/80,
        html.dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #242730 !important; }
        html.dark input[type="text"],
        html.dark input[type="number"],
        html.dark input[type="tel"],
        html.dark textarea { background-color: #121316 !important; color: #F9FAFB !important; border-color: #2A2D35 !important; }
    </style>
</head>
<body class="bg-brand-canvas text-brand-ink font-sans antialiased min-h-screen flex transition-colors duration-300">

    <!-- Progress Bar Atas SPA -->
    <div id="admin-progress-bar" class="fixed top-0 left-0 h-1 bg-brand-pink z-[70] w-0 opacity-0"></div>

    <!-- SIDEBAR ADMIN -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-white border-r border-gray-100 fixed inset-y-0 left-0 z-30">
        <div class="h-20 px-6 flex items-center gap-3 border-b border-gray-100">
            <span class="w-9 h-9 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-base">
                <i class="bi bi-car-front-fill"></i>
            </span>
            <div>
                <span class="font-extrabold text-lg tracking-tight text-brand-ink block leading-none">Rentalin</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-brand-pink">Admin Panel</span>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" data-admin-spa data-admin-nav="ringkasan"
               class="admin-nav-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request()->routeIs('admin.dashboard') && request('tab') !== 'transaksi' ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:bg-brand-canvas hover:text-brand-ink' }}">
                <i class="bi bi-grid-1x2-fill text-sm"></i> Ringkasan Dashboard
            </a>

            <a href="{{ route('admin.dashboard', ['tab' => 'transaksi']) }}" data-admin-spa data-admin-nav="transaksi"
               class="admin-nav-link flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-extrabold transition {{ request('tab') === 'transaksi' ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:bg-brand-canvas hover:text-brand-ink' }}">
                <i class="bi bi-qr-code-scan text-sm"></i> Transaksi & Scan QR
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
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-2xl bg-brand-canvas hover:bg-brand-pinkSoft hover:text-brand-pink text-xs font-extrabold text-gray-600 transition">
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
        <!-- Topbar Admin -->
        <header class="bg-white/95 backdrop-blur-md border-b border-gray-100 px-4 sm:px-8 sticky top-0 z-20">
            <div class="h-16 sm:h-20 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="lg:hidden w-8 h-8 rounded-full bg-brand-yellow flex items-center justify-center text-[#1E1E1E] text-sm">
                        <i class="bi bi-car-front-fill"></i>
                    </span>
                    <h2 id="admin-topbar-title" class="font-extrabold text-base sm:text-lg text-brand-ink">@yield('page-title', 'Dashboard Admin')</h2>
                </div>

                <!-- Navigasi Cepat Mobile/Tablet -->
                <div class="flex lg:hidden items-center gap-1 bg-brand-canvas p-1 rounded-full border border-gray-200/80">
                    <a href="{{ route('admin.dashboard') }}" data-admin-spa data-admin-nav="ringkasan"
                       class="admin-mob-link px-3 py-1.5 rounded-full text-[11px] font-extrabold {{ request()->routeIs('admin.dashboard') && request('tab') !== 'transaksi' ? 'bg-brand-pink text-white' : 'text-gray-500' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.dashboard', ['tab' => 'transaksi']) }}" data-admin-spa data-admin-nav="transaksi"
                       class="admin-mob-link px-3 py-1.5 rounded-full text-[11px] font-extrabold {{ request('tab') === 'transaksi' ? 'bg-brand-pink text-white' : 'text-gray-500' }}">
                        Scan QR
                    </a>
                    <a href="{{ route('admin.vehicles.index') }}" data-admin-spa data-admin-nav="vehicles"
                       class="admin-mob-link px-3 py-1.5 rounded-full text-[11px] font-extrabold {{ request()->routeIs('admin.vehicles.*') ? 'bg-brand-pink text-white' : 'text-gray-500' }}">
                        Armada
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleThemeMode()" title="Ganti Mode Siang / Malam" aria-label="Ganti Tema"
                        class="group relative w-10 h-10 rounded-xl flex items-center justify-center text-brand-ink hover:bg-gray-100/80 dark:hover:bg-white/10 active:scale-90 transition-all duration-200 focus:outline-none cursor-pointer">
                        <i class="bi bi-moon-stars-fill text-base absolute transition-all duration-300 ease-out opacity-100 rotate-0 scale-100 group-hover:-rotate-12 dark:opacity-0 dark:rotate-90 dark:scale-50"></i>
                        <i class="bi bi-sun-fill text-lg text-brand-yellow absolute transition-all duration-300 ease-out opacity-0 -rotate-90 scale-50 dark:opacity-100 dark:rotate-0 dark:scale-100 dark:group-hover:rotate-45"></i>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 bg-brand-canvas px-3.5 py-1.5 rounded-full border border-gray-200/80">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-brand-ink">{{ auth()->user()->name ?? 'Administrator' }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Alert Sukses / Error -->
        <div class="px-4 sm:px-8 pt-6">
            @if(session('success'))
                <div class="mb-4 bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-sm"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-brand-pinkSoft border border-brand-pink/30 text-brand-pink px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-sm"></i> {{ session('error') }}
                </div>
            @endif
        </div>

        <!-- Kontainer SPA-Lite Admin -->
        <main id="admin-spa-main" class="flex-1 px-4 sm:px-8 pb-12">
            @yield('content')
        </main>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        function toggleThemeMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('rentalin_theme', isDark ? 'dark' : 'light');
        }

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
                    if (trigger) {
                        trigger.classList.remove('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                        trigger.classList.add('border-gray-200');
                    }
                    if (chevron) chevron.classList.remove('rotate-180', 'text-brand-pink');
                });
            },

            closeAllDatePickers() {
                document.querySelectorAll('.custom-datepicker-group').forEach(group => {
                    const card = group.querySelector('.custom-calendar-card');
                    const trigger = group.querySelector('.datepicker-trigger');
                    const chevron = group.querySelector('.datepicker-chevron');
                    if (card) card.classList.add('hidden');
                    if (trigger) {
                        trigger.classList.remove('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                        trigger.classList.add('border-gray-200');
                    }
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
                    triggerBtn.classList.remove('border-gray-200');
                    triggerBtn.classList.add('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
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
                    triggerBtn.classList.remove('border-gray-200');
                    triggerBtn.classList.add('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
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

            toggleAllTableRows(masterCheckbox, tableId) {
                const table = document.getElementById(tableId);
                if (!table) return;
                table.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
                    cb.checked = masterCheckbox.checked;
                    const tr = cb.closest('tr');
                    if (tr) tr.classList.toggle('bg-brand-pinkSoft/25', masterCheckbox.checked);
                });
            },

            sortTable(tableId, colIndex, headerBtn) {
                const table = document.getElementById(tableId);
                if (!table) return;
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1);
                const currentDir = headerBtn.dataset.dir === 'asc' ? 'desc' : 'asc';

                table.querySelectorAll('thead button').forEach(btn => {
                    btn.dataset.dir = '';
                    const ind = btn.querySelector('.sort-indicator');
                    if (ind) ind.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
                });

                headerBtn.dataset.dir = currentDir;
                const indicator = headerBtn.querySelector('.sort-indicator');
                if (indicator) {
                    indicator.innerHTML = currentDir === 'asc'
                        ? '<i class="bi bi-arrow-up text-brand-pink"></i>'
                        : '<i class="bi bi-arrow-down text-brand-pink"></i>';
                }

                rows.sort((a, b) => {
                    const cellA = a.children[colIndex];
                    const cellB = b.children[colIndex];
                    const valA = (cellA?.dataset.sort || cellA?.innerText || '').trim();
                    const valB = (cellB?.dataset.sort || cellB?.innerText || '').trim();

                    const numA = parseFloat(valA.replace(/[^0-9.-]+/g, ''));
                    const numB = parseFloat(valB.replace(/[^0-9.-]+/g, ''));

                    if (!isNaN(numA) && !isNaN(numB) && /^\d/.test(valA.replace(/^Rp\s*/i, ''))) {
                        return currentDir === 'asc' ? numA - numB : numB - numA;
                    }
                    return currentDir === 'asc'
                        ? valA.localeCompare(valB, 'id', { sensitivity: 'base' })
                        : valB.localeCompare(valA, 'id', { sensitivity: 'base' });
                });

                rows.forEach(row => tbody.appendChild(row));
            },

            handleFileDrop(e, inputId, dropzone) {
                e.preventDefault();
                dropzone.classList.remove('border-brand-pink', 'bg-brand-pinkSoft/30');
                const input = document.getElementById(inputId);
                if (input && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    this.handleFileSelect(input);
                }
            },

            handleFileSelect(input) {
                const previewBox = document.getElementById('preview-' + input.id);
                if (!previewBox) return;
                previewBox.innerHTML = '';

                if (!input.files || input.files.length === 0) {
                    previewBox.classList.add('hidden');
                    return;
                }

                previewBox.classList.remove('hidden');
                Array.from(input.files).forEach((file, index) => {
                    const sizeKB = (file.size / 1024).toFixed(1);
                    const isImage = file.type.startsWith('image/');
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between gap-3 bg-brand-canvas/70 border border-gray-200/80 rounded-2xl p-2.5';

                    const leftSide = document.createElement('div');
                    leftSide.className = 'flex items-center gap-3 min-w-0';

                    if (isImage) {
                        const thumb = document.createElement('img');
                        thumb.src = URL.createObjectURL(file);
                        thumb.className = 'w-10 h-10 rounded-xl object-cover flex-shrink-0 border border-gray-200';
                        leftSide.appendChild(thumb);
                    } else {
                        const iconBox = document.createElement('div');
                        iconBox.className = 'w-10 h-10 rounded-xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-base flex-shrink-0';
                        iconBox.innerHTML = '<i class="bi bi-file-earmark-text-fill"></i>';
                        leftSide.appendChild(iconBox);
                    }

                    const meta = document.createElement('div');
                    meta.className = 'min-w-0';
                    meta.innerHTML = `
                        <p class="text-xs font-bold text-brand-ink truncate">${file.name}</p>
                        <p class="text-[10px] text-gray-400">${sizeKB} KB</p>
                    `;
                    leftSide.appendChild(meta);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'w-7 h-7 rounded-full hover:bg-brand-pinkSoft text-gray-400 hover:text-brand-pink flex items-center justify-center transition flex-shrink-0';
                    removeBtn.innerHTML = '<i class="bi bi-x-lg text-xs"></i>';
                    removeBtn.onclick = () => {
                        const dt = new DataTransfer();
                        Array.from(input.files).forEach((f, i) => {
                            if (i !== index) dt.items.add(f);
                        });
                        input.files = dt.files;
                        this.handleFileSelect(input);
                    };

                    item.appendChild(leftSide);
                    item.appendChild(removeBtn);
                    previewBox.appendChild(item);
                });
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

        // =========================================================================
        // ADMIN SPA-LITE NAVIGATION (PINDAH MENU & TAB TANPA RELOAD HALAMAN)
        // =========================================================================
        function updateAdminActiveNav(url) {
            const parsed = new URL(url, window.location.origin);
            const path = parsed.pathname;
            const tab = parsed.searchParams.get('tab');

            let activeKey = 'ringkasan';
            if (path.includes('/vehicles')) activeKey = 'vehicles';
            else if (tab === 'transaksi') activeKey = 'transaksi';

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
                link.classList.toggle('text-gray-500', !isActive);
            });
        }

        async function navigateAdminSpa(url, pushHistory = true) {
            const parsed = new URL(url, window.location.origin);
            const currentParsed = new URL(window.location.href);

            // Jika masih di halaman /portal/dashboard dan hanya ganti ?tab=..., langsung switch tab instan tanpa fetch
            if (parsed.pathname === currentParsed.pathname && parsed.pathname.endsWith('/dashboard') && typeof window.switchAdminTab === 'function') {
                const targetTab = parsed.searchParams.get('tab') === 'transaksi' ? 'transaksi' : 'ringkasan';
                window.switchAdminTab(targetTab);
                if (pushHistory) window.history.pushState({}, '', url);
                updateAdminActiveNav(url);
                return;
            }

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
                    if (newTitle) document.getElementById('admin-topbar-title').innerText = newTitle.innerText;
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
            if (!e.target.closest('.custom-select-group')) RentalinUI.closeAllDropdowns();
            if (!e.target.closest('.custom-datepicker-group')) RentalinUI.closeAllDatePickers();

            const link = e.target.closest('a[data-admin-spa]');
            if (link && link.href && !link.target && e.button === 0 && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (link.href !== window.location.href) {
                    navigateAdminSpa(link.href, true);
                }
            }
        });

        window.addEventListener('popstate', () => navigateAdminSpa(window.location.href, false));

        RentalinUI.initAll();
    </script>
</body>
</html>