@extends('layouts.admin')

@section('title', 'Ringkasan Dashboard')
@section('page-title', 'Ringkasan Dashboard')

@section('content')
    @php
        $totalRevenue = $stats['revenue'] ?? 700000;
        $activeUnits  = $stats['rented'] ?? 1;
        $pendingCount = $stats['pending'] ?? 1;
        $readyUnits   = $stats['available'] ?? 4;
        $totalFleet   = $activeUnits + $readyUnits + 1;
    @endphp

    <!-- 4 KARTU STATISTIK UTAMA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-6">
        <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Pendapatan</p>
                <p class="text-xl sm:text-2xl font-extrabold text-brand-ink mt-1">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-brand-yellow text-[#1E1E1E] flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Sedang Disewa</p>
                <p class="text-xl sm:text-2xl font-extrabold text-brand-ink mt-1">{{ $activeUnits }} Unit</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-brand-pink text-white flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-car-front-fill"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Menunggu Konfirmasi</p>
                <p class="text-xl sm:text-2xl font-extrabold text-brand-ink mt-1">{{ $pendingCount }} Pesanan</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Stok Unit Siap</p>
                <p class="text-xl sm:text-2xl font-extrabold text-brand-ink mt-1">{{ $readyUnits }} Unit</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- BARIS TENGAH: RINGKASAN ARMADA & UPLOAD FOTO QRIS TOKO -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">
        <!-- KIRI (7 KOLOM): RINGKASAN KONDISI ARMADA & PINTASAN -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-brand-ink">Ringkasan Operasional &amp; Armada</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Rekap ketersediaan unit dan antrean verifikasi hari ini</p>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-brand-canvas text-[11px] font-extrabold text-brand-ink">
                        Total {{ $totalFleet }} Armada
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                    <div class="bg-brand-canvas rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-gray-500">Siap Disewa</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <p class="text-xl font-extrabold text-brand-ink">{{ $readyUnits }} Unit</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Kondisi bersih &amp; siap jalan</p>
                    </div>

                    <div class="bg-brand-canvas rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-gray-500">Sedang Jalan</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-pink"></span>
                        </div>
                        <p class="text-xl font-extrabold text-brand-ink">{{ $activeUnits }} Unit</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Dalam masa sewa aktif</p>
                    </div>

                    <div class="bg-brand-canvas rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold text-gray-500">Perawatan</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        </div>
                        <p class="text-xl font-extrabold text-brand-ink">1 Unit</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Servis rutin berkala</p>
                    </div>
                </div>
            </div>

            <!-- AKSI CEPAT KE TAB LAIN -->
            <div class="pt-3 flex flex-wrap items-center gap-2.5">
                <a href="{{ Route::has('admin.transactions') ? route('admin.transactions') : url('/portal/admin/transactions') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition">
                    <i class="bi bi-qr-code-scan"></i> Buka Scan QR &amp; Transaksi
                </a>

                <a href="{{ Route::has('admin.fleet') ? route('admin.fleet') : url('/portal/admin/fleet') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition">
                    <i class="bi bi-car-front"></i> Kelola Armada
                </a>
            </div>
        </div>

        <!-- KANAN (5 KOLOM): UPLOAD FOTO QRIS PEMBAYARAN TOKO -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-brand-ink">Foto QRIS Pembayaran</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Gambar QRIS ini otomatis tampil di semua Nota Digital penyewa</p>
                    </div>
                    <span id="qris-status-pill" class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-[10px] font-extrabold">
                        Aktif
                    </span>
                </div>

                <input type="file" id="store-qris-input" accept="image/*" class="hidden" onchange="handleStoreQrisUpload(this)">

                <div onclick="document.getElementById('store-qris-input').click()"
                     class="bg-brand-canvas hover:opacity-95 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition min-h-[180px]">
                    <div id="store-qris-empty" class="flex flex-col items-center">
                        <div class="w-11 h-11 rounded-2xl bg-white text-brand-pink flex items-center justify-center text-lg mb-2">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <p class="text-xs font-extrabold text-brand-ink">Klik untuk Upload Foto QRIS</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Format PNG / JPG (Rasio kotak 1:1 disarankan)</p>
                    </div>

                    <div id="store-qris-preview-box" class="hidden flex-col items-center gap-2">
                        <img id="store-qris-preview-img" src="" alt="QRIS Toko" class="w-36 h-36 object-contain rounded-xl bg-white p-2 shadow-2xs">
                        <span class="text-[11px] font-bold text-brand-pink">Klik gambar untuk mengganti foto QRIS</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-2">
                <button type="button" onclick="document.getElementById('store-qris-input').click()"
                    class="flex-1 py-2.5 px-4 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold transition cursor-pointer">
                    <i class="bi bi-upload mr-1"></i> Upload / Ganti QRIS
                </button>
                <button type="button" id="btn-reset-qris" onclick="resetStoreQris()"
                    class="hidden py-2.5 px-3.5 rounded-full bg-brand-canvas hover:opacity-85 text-gray-500 text-xs font-extrabold transition cursor-pointer">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- RINGKASAN AKTIVITAS TERBARU (5 DATA TERAKHIR) -->
    <div class="bg-white rounded-3xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-brand-ink">Aktivitas Penyewaan Terbaru</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pantauan cepat pesanan masuk terbaru</p>
            </div>
            <a href="{{ Route::has('admin.transactions') ? route('admin.transactions') : url('/portal/admin/transactions') }}"
               class="text-xs font-extrabold text-brand-pink hover:underline">
                Lihat Semua &amp; Scan QR →
            </a>
        </div>

        <div class="space-y-2.5">
            <div class="bg-brand-canvas rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white text-brand-pink flex items-center justify-center font-mono text-xs font-extrabold">
                        #01
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-extrabold text-brand-pink">RNTL-DEMO01</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                Menunggu Konfirmasi
                            </span>
                        </div>
                        <p class="text-xs font-bold text-brand-ink mt-0.5">Budi Santoso • Toyota Avanza Veloz (2 Hari)</p>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-4">
                    <span class="text-sm font-extrabold text-brand-ink">Rp 700.000</span>
                    <a href="{{ Route::has('admin.transactions') ? route('admin.transactions') : url('/portal/admin/transactions') }}"
                       class="px-3.5 py-1.5 rounded-full bg-white text-brand-ink text-[11px] font-extrabold hover:opacity-85 transition">
                        Proses
                    </a>
                </div>
            </div>

            <div class="bg-brand-canvas rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white text-brand-ink flex items-center justify-center font-mono text-xs font-extrabold">
                        #02
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-extrabold text-brand-pink">RNTL-DEMO02</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                Menunggu Pembayaran
                            </span>
                        </div>
                        <p class="text-xs font-bold text-brand-ink mt-0.5">Siti Aminah • Honda Brio RS (1 Hari)</p>
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-4">
                    <span class="text-sm font-extrabold text-brand-ink">Rp 300.000</span>
                    <a href="{{ url('/portal/invoice/RNTL-DEMO02') }}" target="_blank"
                       class="px-3.5 py-1.5 rounded-full bg-white text-brand-ink text-[11px] font-extrabold hover:opacity-85 transition">
                        Lihat Nota
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function renderStoreQrisPreview() {
            const savedQris = localStorage.getItem('rentalin_store_qris_image');
            const emptyBox  = document.getElementById('store-qris-empty');
            const prevBox   = document.getElementById('store-qris-preview-box');
            const prevImg   = document.getElementById('store-qris-preview-img');
            const resetBtn  = document.getElementById('btn-reset-qris');

            if (savedQris) {
                emptyBox.classList.add('hidden');
                prevBox.classList.remove('hidden');
                prevBox.classList.add('flex');
                prevImg.src = savedQris;
                resetBtn.classList.remove('hidden');
            } else {
                emptyBox.classList.remove('hidden');
                prevBox.classList.add('hidden');
                prevBox.classList.remove('flex');
                prevImg.src = '';
                resetBtn.classList.add('hidden');
            }
        }

        function handleStoreQrisUpload(input) {
            if (!input.files || !input.files[0]) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    localStorage.setItem('rentalin_store_qris_image', evt.target.result);
                } catch (err) {
                    alert('Ukuran gambar terlalu besar untuk penyimpanan browser. Gunakan gambar di bawah 2MB.');
                    return;
                }
                renderStoreQrisPreview();
            };
            reader.readAsDataURL(input.files[0]);
        }

        function resetStoreQris() {
            localStorage.removeItem('rentalin_store_qris_image');
            document.getElementById('store-qris-input').value = '';
            renderStoreQrisPreview();
        }

        renderStoreQrisPreview();
    </script>
@endsection