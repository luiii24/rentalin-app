@extends('layouts.admin')

@section('title', 'Ringkasan Dashboard')
@section('page-title', 'Ringkasan Dashboard')

@section('content')
    @php
        $totalRevenue     = $stats['revenue'] ?? 0;
        $activeUnits      = $stats['rented'] ?? 0;
        $pendingCount     = $stats['pending'] ?? 0;
        $readyUnits       = $stats['available'] ?? 0;
        $maintenanceUnits = $stats['maintenance'] ?? 0;
        $totalFleet       = $stats['total_fleet'] ?? ($activeUnits + $readyUnits + $maintenanceUnits);

        $defaultDummyQris = 'data:image/svg+xml;utf8,' . rawurlencode('
            <svg xmlns="http://www.w3.org/2000/svg" width="320" height="320" viewBox="0 0 320 320">
                <rect width="320" height="320" rx="20" fill="#FFFFFF"/>
                <rect x="8" y="8" width="304" height="304" rx="16" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="2"/>
                <rect x="24" y="20" width="54" height="20" rx="5" fill="#FF385C"/>
                <text x="51" y="34" text-anchor="middle" font-family="sans-serif" font-size="11" font-weight="bold" fill="#FFFFFF">QRIS</text>
                <text x="296" y="34" text-anchor="end" font-family="sans-serif" font-size="9" font-weight="bold" fill="#64748B">GPN • BI STANDARD</text>
                <text x="160" y="60" text-anchor="middle" font-family="sans-serif" font-size="13" font-weight="bold" fill="#0F172A">RENTALIN SURABAYA</text>
                <text x="160" y="75" text-anchor="middle" font-family="monospace" font-size="9.5" fill="#64748B">NMID : ID20261127889901</text>
                <g transform="translate(75, 88)" fill="#0F172A">
                    <path d="M0 0h42v42H0V0zm6 6v30h30V6H6zm6 6h18v18H12V12z"/>
                    <path d="M128 0h42v42h-42V0zm6 6v30h30V6h-30zm6 6h18v18h-18V12z"/>
                    <path d="M0 128h42v42H0v-42zm6 6v30h30v-30H6zm6 6h18v18H12v-18z"/>
                    <rect x="52" y="4" width="8" height="8"/><rect x="68" y="4" width="16" height="8"/><rect x="96" y="4" width="8" height="16"/><rect x="112" y="12" width="8" height="8"/>
                    <rect x="52" y="20" width="8" height="16"/><rect x="76" y="24" width="12" height="8"/><rect x="108" y="28" width="12" height="8"/>
                    <rect x="8" y="52" width="16" height="8"/><rect x="32" y="52" width="8" height="16"/><rect x="52" y="52" width="12" height="8"/><rect x="104" y="52" width="16" height="8"/><rect x="132" y="52" width="8" height="16"/><rect x="152" y="52" width="14" height="8"/>
                    <rect x="4" y="72" width="8" height="16"/><rect x="20" y="76" width="16" height="8"/><rect x="48" y="72" width="12" height="12"/><rect x="112" y="72" width="12" height="12"/><rect x="136" y="76" width="16" height="8"/><rect x="160" y="72" width="8" height="16"/>
                    <rect x="8" y="96" width="12" height="8"/><rect x="28" y="96" width="16" height="8"/><rect x="52" y="96" width="8" height="16"/><rect x="108" y="96" width="8" height="16"/><rect x="128" y="96" width="16" height="8"/><rect x="154" y="96" width="12" height="8"/>
                    <rect x="52" y="124" width="16" height="8"/><rect x="76" y="120" width="8" height="16"/><rect x="96" y="124" width="16" height="8"/><rect x="124" y="124" width="24" height="24" fill="none" stroke="#0F172A" stroke-width="5"/><rect x="133" y="133" width="6" height="6"/><rect x="158" y="124" width="10" height="12"/>
                    <rect x="52" y="144" width="8" height="12"/><rect x="68" y="148" width="16" height="8"/><rect x="92" y="144" width="12" height="16"/><rect x="112" y="156" width="16" height="10"/><rect x="138" y="154" width="12" height="12"/><rect x="158" y="146" width="10" height="20"/>
                    <rect x="67" y="67" width="36" height="36" rx="8" fill="#F3C64F"/>
                    <text x="85" y="91" text-anchor="middle" font-family="sans-serif" font-size="18" font-weight="bold" fill="#1E1E1E">R</text>
                </g>
                <text x="160" y="280" text-anchor="middle" font-family="sans-serif" font-size="9" font-weight="bold" fill="#475569">SATU QRIS UNTUK SEMUA PEMBAYARAN</text>
                <text x="160" y="294" text-anchor="middle" font-family="sans-serif" font-size="8" fill="#94A3B8">GoPay • OVO • DANA • ShopeePay • Mobile Banking</text>
            </svg>
        ');
        $activeQrisSrc = !empty($storeQrisUrl) ? $storeQrisUrl : $defaultDummyQris;
    @endphp

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

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
        <!-- KIRI (7 KOLOM) -->
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
                        <p class="text-xl font-extrabold text-brand-ink">{{ $maintenanceUnits }} Unit</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Servis rutin berkala</p>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.transactions') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition">
                    <i class="bi bi-qr-code-scan"></i> Buka Scan QR &amp; Transaksi
                </a>

                <a href="{{ route('admin.vehicles.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition">
                    <i class="bi bi-car-front"></i> Kelola Armada
                </a>
            </div>
        </div>

        <!-- KANAN (5 KOLOM): UPLOAD FOTO QRIS PEMBAYARAN KE SERVER -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-brand-ink">Foto QRIS Pembayaran</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Gambar QRIS ini otomatis tampil di semua Nota Digital penyewa</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-[10px] font-extrabold">
                        {{ !empty($storeQrisUrl) ? 'QRIS Custom Aktif' : 'Aktif (Default)' }}
                    </span>
                </div>

                <form id="store-qris-form" action="{{ route('admin.qris.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" id="store-qris-input" name="qris_image" accept="image/*" class="hidden"
                           onchange="if(this.files && this.files[0]) document.getElementById('store-qris-form').submit()">
                </form>

                <div onclick="document.getElementById('store-qris-input').click()"
                     class="bg-brand-canvas hover:opacity-95 rounded-2xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition min-h-[195px]">
                    <div class="flex flex-col items-center gap-2">
                        <img src="{{ $activeQrisSrc }}" alt="QRIS Toko" class="w-40 h-40 object-contain rounded-2xl bg-white p-2 shadow-2xs border border-gray-200/70">
                        <span class="text-[11px] font-bold text-brand-pink">Klik gambar untuk mengganti foto QRIS</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-2">
                <button type="button" onclick="document.getElementById('store-qris-input').click()"
                    class="flex-1 py-2.5 px-4 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold transition cursor-pointer">
                    <i class="bi bi-upload mr-1"></i> Upload / Ganti QRIS
                </button>

                @if(!empty($storeQrisUrl))
                    <form action="{{ route('admin.qris.reset') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="py-2.5 px-3.5 rounded-full bg-brand-canvas hover:opacity-85 text-gray-500 text-xs font-extrabold transition cursor-pointer">
                            Reset Default
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- RINGKASAN AKTIVITAS TERBARU -->
    <div class="bg-white rounded-3xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-brand-ink">Aktivitas Penyewaan Terbaru</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pantauan cepat pesanan masuk terbaru</p>
            </div>
            <a href="{{ route('admin.transactions') }}"
               class="text-xs font-extrabold text-brand-pink hover:underline">
                Lihat Semua &amp; Scan QR →
            </a>
        </div>

        <div class="space-y-2.5">
            @forelse($recentRentals as $idx => $item)
                @php
                    $isPaid = ($item->payment?->payment_status === 'paid');
                    $hasProof = !empty($item->payment?->proof_image);
                @endphp
                <div class="bg-brand-canvas rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white text-brand-pink flex items-center justify-center font-mono text-xs font-extrabold">
                            #{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-extrabold text-brand-pink">{{ $item->booking_code }}</span>
                                @if($isPaid)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                        Lunas
                                    </span>
                                @elseif($hasProof)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                        Menunggu Konfirmasi
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        Menunggu Pembayaran
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs font-bold text-brand-ink mt-0.5">
                                {{ $item->customer_name }} • {{ $item->vehicle->name ?? 'Unit' }} ({{ $item->duration_days }} Hari)
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4">
                        <span class="text-sm font-extrabold text-brand-ink">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                        <a href="{{ route('admin.transactions', ['code' => $item->booking_code]) }}"
                           class="px-3.5 py-1.5 rounded-full bg-white text-brand-ink text-[11px] font-extrabold hover:opacity-85 transition">
                            Proses
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 text-center py-6">Belum ada transaksi penyewaan masuk.</p>
            @endforelse
        </div>
    </div>
@endsection