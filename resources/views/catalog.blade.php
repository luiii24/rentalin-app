@extends('layouts.app')

@section('title', 'Cari & Pilih Armada - Rentalin')

@section('content')
    @php
        $categoryOptions = ['all' => 'Semua Kategori'];
        foreach ($categories as $cat) {
            $categoryOptions[$cat->slug] = $cat->name;
        }
    @endphp

    <div class="relative overflow-x-clip">
        <!-- ================= LAYER BACKGROUND DEKORATIF ================= -->
        <!-- Sapuan Gradasi Halus di Area Header & Search Bar -->
        <div class="absolute inset-x-0 top-0 h-[26rem] pointer-events-none bg-gradient-to-b from-white/90 via-brand-yellowSoft/35 to-transparent dark:from-[#1B1D22]/75 dark:via-brand-yellow/5 dark:to-transparent"></div>

        <!-- Pola Titik Halus dengan Efek Memudar (Radial Mask) -->
        <div class="absolute inset-x-0 top-0 h-[32rem] pointer-events-none opacity-75 dark:opacity-35 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#2e323d_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_85%_70%_at_50%_15%,#000_65%,transparent_100%)]"></div>

        <!-- Pendaran Cahaya Lembut (Tanpa Lingkaran Solid) -->
        <div class="absolute -top-24 -left-24 w-80 h-80 sm:w-[26rem] sm:h-[26rem] rounded-full bg-brand-yellow/20 dark:bg-brand-yellow/10 blur-3xl pointer-events-none"></div>
        <div class="absolute top-16 -right-24 w-80 h-80 sm:w-[26rem] sm:h-[26rem] rounded-full bg-brand-pink/15 dark:bg-brand-pink/10 blur-3xl pointer-events-none"></div>

        <!-- Aksen Garis Lintasan Tipis di Belakang Search Bar -->
        <svg class="absolute inset-x-0 top-12 w-full h-56 pointer-events-none text-brand-ink/[0.04] dark:text-white/[0.03]" viewBox="0 0 1440 220" fill="none" preserveAspectRatio="none">
            <path d="M-40 165C260 65 520 215 820 120C1080 38 1280 95 1480 45" stroke="currentColor" stroke-width="2" stroke-dasharray="8 8"/>
            <path d="M-40 190C280 95 540 235 840 145C1100 65 1300 120 1480 75" stroke="currentColor" stroke-width="1.5"/>
        </svg>

        <!-- ================= KONTEN UTAMA KATALOG ================= -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-4xl font-extrabold text-brand-ink mb-2">Cari & Pilih Kendaraan</h1>
                <p class="text-gray-500 text-xs sm:text-sm">Ketik nama kendaraan atau klik ikon filter di sebelah kanan untuk atur tanggal & kategori</p>
            </div>

            <!-- SINGLE PILL SEARCH BAR + CUSTOM POPOVER FILTER -->
            <div class="relative max-w-2xl mx-auto mb-10 sm:mb-12">
                <!-- Kapsul Search Bar Utama -->
                <div class="flex items-center bg-brand-canvas border border-gray-200/80 focus-within:border-brand-pink rounded-full p-1.5 shadow-xs transition">
                    <div class="w-10 h-10 rounded-full bg-white text-brand-pink flex items-center justify-center flex-shrink-0 shadow-2xs">
                        <i class="bi bi-search text-sm"></i>
                    </div>

                    <input type="text" id="filter-query" oninput="applyVehicleFilters()"
                        value="{{ request('q') }}"
                        placeholder="Cari mobil, motor, matic, Avanza..."
                        class="flex-1 !bg-transparent border-none outline-none px-3.5 text-sm font-semibold text-brand-ink placeholder:font-normal placeholder:text-gray-400">

                    <button type="button" id="clear-query-btn" onclick="clearSearchQuery()" title="Hapus pencarian"
                        class="hidden w-8 h-8 rounded-full text-gray-400 hover:text-brand-ink flex items-center justify-center transition">
                        <i class="bi bi-x-circle-fill text-sm"></i>
                    </button>

                    <button type="button" id="toggle-filter-btn" onclick="toggleFilterPopover(event)" title="Filter Pencarian"
                        class="relative w-10 h-10 rounded-full hover:bg-white text-brand-ink flex items-center justify-center flex-shrink-0 transition">
                        <i class="bi bi-sliders2 text-base"></i>
                        <span id="active-filter-badge" class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-brand-pink text-white text-[9px] font-extrabold flex items-center justify-center shadow-2xs">
                            0
                        </span>
                    </button>
                </div>

                <!-- PANEL POPOVER FILTER -->
                <div id="filter-popover" onclick="event.stopPropagation()" class="hidden absolute right-0 left-0 sm:left-auto sm:w-[340px] mt-2.5 bg-white rounded-3xl shadow-2xl border border-gray-100 p-5 z-30 transition-all duration-200 origin-top-right scale-95 opacity-0">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                        <span class="font-extrabold text-xs uppercase tracking-wider text-brand-ink flex items-center gap-2">
                            <i class="bi bi-funnel text-brand-pink"></i> Filter Armada
                        </span>
                        <button type="button" onclick="resetAllFilters()" class="text-[11px] font-bold text-brand-pink hover:underline">
                            Reset Semua
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Komponen Date Picker Paten -->
                        <x-datepicker
                            name="date"
                            id="filter-date"
                            label="Tanggal Butuh / Pengambilan"
                            :value="request('date', date('Y-m-d'))"
                            :min="date('Y-m-d')"
                            onchange="applyVehicleFilters"
                        />

                        <!-- Komponen Dropdown Paten: Kategori -->
                        <x-select
                            name="category"
                            id="filter-category"
                            label="Kategori"
                            :options="$categoryOptions"
                            :selected="request('category', 'all')"
                            onchange="applyVehicleFilters"
                        />

                        <!-- Komponen Dropdown Paten: Ketersediaan -->
                        <x-select
                            name="status"
                            id="filter-status"
                            label="Ketersediaan"
                            :options="['all' => 'Semua Unit', 'tersedia' => 'Hanya yang Tersedia']"
                            :selected="request('status', 'all')"
                            onchange="applyVehicleFilters"
                        />

                        <!-- Komponen Dropdown Paten: Urutkan Tarif -->
                        <x-select
                            name="sort"
                            id="filter-sort"
                            label="Urutkan Tarif"
                            :options="[
                                'default' => 'Rekomendasi Default',
                                'price_asc' => 'Harga Terendah',
                                'price_desc' => 'Harga Tertinggi',
                            ]"
                            :selected="request('sort', 'default')"
                            onchange="applyVehicleFilters"
                        />
                    </div>

                    <button type="button" onclick="closeFilterPopover()" class="mt-5 w-full py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold transition shadow-xs">
                        Terapkan Filter
                    </button>
                </div>

                <!-- Baris Info Kecil di Bawah Search Bar -->
                <div class="flex items-center justify-between px-4 mt-2.5 text-[11px] text-gray-400">
                    <span>Menampilkan <strong id="visible-count" class="text-brand-ink">{{ count($vehicles) }}</strong> armada untuk <strong id="info-selected-date" class="text-brand-ink"></strong></span>
                    <span id="active-filter-summary" class="text-brand-pink font-semibold"></span>
                </div>
            </div>

            <!-- GRID KENDARAAN -->
            <div id="vehicle-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-7">
                @forelse($vehicles as $index => $vehicle)
                    @php
                        $images = is_array($vehicle->images) && count($vehicle->images) > 0
                            ? array_map(fn($img) => str_starts_with($img, 'http') ? $img : asset('storage/' . $img), $vehicle->images)
                            : ['https://placehold.co/600x450/f3f4f6/9ca3af?text=No+Image'];
                        $coverImage = $images[0];
                        $isAvailable = $vehicle->status === 'tersedia' && $vehicle->available_stock > 0;
                        $searchHaystack = strtolower($vehicle->name . ' ' . $vehicle->spec . ' ' . ($vehicle->category->name ?? ''));
                    @endphp

                    <div class="vehicle-card group flex flex-col bg-white rounded-3xl border border-gray-100/80 p-3.5 shadow-2xs hover:shadow-md transition duration-300"
                         data-order="{{ $index }}"
                         data-price="{{ $vehicle->price_per_day }}"
                         data-search="{{ $searchHaystack }}"
                         data-category="{{ $vehicle->category->slug ?? 'all' }}"
                         data-status="{{ $isAvailable ? 'tersedia' : 'tidak' }}">
                        
                        <div onclick='openGalleryModal(@json($vehicle->name), @json($vehicle->spec), @json($images))'
                             class="relative overflow-hidden rounded-2xl bg-brand-canvas aspect-[4/3] mb-4 cursor-pointer">
                            <img src="{{ $coverImage }}" alt="{{ $vehicle->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500 {{ !$isAvailable ? 'grayscale opacity-75' : '' }}">
                            
                            <div class="absolute top-2.5 left-2.5 flex gap-1.5">
                                @if($vehicle->status === 'maintenance')
                                    <span class="bg-amber-500 text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full shadow-xs">
                                        Maintenance
                                    </span>
                                @elseif($isAvailable)
                                    <span class="bg-brand-yellow text-[#1E1E1E] text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full shadow-xs">
                                        Sisa {{ $vehicle->available_stock }} Unit
                                    </span>
                                @else
                                    <span class="bg-gray-800 text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full shadow-xs">
                                        Stok Habis
                                    </span>
                                @endif
                            </div>

                            <span class="absolute bottom-2.5 right-2.5 bg-white/95 backdrop-blur-xs text-brand-ink text-[10px] font-bold px-2.5 py-1 rounded-full shadow-xs flex items-center gap-1">
                                <i class="bi bi-images text-brand-pink"></i> {{ count($images) }} Foto
                            </span>
                        </div>

                        <div class="px-1 flex-1 flex flex-col">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h3 class="text-base font-bold text-brand-ink truncate">{{ $vehicle->name }}</h3>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 bg-brand-canvas px-2.5 py-0.5 rounded-full flex-shrink-0">
                                    {{ $vehicle->category->name ?? 'Unit' }}
                                </span>
                            </div>
                            <p class="text-gray-500 text-xs mb-4">{{ $vehicle->spec }}</p>

                            <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-100">
                                <div class="font-extrabold text-brand-ink text-sm">
                                    Rp {{ number_format($vehicle->price_per_day, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-gray-400">/hari</span>
                                </div>

                                @if($isAvailable)
                                    <button type="button"
                                        onclick="openBookingModal({{ $vehicle->id }}, '{{ addslashes($vehicle->name) }}', '{{ addslashes($vehicle->spec) }}', {{ $vehicle->price_per_day }})"
                                        class="bg-brand-pink hover:bg-brand-pinkHover text-white px-4 py-2 rounded-full text-xs font-bold transition shadow-xs">
                                        Sewa
                                    </button>
                                @else
                                    <button type="button" disabled class="bg-gray-100 dark:bg-gray-800 text-gray-400 px-4 py-2 rounded-full text-xs font-semibold cursor-not-allowed">
                                        Habis
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12 bg-brand-canvas rounded-3xl">
                        <p class="text-gray-500 text-sm">Belum ada data kendaraan tersedia.</p>
                    </div>
                @endforelse

                <div id="empty-filter-state" class="hidden col-span-full text-center py-14 bg-brand-canvas rounded-3xl px-4">
                    <div class="w-12 h-12 rounded-full bg-brand-pink/15 text-brand-pink inline-flex items-center justify-center text-xl mb-3">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 class="font-extrabold text-base text-brand-ink mb-1">Kendaraan tidak ditemukan</h3>
                    <p class="text-xs text-gray-500 mb-4">Coba ubah kata kunci pencarian atau pilih filter lain.</p>
                    <button type="button" onclick="resetAllFilters()" class="bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-bold px-5 py-2.5 rounded-full transition">
                        Tampilkan Semua Armada
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL GALERI FOTO DETAIL -->
    <div id="galleryModal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl border border-gray-100">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 id="gal-title" class="font-extrabold text-base sm:text-lg text-brand-ink">Detail Kendaraan</h3>
                    <p id="gal-spec" class="text-xs text-gray-500">Spesifikasi</p>
                </div>
                <button type="button" onclick="closePublicModal('galleryModal')" class="w-9 h-9 rounded-full bg-brand-canvas hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                <div class="aspect-[16/10] bg-brand-canvas rounded-2xl overflow-hidden mb-4">
                    <img id="gal-main-img" src="" alt="Preview" class="w-full h-full object-cover">
                </div>
                <div id="gal-thumbs" class="flex gap-2.5 overflow-x-auto pb-2"></div>
            </div>
        </div>
    </div>

    <!-- MODAL FORM PEMESANAN (JUGA MENGGUNAKAN KOMPONEN DATEPICKER & SELECT PATEN) -->
    <div id="bookingModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-gray-100">
            <div class="p-5 sm:p-6 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                <h2 class="text-lg sm:text-xl font-extrabold text-brand-ink">Lengkapi Pesanan</h2>
                <button type="button" onclick="closePublicModal('bookingModal')" class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('booking.store') }}" method="POST" class="p-5 sm:p-6">
                @csrf
                <input type="hidden" name="vehicle_id" id="b-vehicleId">

                <div class="bg-brand-yellowSoft border border-brand-yellow/50 p-4 rounded-2xl mb-5 flex items-center justify-between gap-2">
                    <div>
                        <h4 id="b-vehicleName" class="font-bold text-sm sm:text-base text-brand-ink">Nama Kendaraan</h4>
                        <p id="b-vehicleSpec" class="text-xs text-gray-600">Spesifikasi</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p id="b-vehiclePrice" class="text-xs sm:text-sm font-extrabold text-brand-ink">Rp 0 / hari</p>
                    </div>
                </div>

                <div class="space-y-4 mb-5">
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" name="customer_name" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-xs font-semibold focus:outline-none focus:border-brand-pink" placeholder="Contoh: Budi Santoso">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Nomor WhatsApp</label>
                        <input type="tel" name="customer_phone" required class="w-full border border-gray-200 rounded-2xl px-4 py-3 text-xs font-semibold focus:outline-none focus:border-brand-pink" placeholder="0812xxxxxxxx">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-datepicker
                            name="pickup_date"
                            id="b-date"
                            label="Tgl Pengambilan"
                            :value="date('Y-m-d')"
                            :min="date('Y-m-d')"
                        />
                        <x-select
                            name="duration_days"
                            id="b-duration"
                            label="Durasi Sewa"
                            :options="[
                                '1' => '1 Hari',
                                '2' => '2 Hari',
                                '3' => '3 Hari',
                                '5' => '5 Hari',
                                '7' => '7 Hari',
                            ]"
                            selected="1"
                            onchange="updateEstimatedTotal"
                        />
                    </div>
                </div>

                <div class="bg-brand-canvas rounded-2xl p-4 mb-5 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-600">Estimasi Total Bayar (QRIS):</span>
                    <span id="b-estimatedTotal" class="text-base sm:text-lg font-extrabold text-brand-ink">Rp 0</span>
                </div>

                <label class="flex items-start gap-3 mb-6 cursor-pointer">
                    <input type="checkbox" required class="mt-0.5 w-4 h-4 accent-brand-pink border-gray-300 rounded">
                    <span class="text-xs text-gray-500 leading-relaxed">
                        Saya bersedia menitipkan <strong>KTP Asli</strong> saat pengambilan unit dan menyelesaikan pembayaran QRIS pada nota digital.
                    </span>
                </label>

                <button type="submit" class="w-full bg-brand-pink hover:bg-brand-pinkHover text-white rounded-full py-3.5 text-sm font-bold shadow-sm transition">
                    Buat Nota Digital & QRIS
                </button>
            </form>
        </div>
    </div>

    <script>
        var catalogTodayStr = "{{ date('Y-m-d') }}";

        function toggleFilterPopover(e) {
            if (e) e.stopPropagation();
            const popover = document.getElementById('filter-popover');
            const isHidden = popover.classList.contains('hidden');

            if (isHidden) {
                popover.classList.remove('hidden');
                requestAnimationFrame(() => {
                    popover.classList.remove('scale-95', 'opacity-0');
                    popover.classList.add('scale-100', 'opacity-100');
                });
            } else {
                closeFilterPopover();
            }
        }

        function closeFilterPopover() {
            const popover = document.getElementById('filter-popover');
            if (!popover || popover.classList.contains('hidden')) return;
            RentalinUI.closeAllDropdowns();
            RentalinUI.closeAllDatePickers();
            popover.classList.remove('scale-100', 'opacity-100');
            popover.classList.add('scale-95', 'opacity-0');
            setTimeout(() => popover.classList.add('hidden'), 180);
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#filter-popover') && !e.target.closest('#toggle-filter-btn')) {
                closeFilterPopover();
            }
        });

        function applyVehicleFilters() {
            const queryInput = document.getElementById('filter-query');
            const query = queryInput.value.trim().toLowerCase();
            const pickedDate = document.getElementById('filter-date').value;
            const cat = document.getElementById('filter-category').value;
            const status = document.getElementById('filter-status').value;
            const sort = document.getElementById('filter-sort').value;

            document.getElementById('clear-query-btn').classList.toggle('hidden', query === '');

            // Update info tanggal di bawah search bar
            const d = new Date(pickedDate + 'T00:00:00');
            if (!isNaN(d)) {
                document.getElementById('info-selected-date').innerText =
                    `${d.getDate()} ${RentalinUI.monthShort[d.getMonth()]} ${d.getFullYear()}`;
            }

            let activeFilterCount = 0;
            if (pickedDate !== catalogTodayStr) activeFilterCount++;
            if (cat !== 'all') activeFilterCount++;
            if (status !== 'all') activeFilterCount++;
            if (sort !== 'default') activeFilterCount++;

            const badge = document.getElementById('active-filter-badge');
            badge.innerText = activeFilterCount;
            badge.classList.toggle('hidden', activeFilterCount === 0);

            const summaryEl = document.getElementById('active-filter-summary');
            summaryEl.innerText = activeFilterCount > 0 ? `${activeFilterCount} filter aktif` : '';

            const grid = document.getElementById('vehicle-grid');
            const cards = Array.from(document.querySelectorAll('.vehicle-card'));

            cards.sort((a, b) => {
                if (sort === 'price_asc') return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                if (sort === 'price_desc') return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                return parseInt(a.dataset.order) - parseInt(b.dataset.order);
            });

            let visibleCount = 0;
            cards.forEach(card => {
                grid.appendChild(card);
                const matchQuery = query === '' || card.dataset.search.includes(query);
                const matchCat = cat === 'all' || card.dataset.category === cat;
                const matchStatus = status === 'all' || card.dataset.status === status;
                const isVisible = matchQuery && matchCat && matchStatus;

                card.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount++;
            });

            const emptyState = document.getElementById('empty-filter-state');
            grid.appendChild(emptyState);
            emptyState.classList.toggle('hidden', visibleCount > 0);

            document.getElementById('visible-count').innerText = visibleCount;

            const url = new URL(window.location);
            query ? url.searchParams.set('q', queryInput.value.trim()) : url.searchParams.delete('q');
            pickedDate !== catalogTodayStr ? url.searchParams.set('date', pickedDate) : url.searchParams.delete('date');
            cat !== 'all' ? url.searchParams.set('category', cat) : url.searchParams.delete('category');
            status !== 'all' ? url.searchParams.set('status', status) : url.searchParams.delete('status');
            sort !== 'default' ? url.searchParams.set('sort', sort) : url.searchParams.delete('sort');
            window.history.replaceState({}, '', url);
        }

        function clearSearchQuery() {
            document.getElementById('filter-query').value = '';
            applyVehicleFilters();
        }

        function resetAllFilters() {
            document.getElementById('filter-query').value = '';
            RentalinUI.setDateValue('filter-date', catalogTodayStr);
            RentalinUI.setSelectValue('filter-category', 'all');
            RentalinUI.setSelectValue('filter-status', 'all');
            RentalinUI.setSelectValue('filter-sort', 'default');
            applyVehicleFilters();
        }

        applyVehicleFilters();

        function openGalleryModal(name, spec, images) {
            document.getElementById('gal-title').innerText = name;
            document.getElementById('gal-spec').innerText = spec;
            const mainImg = document.getElementById('gal-main-img');
            const thumbs = document.getElementById('gal-thumbs');

            mainImg.src = images[0];
            thumbs.innerHTML = '';

            images.forEach((src, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `w-20 h-14 rounded-xl overflow-hidden flex-shrink-0 border-2 ${idx === 0 ? 'border-brand-pink' : 'border-transparent opacity-70 hover:opacity-100'}`;
                btn.innerHTML = `<img src="${src}" class="w-full h-full object-cover">`;
                btn.onclick = () => {
                    mainImg.src = src;
                    thumbs.querySelectorAll('button').forEach(b => b.className = 'w-20 h-14 rounded-xl overflow-hidden flex-shrink-0 border-2 border-transparent opacity-70 hover:opacity-100');
                    btn.className = 'w-20 h-14 rounded-xl overflow-hidden flex-shrink-0 border-2 border-brand-pink';
                };
                thumbs.appendChild(btn);
            });

            openPublicModal('galleryModal');
        }

        var activeDailyPrice = 0;
        function openBookingModal(id, name, spec, price) {
            activeDailyPrice = price;
            document.getElementById('b-vehicleId').value = id;
            document.getElementById('b-vehicleName').innerText = name;
            document.getElementById('b-vehicleSpec').innerText = spec;
            document.getElementById('b-vehiclePrice').innerText = 'Rp ' + price.toLocaleString('id-ID') + ' / hari';

            // Sinkronkan tanggal pengambilan di modal dengan tanggal filter
            const filteredDate = document.getElementById('filter-date').value;
            if (filteredDate) {
                RentalinUI.setDateValue('b-date', filteredDate);
            }

            updateEstimatedTotal();
            openPublicModal('bookingModal');
        }

        function updateEstimatedTotal() {
            const days = parseInt(document.getElementById('b-duration').value || 1);
            const total = activeDailyPrice * days;
            document.getElementById('b-estimatedTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
@endsection