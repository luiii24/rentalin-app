@extends('layouts.admin')

@section('title', 'Transaksi & Scan QR')
@section('page-title', 'Transaksi & Scan QR')

@section('content')
    @php
        $sampleReceiptSvg = 'data:image/svg+xml;utf8,' . rawurlencode('
            <svg xmlns="http://www.w3.org/2000/svg" width="360" height="440" viewBox="0 0 360 440">
                <rect width="360" height="440" rx="20" fill="#F8FAFC"/>
                <rect x="20" y="20" width="320" height="400" rx="16" fill="#FFFFFF" stroke="#E2E8F0"/>
                <circle cx="180" cy="64" r="22" fill="#10B981" fill-opacity="0.15"/>
                <path d="M171 64l6 6 12-12" stroke="#10B981" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <text x="180" y="106" text-anchor="middle" font-family="sans-serif" font-size="14" font-weight="bold" fill="#0F172A">PEMBAYARAN QRIS BERHASIL</text>
                <text x="180" y="132" text-anchor="middle" font-family="sans-serif" font-size="22" font-weight="bold" fill="#FF385C">Rp 700.000</text>
                <line x1="40" y1="154" x2="320" y2="154" stroke="#E2E8F0" stroke-dasharray="5,5"/>
                <text x="44" y="182" font-family="sans-serif" font-size="11" fill="#64748B">Merchant</text>
                <text x="316" y="182" text-anchor="end" font-family="sans-serif" font-size="11" font-weight="bold" fill="#0F172A">RENTALIN SURABAYA</text>
                <text x="44" y="210" font-family="sans-serif" font-size="11" fill="#64748B">Pengirim</text>
                <text x="316" y="210" text-anchor="end" font-family="sans-serif" font-size="11" font-weight="bold" fill="#0F172A">BUDI SANTOSO</text>
                <text x="44" y="238" font-family="sans-serif" font-size="11" fill="#64748B">Referensi</text>
                <text x="316" y="238" text-anchor="end" font-family="monospace" font-size="11" font-weight="bold" fill="#0F172A">RNTL-DEMO01</text>
                <rect x="44" y="280" width="272" height="44" rx="10" fill="#F1F5F9"/>
                <text x="180" y="306" text-anchor="middle" font-family="monospace" font-size="11" fill="#475569">RRN: 2026112688492014</text>
            </svg>
        ');

        $dummyRentals = [
            (object) [
                'id'              => 1,
                'booking_code'    => 'RNTL-DEMO01',
                'customer_name'   => 'Budi Santoso',
                'customer_phone'  => '081234567890',
                'vehicle_name'    => 'Toyota Avanza Veloz',
                'license_plate'   => 'L 1428 AB',
                'pickup_date'     => date('Y-m-d') . ' 14:42:36',
                'duration_days'   => 2,
                'total_amount'    => 700000,
                'status'          => 'menunggu_konfirmasi',
                'payment_proof'   => $sampleReceiptSvg,
                'is_paid'         => false,
                'is_picked_up'    => false,
                'collateral_type' => null,
            ],
            (object) [
                'id'              => 2,
                'booking_code'    => 'RNTL-DEMO02',
                'customer_name'   => 'Siti Aminah',
                'customer_phone'  => '085712349876',
                'vehicle_name'    => 'Honda Brio RS',
                'license_plate'   => 'L 1890 XY',
                'pickup_date'     => date('Y-m-d') . ' 09:15:00',
                'duration_days'   => 1,
                'total_amount'    => 300000,
                'status'          => 'menunggu_pembayaran',
                'payment_proof'   => null,
                'is_paid'         => false,
                'is_picked_up'    => false,
                'collateral_type' => null,
            ],
        ];

        $rentalCollection = isset($rentals) && count($rentals) > 0 ? collect($rentals) : collect($dummyRentals);
        $normalizedRentals = $rentalCollection->map(function ($r) {
            $statusRaw = strtolower($r->status ?? 'menunggu_pembayaran');
            $isPaid = isset($r->is_paid)
                ? (bool) $r->is_paid
                : in_array($statusRaw, ['lunas', 'paid', 'siap_diambil', 'disewa', 'aktif', 'selesai']);
            $isPickedUp = isset($r->is_picked_up)
                ? (bool) $r->is_picked_up
                : in_array($statusRaw, ['disewa', 'aktif', 'selesai']);

            $proofRaw = $r->payment_proof ?? null;
            $proofUrl = null;
            if (!empty($proofRaw)) {
                $proofUrl = (str_starts_with($proofRaw, 'http') || str_starts_with($proofRaw, 'data:'))
                    ? $proofRaw
                    : asset('storage/' . $proofRaw);
            }

            return [
                'id'              => $r->id ?? 0,
                'booking_code'    => $r->booking_code ?? 'RNTL-DEMO01',
                'customer_name'   => $r->customer_name ?? 'Pelanggan',
                'customer_phone'  => $r->customer_phone ?? '-',
                'vehicle_name'    => $r->vehicle_name ?? ($r->vehicle->name ?? 'Unit Armada'),
                'license_plate'   => $r->license_plate ?? ($r->vehicle->license_plate ?? ''),
                'pickup_date'     => $r->pickup_date ?? date('Y-m-d H:i:s'),
                'duration_days'   => $r->duration_days ?? 1,
                'total_amount'    => (int) ($r->total_amount ?? ($r->total_price ?? 0)),
                'payment_proof'   => $proofUrl,
                'is_paid'         => $isPaid,
                'is_picked_up'    => $isPickedUp,
                'collateral_type' => $r->collateral_type ?? null,
            ];
        })->values();
    @endphp

    <!-- PANEL SCAN & CEK TRANSAKSI -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-7">
        <!-- KIRI: SCAN QR / CARI KODE BOOKING -->
        <div class="lg:col-span-5 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-brand-ink">Scan QR Nota Digital</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Pindai QR nota pelanggan atau ketik kode booking</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-brand-yellowSoft text-brand-ink flex items-center justify-center text-base flex-shrink-0">
                        <i class="bi bi-qr-code"></i>
                    </div>
                </div>

                <button type="button" onclick="toggleCameraPreview()"
                    class="w-full py-3 px-4 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold flex items-center justify-center gap-2 transition-all cursor-pointer mb-4">
                    <i class="bi bi-camera-video-fill"></i> <span id="cam-btn-label">Buka Kamera Scan</span>
                </button>

                <div id="qr-camera-box" class="hidden bg-brand-canvas rounded-2xl p-4 mb-4 text-center">
                    <video id="qr-video-stream" class="w-full h-40 object-cover rounded-xl bg-black mb-2" autoplay playsinline muted></video>
                    <p class="text-[11px] text-gray-500">Arahkan QR Nota Digital pelanggan ke kamera</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-brand-ink mb-2">Atau Masukkan Kode Booking</label>
                <form onsubmit="lookupBookingFromInput(event)" class="flex items-center gap-2">
                    <input type="text" id="scan-code-input" value="{{ $normalizedRentals[0]['booking_code'] ?? 'RNTL-DEMO01' }}"
                        placeholder="RNTL-XXXXXX"
                        class="flex-1 rounded-full px-4 py-3 font-mono text-xs font-extrabold uppercase">
                    <button type="submit"
                        class="px-5 py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        Cek
                    </button>
                </form>
            </div>
        </div>

        <!-- KANAN: DETAIL TRANSAKSI & 2 TOMBOL KONFIRMASI -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Kode Transaksi</p>
                        <h3 id="detail-booking-code" class="font-mono text-xl font-extrabold text-brand-pink mt-0.5">
                            RNTL-DEMO01
                        </h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <span id="detail-status-badge"
                            class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400">
                            Menunggu Konfirmasi
                        </span>
                        <a id="detail-invoice-link"
                           href="{{ url('/portal/invoice/RNTL-DEMO01') }}"
                           target="_blank"
                           title="Buka Nota Digital"
                           class="w-8 h-8 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink flex items-center justify-center transition">
                            <i class="bi bi-box-arrow-up-right text-xs"></i>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                    <div class="bg-brand-canvas rounded-2xl p-3.5">
                        <p class="text-[11px] font-semibold text-gray-400">Penyewa</p>
                        <p id="detail-customer-name" class="text-sm font-extrabold text-brand-ink mt-0.5">Budi Santoso</p>
                        <p id="detail-customer-phone" class="text-xs text-gray-500 mt-0.5">081234567890</p>
                    </div>

                    <div class="bg-brand-canvas rounded-2xl p-3.5">
                        <p class="text-[11px] font-semibold text-gray-400">Unit Armada</p>
                        <p id="detail-vehicle-name" class="text-sm font-extrabold text-brand-ink mt-0.5">Toyota Avanza Veloz</p>
                        <p id="detail-schedule-info" class="text-xs text-gray-500 mt-0.5">{{ date('Y-m-d') }} 14:42:36 (2 Hari)</p>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400">Total Tagihan</p>
                    <p id="detail-total-amount" class="text-xl font-extrabold text-brand-ink">Rp 700.000</p>
                    <p id="detail-collateral-info" class="hidden text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5"></p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" id="btn-confirm-payment" onclick="openConfirmPaymentModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        <i id="icon-confirm-payment" class="bi bi-check2-circle"></i>
                        <span id="label-confirm-payment">Konfirmasi Pembayaran</span>
                    </button>

                    <button type="button" id="btn-confirm-pickup" onclick="openConfirmPickupModal()" disabled
                        title="Konfirmasi pembayaran terlebih dahulu"
                        class="inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all">
                        <i id="icon-confirm-pickup" class="bi bi-lock-fill"></i>
                        <span id="label-confirm-pickup">Unit Sudah Diambil</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & DAFTAR TRANSAKSI -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
        <div>
            <h2 class="text-sm sm:text-base font-extrabold text-brand-ink">Daftar Transaksi Penyewaan</h2>
            <p class="text-xs text-gray-400">Total <strong class="text-brand-ink">{{ count($normalizedRentals) }}</strong> transaksi</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div class="w-48">
                <x-datepicker name="filter_date" id="tx-filter-date" :value="request('date', date('Y-m-d'))" />
            </div>
            <div class="w-48">
                <x-select
                    name="filter_status"
                    id="tx-filter-status"
                    :options="[
                        'all' => 'Semua Status',
                        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                        'siap_diambil' => 'Lunas / Siap Diambil',
                        'disewa' => 'Sedang Disewa',
                    ]"
                    selected="all"
                    onchange="filterTransactionsTable"
                />
            </div>
        </div>
    </div>

    <x-table id="transactions-table" :headers="[
        ['label' => 'Kode Booking', 'sortable' => true],
        ['label' => 'Penyewa', 'sortable' => true],
        ['label' => 'Armada', 'sortable' => true],
        ['label' => 'Jadwal & Durasi', 'sortable' => true],
        ['label' => 'Total Bayar', 'sortable' => true],
        ['label' => 'Status', 'sortable' => true],
        ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
    ]">
        @foreach($normalizedRentals as $item)
            @php
                $rowState = $item['is_picked_up']
                    ? 'disewa'
                    : ($item['is_paid']
                        ? 'siap_diambil'
                        : (!empty($item['payment_proof']) ? 'menunggu_konfirmasi' : 'menunggu_pembayaran'));
            @endphp
            <tr data-code="{{ $item['booking_code'] }}"
                data-state="{{ $rowState }}"
                onclick="selectTransactionByCode('{{ $item['booking_code'] }}')"
                class="rental-row cursor-pointer transition">
                <td class="py-3.5 pl-5 pr-2" onclick="event.stopPropagation()">
                    <input type="checkbox" class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>
                <td class="py-3.5 px-4 font-mono font-extrabold text-brand-pink" data-sort="{{ $item['booking_code'] }}">
                    {{ $item['booking_code'] }}
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $item['customer_name'] }}">
                    <p class="font-bold text-brand-ink">{{ $item['customer_name'] }}</p>
                    <p class="text-[11px] text-gray-400">{{ $item['customer_phone'] }}</p>
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $item['vehicle_name'] }}">
                    <p class="font-bold text-brand-ink">{{ $item['vehicle_name'] }}</p>
                    @if(!empty($item['license_plate']))
                        <span class="font-mono text-[10px] font-bold text-gray-400">{{ $item['license_plate'] }}</span>
                    @endif
                </td>
                <td class="py-3.5 px-4 text-xs text-gray-500" data-sort="{{ $item['pickup_date'] }}">
                    <p class="font-semibold text-brand-ink">{{ \Carbon\Carbon::parse($item['pickup_date'])->translatedFormat('d M Y') }}</p>
                    <p class="text-[11px] text-gray-400">{{ $item['duration_days'] }} Hari</p>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $item['total_amount'] }}">
                    Rp {{ number_format($item['total_amount'], 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $rowState }}">
                    <span class="row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold
                        {{ $rowState === 'disewa' || $rowState === 'siap_diambil'
                            ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                            : ($rowState === 'menunggu_konfirmasi'
                                ? 'bg-blue-500/15 text-blue-600 dark:text-blue-400'
                                : 'bg-amber-500/15 text-amber-600 dark:text-amber-400') }}">
                        {{ $rowState === 'disewa'
                            ? 'Sedang Disewa'
                            : ($rowState === 'siap_diambil'
                                ? 'Siap Diambil'
                                : ($rowState === 'menunggu_konfirmasi' ? 'Menunggu Konfirmasi' : 'Menunggu Pembayaran')) }}
                    </span>
                </td>
                <td class="py-3.5 px-4 text-right" onclick="event.stopPropagation()">
                    <button type="button" onclick="selectTransactionByCode('{{ $item['booking_code'] }}')"
                        class="px-3.5 py-1.5 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-[11px] font-extrabold transition cursor-pointer">
                        Pilih
                    </button>
                </td>
            </tr>
        @endforeach
    </x-table>

    <!-- POP-UP 1: VERIFIKASI PEMBAYARAN -->
    <div id="confirmPaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-base">
                        <i class="bi bi-shield-check"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-brand-ink">Verifikasi Pembayaran</h3>
                        <p class="text-[11px] text-gray-400">Periksa bukti bayar &amp; pastikan dana sudah masuk</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('confirmPaymentModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="bg-brand-canvas rounded-2xl p-4 mb-4 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Kode Booking</span>
                    <span id="pay-modal-code" class="font-mono font-extrabold text-brand-pink">RNTL-DEMO01</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Penyewa</span>
                    <span id="pay-modal-customer" class="font-bold text-brand-ink">Budi Santoso</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Status Pembayaran</span>
                    <span id="pay-modal-status-pill" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400">
                        Menunggu Konfirmasi
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nominal Pembayaran</span>
                    <span id="pay-modal-amount" class="font-extrabold text-brand-ink">Rp 700.000</span>
                </div>
            </div>

            <div class="bg-brand-canvas rounded-2xl p-3.5 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-extrabold text-brand-ink">Bukti Pembayaran Terunggah</span>
                    <button type="button" id="pay-modal-zoom-btn" onclick="openProofLightbox()"
                        class="text-[11px] font-extrabold text-brand-pink hover:underline cursor-pointer">
                        Perbesar Foto
                    </button>
                </div>

                <div id="pay-modal-proof-wrap" class="bg-white rounded-xl p-2 flex items-center justify-center">
                    <img id="pay-modal-proof-img" src="" alt="Bukti Pembayaran" onclick="openProofLightbox()"
                         class="max-h-52 w-auto rounded-lg object-contain cursor-zoom-in">
                </div>

                <div id="pay-modal-no-proof" class="hidden bg-white rounded-xl p-4 text-center">
                    <p class="text-xs font-bold text-amber-600 dark:text-amber-400">Status Masih Menunggu Pembayaran</p>
                    <p class="text-[11px] text-gray-500 mt-1">Penyewa belum mengunggah foto bukti pembayaran.</p>
                </div>
            </div>

            <label id="legal-payment-label" class="flex items-start gap-3 bg-brand-canvas rounded-2xl p-3.5 mb-5 cursor-pointer select-none">
                <input type="checkbox" id="legal-payment-checkbox" onchange="toggleLegalPaymentButton()"
                    class="mt-0.5 w-4 h-4 rounded accent-brand-pink cursor-pointer flex-shrink-0">
                <span class="text-[11px] text-gray-600 leading-relaxed">
                    Saya menyatakan bahwa <strong>pembayaran telah diterima dan dilakukan secara sah</strong> sesuai aturan dan ketentuan transaksi yang berlaku di Rentalin.
                </span>
            </label>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeModal('confirmPaymentModal')"
                    class="flex-1 py-3 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer">
                    Tidak
                </button>
                <button type="button" id="btn-submit-legal-payment" onclick="submitConfirmPayment()" disabled
                    class="flex-[2] py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all">
                    Ya, Konfirmasi Sah
                </button>
            </div>
        </div>
    </div>

    <div id="proofLightboxModal" onclick="closeModal('proofLightboxModal')" class="fixed inset-0 bg-black/80 backdrop-blur-xs z-[70] hidden flex items-center justify-center p-4 cursor-zoom-out">
        <img id="proof-lightbox-img" src="" alt="Bukti Pembayaran Penuh" class="max-w-full max-h-[88vh] rounded-2xl shadow-2xl object-contain bg-white">
    </div>

    <!-- POP-UP 2: KONFIRMASI PENGAMBILAN & UPLOAD JAMINAN -->
    <div id="confirmPickupModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-brand-yellowSoft text-brand-ink flex items-center justify-center text-base">
                        <i class="bi bi-person-vcard-fill"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-brand-ink">Konfirmasi Pengambilan Unit</h3>
                        <p class="text-[11px] text-gray-400">Unggah foto identitas / dokumen jaminan penyewa</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('confirmPickupModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form id="confirmPickupForm" onsubmit="submitConfirmPickup(event)" class="space-y-4">
                <div class="bg-brand-canvas rounded-2xl p-3.5 text-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-gray-400">Penyerahan Unit</p>
                        <p id="pickup-modal-vehicle" class="font-extrabold text-brand-ink mt-0.5">Toyota Avanza Veloz</p>
                    </div>
                    <span id="pickup-modal-code" class="font-mono font-extrabold text-brand-pink">RNTL-DEMO01</span>
                </div>

                <div>
                    <x-select
                        name="collateral_type"
                        id="pickup-collateral-type"
                        label="Jenis Bukti Jaminan"
                        :options="[
                            'KTP Asli' => 'KTP Asli',
                            'SIM A / SIM C' => 'SIM A / SIM C',
                            'STNK & Motor Jaminan' => 'STNK & Motor Jaminan',
                            'Paspor / Identitas Lain' => 'Paspor / Identitas Lain',
                        ]"
                        selected="KTP Asli"
                    />
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-brand-ink mb-1.5">
                        Upload File Bukti Jaminan (KTP / SIM / Dokumen)
                    </label>
                    <input type="file" id="collateral-file-input" name="collateral_file" accept="image/*,.pdf" required class="hidden" onchange="previewCollateralFile(this)">

                    <div onclick="document.getElementById('collateral-file-input').click()"
                         class="bg-brand-canvas hover:opacity-95 rounded-2xl p-5 text-center cursor-pointer transition">
                        <div id="collateral-placeholder" class="flex flex-col items-center justify-center">
                            <div class="w-10 h-10 rounded-2xl bg-white text-brand-pink flex items-center justify-center text-base mb-2">
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                            </div>
                            <p class="text-xs font-bold text-brand-ink">Klik untuk unggah foto jaminan</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Foto KTP, SIM, atau serah terima kunci</p>
                        </div>

                        <div id="collateral-preview-wrap" class="hidden flex-col items-center gap-2">
                            <img id="collateral-preview-img" src="" alt="Preview Jaminan" class="max-h-40 rounded-xl object-contain bg-white">
                            <span id="collateral-file-name" class="text-[11px] font-bold text-brand-pink"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 pt-1">
                    <button type="button" onclick="closeModal('confirmPickupModal')"
                        class="flex-1 py-3 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        Konfirmasi Unit Diambil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dashboardRentals = @json($normalizedRentals);
        dashboardRentals.forEach(item => {
            const localProof = localStorage.getItem(`rentalin_proof_${item.booking_code}`);
            if (localProof) item.payment_proof = localProof;
        });

        let selectedRental = dashboardRentals[0] || null;
        let activeVideoStream = null;

        function formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }

        function getRentalState(rental) {
            if (rental.is_picked_up) return 'disewa';
            if (rental.is_paid) return 'siap_diambil';
            if (rental.payment_proof) return 'menunggu_konfirmasi';
            return 'menunggu_pembayaran';
        }

        function renderSelectedRentalPanel() {
            if (!selectedRental) return;
            const state = getRentalState(selectedRental);

            document.getElementById('scan-code-input').value = selectedRental.booking_code;
            document.getElementById('detail-booking-code').innerText = selectedRental.booking_code;
            document.getElementById('detail-customer-name').innerText = selectedRental.customer_name;
            document.getElementById('detail-customer-phone').innerText = selectedRental.customer_phone;
            document.getElementById('detail-vehicle-name').innerText = selectedRental.vehicle_name + (selectedRental.license_plate ? ` (${selectedRental.license_plate})` : '');
            document.getElementById('detail-schedule-info').innerText = `${selectedRental.pickup_date} (${selectedRental.duration_days} Hari)`;
            document.getElementById('detail-total-amount').innerText = formatRupiah(selectedRental.total_amount);
            document.getElementById('detail-invoice-link').href = `/portal/invoice/${encodeURIComponent(selectedRental.booking_code)}`;

            const statusBadge = document.getElementById('detail-status-badge');
            const btnPay      = document.getElementById('btn-confirm-payment');
            const iconPay     = document.getElementById('icon-confirm-payment');
            const labelPay    = document.getElementById('label-confirm-payment');
            const btnPickup   = document.getElementById('btn-confirm-pickup');
            const iconPickup  = document.getElementById('icon-confirm-pickup');
            const labelPickup = document.getElementById('label-confirm-pickup');
            const colInfo     = document.getElementById('detail-collateral-info');

            if (selectedRental.collateral_type) {
                colInfo.innerText = `Jaminan tersimpan: ${selectedRental.collateral_type}`;
                colInfo.classList.remove('hidden');
            } else {
                colInfo.classList.add('hidden');
            }

            if (selectedRental.is_paid) {
                btnPay.disabled = true;
                btnPay.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-extrabold cursor-default';
                iconPay.className = 'bi bi-check-circle-fill';
                labelPay.innerText = 'Pembayaran Sah';
            } else if (!selectedRental.payment_proof) {
                btnPay.disabled = true;
                btnPay.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
                iconPay.className = 'bi bi-lock-fill';
                labelPay.innerText = 'Konfirmasi Pembayaran';
            } else {
                btnPay.disabled = false;
                btnPay.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer';
                iconPay.className = 'bi bi-check2-circle';
                labelPay.innerText = 'Konfirmasi Pembayaran';
            }

            if (!selectedRental.is_paid) {
                btnPickup.disabled = true;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
                iconPickup.className = 'bi bi-lock-fill';
                labelPickup.innerText = 'Unit Sudah Diambil';
            } else if (selectedRental.is_picked_up) {
                btnPickup.disabled = true;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-extrabold cursor-default';
                iconPickup.className = 'bi bi-car-front-fill';
                labelPickup.innerText = 'Unit Sudah Diambil';
            } else {
                btnPickup.disabled = false;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold transition-all cursor-pointer';
                iconPickup.className = 'bi bi-key-fill';
                labelPickup.innerText = 'Unit Sudah Diambil';
            }

            if (state === 'disewa') {
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                statusBadge.innerText = 'Sedang Disewa';
            } else if (state === 'siap_diambil') {
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                statusBadge.innerText = 'Lunas • Siap Diambil';
            } else if (state === 'menunggu_konfirmasi') {
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400';
                statusBadge.innerText = 'Menunggu Konfirmasi';
            } else {
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400';
                statusBadge.innerText = 'Menunggu Pembayaran';
            }

            syncTableRowUI(selectedRental);
        }

        function syncTableRowUI(rental) {
            const row = document.querySelector(`.rental-row[data-code="${rental.booking_code}"]`);
            if (!row) return;
            const badge = row.querySelector('.row-status-badge');
            const state = getRentalState(rental);
            row.dataset.state = state;

            if (badge) {
                if (state === 'disewa') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                    badge.innerText = 'Sedang Disewa';
                } else if (state === 'siap_diambil') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                    badge.innerText = 'Siap Diambil';
                } else if (state === 'menunggu_konfirmasi') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-blue-500/15 text-blue-600 dark:text-blue-400';
                    badge.innerText = 'Menunggu Konfirmasi';
                } else {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-500/15 text-amber-600 dark:text-amber-400';
                    badge.innerText = 'Menunggu Pembayaran';
                }
            }
        }

        function selectTransactionByCode(code) {
            const clean = String(code || '').trim().toUpperCase();
            const found = dashboardRentals.find(r => r.booking_code.toUpperCase() === clean);
            if (!found) {
                alert(`Kode booking "${clean}" tidak ditemukan.`);
                return;
            }
            selectedRental = found;
            renderSelectedRentalPanel();
        }

        function lookupBookingFromInput(e) {
            e.preventDefault();
            selectTransactionByCode(document.getElementById('scan-code-input').value);
        }

        function openConfirmPaymentModal() {
            if (!selectedRental || selectedRental.is_paid || !selectedRental.payment_proof) return;

            document.getElementById('pay-modal-code').innerText = selectedRental.booking_code;
            document.getElementById('pay-modal-customer').innerText = selectedRental.customer_name;
            document.getElementById('pay-modal-amount').innerText = formatRupiah(selectedRental.total_amount);

            const statusPill = document.getElementById('pay-modal-status-pill');
            const proofWrap  = document.getElementById('pay-modal-proof-wrap');
            const proofImg   = document.getElementById('pay-modal-proof-img');
            const noProof    = document.getElementById('pay-modal-no-proof');
            const zoomBtn    = document.getElementById('pay-modal-zoom-btn');
            const cb         = document.getElementById('legal-payment-checkbox');

            cb.checked = false;

            if (selectedRental.payment_proof) {
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400';
                statusPill.innerText = 'Menunggu Konfirmasi';
                proofImg.src = selectedRental.payment_proof;
                proofWrap.classList.remove('hidden');
                noProof.classList.add('hidden');
                zoomBtn.classList.remove('hidden');
                cb.disabled = false;
            } else {
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400';
                statusPill.innerText = 'Menunggu Pembayaran';
                proofWrap.classList.add('hidden');
                noProof.classList.remove('hidden');
                zoomBtn.classList.add('hidden');
                cb.disabled = true;
            }

            toggleLegalPaymentButton();
            document.getElementById('confirmPaymentModal').classList.remove('hidden');
        }

        function openProofLightbox() {
            if (!selectedRental || !selectedRental.payment_proof) return;
            document.getElementById('proof-lightbox-img').src = selectedRental.payment_proof;
            document.getElementById('proofLightboxModal').classList.remove('hidden');
        }

        function toggleLegalPaymentButton() {
            const cb = document.getElementById('legal-payment-checkbox');
            const canConfirm = Boolean(selectedRental && selectedRental.payment_proof && !cb.disabled && cb.checked);
            const btn = document.getElementById('btn-submit-legal-payment');
            btn.disabled = !canConfirm;
            btn.className = canConfirm
                ? 'flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer'
                : 'flex-[2] py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
        }

        function submitConfirmPayment() {
            const cb = document.getElementById('legal-payment-checkbox');
            if (!selectedRental || !selectedRental.payment_proof || !cb.checked) return;
            selectedRental.is_paid = true;
            closeModal('confirmPaymentModal');
            renderSelectedRentalPanel();
        }

        function openConfirmPickupModal() {
            if (!selectedRental || !selectedRental.is_paid || selectedRental.is_picked_up) return;
            document.getElementById('pickup-modal-code').innerText = selectedRental.booking_code;
            document.getElementById('pickup-modal-vehicle').innerText = selectedRental.vehicle_name;
            document.getElementById('confirmPickupForm').reset();
            document.getElementById('collateral-placeholder').classList.remove('hidden');
            document.getElementById('collateral-placeholder').classList.add('flex');
            document.getElementById('collateral-preview-wrap').classList.add('hidden');
            document.getElementById('confirmPickupModal').classList.remove('hidden');
        }

        function previewCollateralFile(input) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            document.getElementById('collateral-placeholder').classList.add('hidden');
            const wrap = document.getElementById('collateral-preview-wrap');
            wrap.classList.remove('hidden');
            wrap.classList.add('flex');
            const img = document.getElementById('collateral-preview-img');
            if (file.type.startsWith('image/')) {
                img.src = URL.createObjectURL(file);
                img.classList.remove('hidden');
            } else {
                img.classList.add('hidden');
            }
            document.getElementById('collateral-file-name').innerText = file.name;
        }

        function submitConfirmPickup(e) {
            e.preventDefault();
            if (!selectedRental || !selectedRental.is_paid) return;
            selectedRental.is_picked_up = true;
            selectedRental.collateral_type = document.getElementById('pickup-collateral-type')?.value || 'KTP Asli';
            closeModal('confirmPickupModal');
            renderSelectedRentalPanel();
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        function filterTransactionsTable(statusVal) {
            document.querySelectorAll('.rental-row').forEach(row => {
                const match = statusVal === 'all' || row.dataset.state === statusVal;
                row.classList.toggle('hidden', !match);
            });
        }

        async function toggleCameraPreview() {
            const box = document.getElementById('qr-camera-box');
            const video = document.getElementById('qr-video-stream');
            const label = document.getElementById('cam-btn-label');

            if (!box.classList.contains('hidden')) {
                box.classList.add('hidden');
                label.innerText = 'Buka Kamera Scan';
                if (activeVideoStream) {
                    activeVideoStream.getTracks().forEach(t => t.stop());
                    activeVideoStream = null;
                }
                return;
            }

            try {
                activeVideoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = activeVideoStream;
                box.classList.remove('hidden');
                label.innerText = 'Tutup Kamera Scan';
            } catch (err) {
                alert('Kamera tidak tersedia atau izin ditolak browser.');
            }
        }

        renderSelectedRentalPanel();
    </script>
@endsection