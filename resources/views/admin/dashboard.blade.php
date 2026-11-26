@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Ringkasan & Transaksi')

@section('content')
    @php
        $initialTab = in_array(request('tab'), ['transaksi', 'scan']) ? 'transaksi' : 'ringkasan';
    @endphp

    <!-- KARTU STATISTIK UTAMA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-7">
        <div class="bg-white rounded-3xl p-5 border border-gray-100/80 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Total Pendapatan</p>
                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-ink">
                    Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}
                </h3>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-brand-yellow text-[#1E1E1E] flex items-center justify-center text-lg shadow-2xs">
                <i class="bi bi-wallet2"></i>
            </span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-gray-100/80 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Sedang Disewa</p>
                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-ink">{{ $activeRentals ?? 0 }} Unit</h3>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-brand-pink text-white flex items-center justify-center text-lg shadow-2xs">
                <i class="bi bi-car-front-fill"></i>
            </span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-gray-100/80 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Menunggu Konfirmasi</p>
                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-ink">{{ $pendingRentals ?? 0 }} Pesanan</h3>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-lg">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-gray-100/80 shadow-2xs flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">Stok Unit Siap</p>
                <h3 class="text-xl sm:text-2xl font-extrabold text-brand-ink">{{ $availableVehicles ?? 0 }} Unit</h3>
            </div>
            <span class="w-12 h-12 rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                <i class="bi bi-check2-circle"></i>
            </span>
        </div>
    </div>

    <!-- SWITCHER TAB & FILTER -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="inline-flex items-center gap-1.5 bg-white p-1.5 rounded-full border border-gray-200/80 shadow-2xs">
            <button type="button" id="tab-btn-ringkasan" onclick="switchAdminTab('ringkasan')"
                class="px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 {{ $initialTab === 'ringkasan' ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:text-brand-ink' }}">
                <i class="bi bi-table"></i> Semua Transaksi
            </button>
            <button type="button" id="tab-btn-transaksi" onclick="switchAdminTab('transaksi')"
                class="px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 {{ $initialTab === 'transaksi' ? 'bg-brand-pink text-white shadow-xs' : 'text-gray-500 hover:text-brand-ink' }}">
                <i class="bi bi-qr-code-scan"></i> Scan & Cek Transaksi
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="w-48">
                <x-datepicker
                    name="filter_rental_date"
                    id="admin-filter-date"
                    :value="date('Y-m-d')"
                    onchange="filterAdminRentals"
                />
            </div>
            <div class="w-44">
                <x-select
                    name="filter_rental_status"
                    id="admin-filter-status"
                    :options="[
                        'all' => 'Semua Status',
                        'menunggu' => 'Menunggu',
                        'aktif' => 'Sedang Aktif',
                        'selesai' => 'Selesai',
                    ]"
                    selected="all"
                    onchange="filterAdminRentals"
                />
            </div>
        </div>
    </div>

    <!-- PANEL SCAN QR & CARI TRANSAKSI -->
    <div id="panel-scan-transaksi" class="{{ $initialTab === 'transaksi' ? '' : 'hidden' }} mb-7">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-5 bg-white rounded-3xl border border-gray-100/80 p-6 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-brand-ink">Scan QR Nota Digital</h3>
                        <p class="text-xs text-gray-500">Pindai QR nota pelanggan atau ketik kode booking</p>
                    </div>
                    <span class="w-10 h-10 rounded-2xl bg-brand-yellowSoft text-brand-ink flex items-center justify-center text-base">
                        <i class="bi bi-qr-code"></i>
                    </span>
                </div>

                <div id="qr-reader-wrapper" class="hidden mb-4 overflow-hidden rounded-2xl border-2 border-brand-pink bg-black">
                    <div id="qr-reader" class="w-full"></div>
                </div>

                <div class="flex gap-2 mb-4">
                    <button type="button" id="btn-toggle-camera" onclick="toggleQrCamera()"
                        class="flex-1 py-2.5 px-4 rounded-full bg-brand-yellow hover:opacity-90 text-[#1E1E1E] text-xs font-extrabold transition flex items-center justify-center gap-2">
                        <i class="bi bi-camera-video-fill"></i> <span id="camera-btn-label">Buka Kamera Scan</span>
                    </button>
                </div>

                <div class="pt-3 border-t border-gray-100">
                    <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Atau Masukkan Kode Booking</label>
                    <div class="flex gap-2">
                        <input type="text" id="scan-booking-input" oninput="lookupTransactionByCode(this.value)"
                            placeholder="Contoh: RNTL-DEMO01"
                            class="flex-1 border border-gray-200 rounded-2xl px-4 py-3 font-mono text-xs font-bold uppercase focus:outline-none focus:border-brand-pink">
                        <button type="button" onclick="lookupTransactionByCode(document.getElementById('scan-booking-input').value)"
                            class="px-5 py-3 rounded-2xl bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition">
                            Cek
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 bg-white rounded-3xl border border-gray-100/80 p-6 shadow-2xs flex flex-col justify-between">
                <div id="scan-result-empty" class="my-auto text-center py-8">
                    <div class="w-14 h-14 rounded-full bg-brand-pinkSoft text-brand-pink inline-flex items-center justify-center text-2xl mb-3">
                        <i class="bi bi-upc-scan"></i>
                    </div>
                    <h4 class="font-extrabold text-sm text-brand-ink mb-1">Menunggu Hasil Scan / Kode Booking</h4>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        Rincian penyewa, unit armada, dan tombol buka nota digital akan langsung muncul di sini.
                    </p>
                </div>

                <div id="scan-result-card" class="hidden space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Kode Transaksi</span>
                            <h4 id="res-code" class="font-mono text-lg font-extrabold text-brand-pink">RNTL-XXXXXX</h4>
                        </div>
                        <span id="res-status-badge" class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-emerald-500/15 text-emerald-600">
                            AKTIF
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-brand-canvas/60 p-3.5 rounded-2xl">
                            <p class="text-gray-400 mb-0.5">Penyewa</p>
                            <p id="res-customer" class="font-extrabold text-brand-ink text-sm">-</p>
                            <p id="res-phone" class="text-gray-500 mt-0.5">-</p>
                        </div>
                        <div class="bg-brand-canvas/60 p-3.5 rounded-2xl">
                            <p class="text-gray-400 mb-0.5">Unit Armada</p>
                            <p id="res-vehicle" class="font-extrabold text-brand-ink text-sm">-</p>
                            <p id="res-schedule" class="text-gray-500 mt-0.5">-</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                        <div>
                            <p class="text-[11px] text-gray-400">Total Tagihan</p>
                            <p id="res-total" class="text-lg font-extrabold text-brand-ink">Rp 0</p>
                        </div>
                        <a id="res-invoice-link" href="#" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition">
                            <i class="bi bi-receipt"></i> Buka Nota Digital
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL TRANSAKSI UTAMA -->
    <div class="mb-3 flex items-center justify-between">
        <h3 class="font-extrabold text-base text-brand-ink">Daftar Transaksi Penyewaan</h3>
        <span class="text-xs text-gray-400">Total <strong class="text-brand-ink">{{ count($rentals) }}</strong> transaksi</span>
    </div>

    <x-table id="rentals-table" :headers="[
        ['label' => 'Kode Booking', 'sortable' => true],
        ['label' => 'Penyewa', 'sortable' => true],
        ['label' => 'Armada', 'sortable' => true],
        ['label' => 'Jadwal & Durasi', 'sortable' => true],
        ['label' => 'Total Bayar', 'sortable' => true],
        ['label' => 'Status', 'sortable' => true],
        ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
    ]">
        @forelse($rentals as $rental)
            <tr class="rental-row hover:bg-brand-canvas/60 transition"
                data-code="{{ strtoupper($rental->booking_code) }}"
                data-customer="{{ $rental->customer_name }}"
                data-phone="{{ $rental->customer_phone }}"
                data-vehicle="{{ $rental->vehicle->name ?? '-' }}"
                data-date="{{ $rental->pickup_date }}"
                data-duration="{{ $rental->duration_days }}"
                data-total="{{ $rental->total_price }}"
                data-status="{{ $rental->status }}"
                data-invoice-url="{{ route('admin.invoice', $rental->booking_code) }}">

                <td class="py-3.5 pl-5 pr-2">
                    <input type="checkbox" onchange="this.closest('tr').classList.toggle('bg-brand-pinkSoft/25', this.checked)"
                        class="w-4 h-4 rounded border-gray-300 accent-brand-pink cursor-pointer">
                </td>

                <td class="py-3.5 px-4 font-mono font-extrabold text-brand-pink" data-sort="{{ $rental->booking_code }}">
                    {{ $rental->booking_code }}
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $rental->customer_name }}">
                    <p class="font-bold text-brand-ink">{{ $rental->customer_name }}</p>
                    <p class="text-[11px] text-gray-400">{{ $rental->customer_phone }}</p>
                </td>

                <td class="py-3.5 px-4 font-bold text-brand-ink" data-sort="{{ $rental->vehicle->name ?? '' }}">
                    {{ $rental->vehicle->name ?? '-' }}
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $rental->pickup_date }}">
                    <p class="font-bold text-brand-ink">{{ \Carbon\Carbon::parse($rental->pickup_date)->format('d M Y') }}</p>
                    <p class="text-[11px] text-gray-400">{{ $rental->duration_days }} Hari</p>
                </td>

                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $rental->total_price }}">
                    Rp {{ number_format($rental->total_price, 0, ',', '.') }}
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $rental->status }}">
                    @if($rental->status === 'aktif')
                        <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-[11px] font-extrabold px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sedang Aktif
                        </span>
                    @elseif($rental->status === 'menunggu')
                        <span class="inline-flex items-center gap-1.5 bg-brand-yellowSoft text-brand-ink border border-brand-yellow/60 text-[11px] font-extrabold px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-yellow"></span> Menunggu
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-gray-200/70 text-gray-600 text-[11px] font-extrabold px-3 py-1 rounded-full">
                            Selesai
                        </span>
                    @endif
                </td>

                <td class="py-3.5 px-4 text-right space-x-1">
                    <button type="button" onclick="inspectTransactionRow(this.closest('tr'))" title="Detail / Verifikasi"
                        class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-yellowSoft text-brand-ink inline-flex items-center justify-center transition">
                        <i class="bi bi-qr-code-scan text-xs"></i>
                    </button>
                    <a href="{{ route('admin.invoice', $rental->booking_code) }}" target="_blank" title="Lihat Nota"
                        class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink inline-flex items-center justify-center transition">
                        <i class="bi bi-receipt text-xs"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="py-12 text-center text-gray-400 text-xs">
                    Belum ada transaksi penyewaan yang tercatat.
                </td>
            </tr>
        @endforelse
    </x-table>

    <script>
        var html5QrScanner = null;
        var isCameraRunning = false;

        function switchAdminTab(tab) {
            const btnRingkasan = document.getElementById('tab-btn-ringkasan');
            const btnTransaksi = document.getElementById('tab-btn-transaksi');
            const scanPanel = document.getElementById('panel-scan-transaksi');
            if (!btnRingkasan || !btnTransaksi || !scanPanel) return;

            if (tab === 'transaksi') {
                btnTransaksi.className = 'px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 bg-brand-pink text-white shadow-xs';
                btnRingkasan.className = 'px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 text-gray-500 hover:text-brand-ink';
                scanPanel.classList.remove('hidden');
            } else {
                btnRingkasan.className = 'px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 bg-brand-pink text-white shadow-xs';
                btnTransaksi.className = 'px-5 py-2.5 rounded-full text-xs font-extrabold transition flex items-center gap-2 text-gray-500 hover:text-brand-ink';
                scanPanel.classList.add('hidden');
                if (isCameraRunning) toggleQrCamera();
            }
        }

        function toggleQrCamera() {
            const wrapper = document.getElementById('qr-reader-wrapper');
            const label = document.getElementById('camera-btn-label');

            if (!isCameraRunning) {
                wrapper.classList.remove('hidden');
                label.innerText = 'Hentikan Kamera';
                html5QrScanner = new Html5Qrcode('qr-reader');
                html5QrScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    (decodedText) => {
                        const extractedCode = decodedText.split('/').pop().trim().toUpperCase();
                        document.getElementById('scan-booking-input').value = extractedCode;
                        lookupTransactionByCode(extractedCode);
                        toggleQrCamera();
                    }
                ).then(() => {
                    isCameraRunning = true;
                }).catch(() => {
                    wrapper.classList.add('hidden');
                    label.innerText = 'Buka Kamera Scan';
                    alert('Kamera tidak tersedia atau izin kamera belum diaktifkan. Silakan ketik kode booking secara manual.');
                });
            } else if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    wrapper.classList.add('hidden');
                    label.innerText = 'Buka Kamera Scan';
                    isCameraRunning = false;
                });
            }
        }

        function lookupTransactionByCode(rawCode) {
            const code = (rawCode || '').trim().toUpperCase();
            const rows = Array.from(document.querySelectorAll('.rental-row'));
            const matched = rows.find(r => r.dataset.code === code);

            const emptyBox = document.getElementById('scan-result-empty');
            const cardBox = document.getElementById('scan-result-card');

            if (!matched) {
                cardBox.classList.add('hidden');
                emptyBox.classList.remove('hidden');
                return;
            }

            emptyBox.classList.add('hidden');
            cardBox.classList.remove('hidden');

            document.getElementById('res-code').innerText = matched.dataset.code;
            document.getElementById('res-customer').innerText = matched.dataset.customer;
            document.getElementById('res-phone').innerText = matched.dataset.phone;
            document.getElementById('res-vehicle').innerText = matched.dataset.vehicle;
            document.getElementById('res-schedule').innerText = `${matched.dataset.date} (${matched.dataset.duration} Hari)`;
            document.getElementById('res-total').innerText = 'Rp ' + parseInt(matched.dataset.total || 0).toLocaleString('id-ID');
            document.getElementById('res-invoice-link').href = matched.dataset.invoiceUrl;
            document.getElementById('res-status-badge').innerText = matched.dataset.status.toUpperCase();
        }

        function inspectTransactionRow(row) {
            switchAdminTab('transaksi');
            document.getElementById('scan-booking-input').value = row.dataset.code;
            lookupTransactionByCode(row.dataset.code);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function filterAdminRentals() {
            const status = document.getElementById('admin-filter-status').value;
            document.querySelectorAll('.rental-row').forEach(row => {
                const matchStatus = status === 'all' || row.dataset.status === status;
                row.classList.toggle('hidden', !matchStatus);
            });
        }
    </script>
@endsection