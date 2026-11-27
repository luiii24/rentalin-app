@extends('layouts.admin')

@section('title', 'Transaksi & Scan QR')
@section('page-title', 'Transaksi & Scan QR')

@section('content')
    @php
        $rentalCollection = isset($rentals) ? collect($rentals) : collect();
        $normalizedRentals = $rentalCollection->map(function ($r) {
            $statusRaw   = strtolower($r->status ?? 'pending');
            $isCompleted = $statusRaw === 'completed';
            $isPickedUp  = in_array($statusRaw, ['active', 'completed', 'disewa', 'aktif', 'selesai']);
            $isPaid      = ($r->payment?->payment_status === 'paid') || $isPickedUp;

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
                'vehicle_name'    => $r->vehicle?->name ?? 'Unit Armada',
                'license_plate'   => $r->vehicle?->license_plate ?? '',
                'pickup_date'     => isset($r->pickup_date) ? \Carbon\Carbon::parse($r->pickup_date)->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'duration_days'   => (int) ($r->duration_days ?? 1),
                'total_amount'    => (int) ($r->total_price ?? 0),
                'payment_proof'   => $proofUrl,
                'is_paid'         => $isPaid,
                'is_picked_up'    => $isPickedUp,
                'is_completed'    => $isCompleted,
                'collateral_type' => $r->collateral_type ?? null,
            ];
        })->values();

        $initialCode = !empty($selectedCode)
            ? $selectedCode
            : ($normalizedRentals[0]['booking_code'] ?? '');
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
                    <div id="qr-reader-viewport" class="w-full overflow-hidden rounded-xl bg-black mb-2"></div>
                    <p class="text-[11px] text-gray-500">Arahkan QR / Barcode Nota Digital pelanggan ke kamera</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-extrabold text-brand-ink mb-2">Atau Masukkan Kode Booking</label>
                <form onsubmit="lookupBookingFromInput(event)" class="flex items-center gap-2">
                    <input type="text" id="scan-code-input" value="{{ $initialCode }}"
                        placeholder="RNTL-XXXXXX"
                        class="flex-1 rounded-full px-4 py-3 font-mono text-xs font-extrabold uppercase">
                    <button type="submit"
                        class="px-5 py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        Cek
                    </button>
                </form>
            </div>
        </div>

        <!-- KANAN: DETAIL TRANSAKSI & TOMBOL KONFIRMASI -->
        <div class="lg:col-span-7 bg-white rounded-3xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Kode Transaksi</p>
                        <h3 id="detail-booking-code" class="font-mono text-xl font-extrabold text-brand-pink mt-0.5">
                            {{ $initialCode ?: '-' }}
                        </h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <span id="detail-status-badge"
                            class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            Menunggu Pembayaran
                        </span>
                        <a id="detail-invoice-link"
                           href="{{ $initialCode ? route('admin.invoice', $initialCode) : '#' }}"
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
                        <p id="detail-customer-name" class="text-sm font-extrabold text-brand-ink mt-0.5">-</p>
                        <p id="detail-customer-phone" class="text-xs text-gray-500 mt-0.5">-</p>
                    </div>

                    <div class="bg-brand-canvas rounded-2xl p-3.5">
                        <p class="text-[11px] font-semibold text-gray-400">Unit Armada</p>
                        <p id="detail-vehicle-name" class="text-sm font-extrabold text-brand-ink mt-0.5">-</p>
                        <p id="detail-schedule-info" class="text-xs text-gray-500 mt-0.5">-</p>
                    </div>
                </div>
            </div>

            <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400">Total Tagihan</p>
                    <p id="detail-total-amount" class="text-xl font-extrabold text-brand-ink">Rp 0</p>
                    <p id="detail-collateral-info" class="hidden text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mt-0.5"></p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
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

                    <button type="button" id="btn-confirm-return" onclick="submitConfirmReturn()"
                        class="hidden inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        <i class="bi bi-arrow-return-left"></i>
                        <span>Selesaikan &amp; Kembali</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER, GLOBAL SEARCH BAR & FILTER -->
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-sm sm:text-base font-extrabold text-brand-ink">Daftar Transaksi Penyewaan</h2>
            <p class="text-xs text-gray-400">
                Menampilkan <strong id="tx-visible-count" class="text-brand-ink">{{ count($normalizedRentals) }}</strong> dari <strong class="text-brand-ink">{{ count($normalizedRentals) }}</strong> transaksi
            </p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <x-search-bar
                id="tx-search-input"
                placeholder="Cari kode, penyewa, unit, plat..."
                oninput="filterTransactionsTable"
                widthClass="w-full sm:w-72"
            />

            <div class="flex items-center gap-2.5">
                <div class="w-44">
                    <x-datepicker name="filter_date" id="tx-filter-date" :value="request('date', date('Y-m-d'))" />
                </div>
                <div class="w-44">
                    <x-select
                        name="filter_status"
                        id="tx-filter-status"
                        :options="[
                            'all' => 'Semua Status',
                            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                            'menunggu_pembayaran' => 'Menunggu Pembayaran',
                            'siap_diambil' => 'Lunas / Siap Diambil',
                            'disewa' => 'Sedang Disewa',
                            'selesai' => 'Selesai',
                        ]"
                        selected="all"
                        onchange="filterTransactionsTable"
                    />
                </div>
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
                $rowState = $item['is_completed']
                    ? 'selesai'
                    : ($item['is_picked_up']
                        ? 'disewa'
                        : ($item['is_paid']
                            ? 'siap_diambil'
                            : (!empty($item['payment_proof']) ? 'menunggu_konfirmasi' : 'menunggu_pembayaran')));
                $searchHaystack = strtolower(
                    $item['booking_code'] . ' ' .
                    $item['customer_name'] . ' ' .
                    $item['customer_phone'] . ' ' .
                    $item['vehicle_name'] . ' ' .
                    $item['license_plate']
                );
            @endphp
            <tr data-code="{{ $item['booking_code'] }}"
                data-state="{{ $rowState }}"
                data-search="{{ $searchHaystack }}"
                onclick="selectTransactionByCode('{{ $item['booking_code'] }}')"
                class="rental-row cursor-pointer hover:bg-brand-canvas/40 transition">
                <td class="py-3.5 pl-5 pr-2" onclick="event.stopPropagation()">
                    <input type="checkbox" class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>
                <td class="py-3.5 px-4 font-mono font-extrabold text-brand-pink" data-sort="{{ $item['booking_code'] }}">
                    {{ $item['booking_code'] }}
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $item['customer_name'] }}">
                    <p class="font-bold text-brand-ink">{{ $item['customer_name'] }}</p>
                    <p class="text-[11px] text-gray-400" data-format-phone="{{ $item['customer_phone'] }}">{{ $item['customer_phone'] }}</p>
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $item['vehicle_name'] }}">
                    <p class="font-bold text-brand-ink">{{ $item['vehicle_name'] }}</p>
                    @if(!empty($item['license_plate']))
                        <x-badge variant="plate" class="mt-1 !text-[10px] !py-0.5 !px-2">{{ $item['license_plate'] }}</x-badge>
                    @endif
                </td>
                <td class="py-3.5 px-4 text-xs text-gray-500" data-sort="{{ $item['pickup_date'] }}">
                    <p class="font-semibold text-brand-ink" data-format-date="{{ $item['pickup_date'] }}" data-date-mode="short">
                        {{ $item['pickup_date'] }}
                    </p>
                    <p class="text-[11px] text-gray-400">{{ $item['duration_days'] }} Hari</p>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $item['total_amount'] }}" data-format-rupiah="{{ $item['total_amount'] }}">
                    Rp {{ number_format($item['total_amount'], 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $rowState }}">
                    @if($rowState === 'selesai')
                        <x-badge variant="neutral" :dot="true" class="row-status-badge">Selesai</x-badge>
                    @elseif($rowState === 'disewa')
                        <x-badge variant="success" :dot="true" class="row-status-badge">Sedang Disewa</x-badge>
                    @elseif($rowState === 'siap_diambil')
                        <x-badge variant="success" :dot="true" class="row-status-badge">Siap Diambil</x-badge>
                    @elseif($rowState === 'menunggu_konfirmasi')
                        <x-badge variant="info" :dot="true" class="row-status-badge">Menunggu Konfirmasi</x-badge>
                    @else
                        <x-badge variant="warning" :dot="true" class="row-status-badge">Menunggu Pembayaran</x-badge>
                    @endif
                </td>
                <td class="py-3.5 px-4 text-right" onclick="event.stopPropagation()">
                    <button type="button" onclick="selectTransactionByCode('{{ $item['booking_code'] }}')"
                        class="px-3.5 py-1.5 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-[11px] font-extrabold transition cursor-pointer">
                        Pilih
                    </button>
                </td>
            </tr>
        @endforeach
        <tr id="tx-empty-row" data-empty-row="true" class="{{ count($normalizedRentals) === 0 ? '' : 'hidden' }}">
            <td colspan="8" class="py-10 text-center text-xs text-gray-400">
                Tidak ada transaksi yang cocok dengan pencarian atau filter status.
            </td>
        </tr>
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
                <button type="button" onclick="RentalinUI.closeModal('confirmPaymentModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="bg-brand-canvas rounded-2xl p-4 mb-4 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-500">Kode Booking</span>
                    <span id="pay-modal-code" class="font-mono font-extrabold text-brand-pink">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Penyewa</span>
                    <span id="pay-modal-customer" class="font-bold text-brand-ink">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Status Pembayaran</span>
                    <span id="pay-modal-status-pill" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400">
                        Menunggu Konfirmasi
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Nominal Pembayaran</span>
                    <span id="pay-modal-amount" class="font-extrabold text-brand-ink">Rp 0</span>
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
                <button type="button" onclick="RentalinUI.closeModal('confirmPaymentModal')"
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

    <div id="proofLightboxModal" onclick="RentalinUI.closeModal('proofLightboxModal')" class="fixed inset-0 bg-black/80 backdrop-blur-xs z-[70] hidden flex items-center justify-center p-4 cursor-zoom-out">
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
                <button type="button" onclick="RentalinUI.closeModal('confirmPickupModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form id="confirmPickupForm" onsubmit="submitConfirmPickup(event)" class="space-y-4">
                <div class="bg-brand-canvas rounded-2xl p-3.5 text-xs flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-gray-400">Penyerahan Unit</p>
                        <p id="pickup-modal-vehicle" class="font-extrabold text-brand-ink mt-0.5">-</p>
                    </div>
                    <span id="pickup-modal-code" class="font-mono font-extrabold text-brand-pink">-</span>
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
                    <button type="button" onclick="RentalinUI.closeModal('confirmPickupModal')"
                        class="flex-1 py-3 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-pickup"
                        class="flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                        Konfirmasi Unit Diambil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const TX_CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";
            window.TX_CSRF = TX_CSRF;
            window.dashboardRentals = @json($normalizedRentals);

            const requestedCode = @json($initialCode);
            const preselected = window.dashboardRentals.find(r => r.booking_code.toUpperCase() === requestedCode);
            window.selectedRental = preselected || window.dashboardRentals[0] || null;
            window.html5QrScanner = null;
        })();

        function getRentalState(rental) {
            if (rental.is_completed) return 'selesai';
            if (rental.is_picked_up) return 'disewa';
            if (rental.is_paid) return 'siap_diambil';
            if (rental.payment_proof) return 'menunggu_konfirmasi';
            return 'menunggu_pembayaran';
        }

        function renderSelectedRentalPanel() {
            const rental = window.selectedRental;
            if (!rental) return;
            const state = getRentalState(rental);

            document.getElementById('scan-code-input').value = rental.booking_code;
            document.getElementById('detail-booking-code').innerText = rental.booking_code;
            document.getElementById('detail-customer-name').innerText = rental.customer_name;
            document.getElementById('detail-customer-phone').innerText = RentalinUI.formatPhone(rental.customer_phone);
            document.getElementById('detail-vehicle-name').innerText = rental.vehicle_name + (rental.license_plate ? ` (${rental.license_plate})` : '');
            document.getElementById('detail-schedule-info').innerText = `${RentalinUI.formatDate(rental.pickup_date, 'datetime')} (${rental.duration_days} Hari)`;
            document.getElementById('detail-total-amount').innerText = RentalinUI.formatRupiah(rental.total_amount);
            document.getElementById('detail-invoice-link').href = `/portal/invoice/${encodeURIComponent(rental.booking_code)}`;

            const statusBadge = document.getElementById('detail-status-badge');
            const btnPay      = document.getElementById('btn-confirm-payment');
            const iconPay     = document.getElementById('icon-confirm-payment');
            const labelPay    = document.getElementById('label-confirm-payment');
            const btnPickup   = document.getElementById('btn-confirm-pickup');
            const iconPickup  = document.getElementById('icon-confirm-pickup');
            const labelPickup = document.getElementById('label-confirm-pickup');
            const btnReturn   = document.getElementById('btn-confirm-return');
            const colInfo     = document.getElementById('detail-collateral-info');

            if (rental.collateral_type) {
                colInfo.innerText = `Jaminan tersimpan: ${rental.collateral_type}`;
                colInfo.classList.remove('hidden');
            } else {
                colInfo.classList.add('hidden');
            }

            if (rental.is_paid) {
                btnPay.disabled = true;
                btnPay.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-extrabold cursor-default';
                iconPay.className = 'bi bi-check-circle-fill';
                labelPay.innerText = 'Pembayaran Sah';
            } else if (!rental.payment_proof) {
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

            if (!rental.is_paid) {
                btnPickup.disabled = true;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
                iconPickup.className = 'bi bi-lock-fill';
                labelPickup.innerText = 'Unit Sudah Diambil';
            } else if (rental.is_picked_up) {
                btnPickup.disabled = true;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-extrabold cursor-default';
                iconPickup.className = 'bi bi-car-front-fill';
                labelPickup.innerText = rental.is_completed ? 'Sewa Selesai' : 'Unit Sudah Diambil';
            } else {
                btnPickup.disabled = false;
                btnPickup.className = 'inline-flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold transition-all cursor-pointer';
                iconPickup.className = 'bi bi-key-fill';
                labelPickup.innerText = 'Unit Sudah Diambil';
            }

            if (btnReturn) {
                if (rental.is_picked_up && !rental.is_completed) {
                    btnReturn.classList.remove('hidden');
                } else {
                    btnReturn.classList.add('hidden');
                }
            }

            if (state === 'selesai') {
                statusBadge.className = 'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase bg-gray-200/80 dark:bg-white/10 text-gray-600 dark:text-gray-300';
                statusBadge.innerText = 'Sewa Selesai';
            } else if (state === 'disewa') {
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

            syncTableRowUI(rental);
            filterTransactionsTable();
        }

        function syncTableRowUI(rental) {
            const row = document.querySelector(`.rental-row[data-code="${rental.booking_code}"]`);
            if (!row) return;
            const badge = row.querySelector('.row-status-badge');
            const state = getRentalState(rental);
            row.dataset.state = state;

            if (badge) {
                if (state === 'selesai') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-gray-200/80 dark:bg-white/10 text-gray-600 dark:text-gray-300';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Selesai';
                } else if (state === 'disewa') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sedang Disewa';
                } else if (state === 'siap_diambil') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Siap Diambil';
                } else if (state === 'menunggu_konfirmasi') {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-blue-500/15 text-blue-600 dark:text-blue-400';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Menunggu Konfirmasi';
                } else {
                    badge.className = 'row-status-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-amber-500/15 text-amber-600 dark:text-amber-400';
                    badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu Pembayaran';
                }
            }
        }

        function selectTransactionByCode(code) {
            const clean = String(code || '').trim().toUpperCase();
            const found = window.dashboardRentals.find(r => r.booking_code.toUpperCase() === clean);
            if (!found) {
                alert(`Kode booking "${clean}" tidak ditemukan.`);
                return;
            }
            window.selectedRental = found;
            renderSelectedRentalPanel();
        }

        function lookupBookingFromInput(e) {
            e.preventDefault();
            selectTransactionByCode(document.getElementById('scan-code-input').value);
        }

        function openConfirmPaymentModal() {
            const rental = window.selectedRental;
            if (!rental || rental.is_paid || !rental.payment_proof) return;

            document.getElementById('pay-modal-code').innerText = rental.booking_code;
            document.getElementById('pay-modal-customer').innerText = rental.customer_name;
            document.getElementById('pay-modal-amount').innerText = RentalinUI.formatRupiah(rental.total_amount);

            const statusPill = document.getElementById('pay-modal-status-pill');
            const proofWrap  = document.getElementById('pay-modal-proof-wrap');
            const proofImg   = document.getElementById('pay-modal-proof-img');
            const noProof    = document.getElementById('pay-modal-no-proof');
            const zoomBtn    = document.getElementById('pay-modal-zoom-btn');
            const cb         = document.getElementById('legal-payment-checkbox');

            cb.checked = false;

            if (rental.payment_proof) {
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400';
                statusPill.innerText = 'Menunggu Konfirmasi';
                proofImg.src = rental.payment_proof;
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
            RentalinUI.openModal('confirmPaymentModal');
        }

        function openProofLightbox() {
            if (!window.selectedRental || !window.selectedRental.payment_proof) return;
            document.getElementById('proof-lightbox-img').src = window.selectedRental.payment_proof;
            RentalinUI.openModal('proofLightboxModal');
        }

        function toggleLegalPaymentButton() {
            const cb = document.getElementById('legal-payment-checkbox');
            const canConfirm = Boolean(window.selectedRental && window.selectedRental.payment_proof && !cb.disabled && cb.checked);
            const btn = document.getElementById('btn-submit-legal-payment');
            btn.disabled = !canConfirm;
            btn.className = canConfirm
                ? 'flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer'
                : 'flex-[2] py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
        }

        async function submitConfirmPayment() {
            const cb = document.getElementById('legal-payment-checkbox');
            const rental = window.selectedRental;
            if (!rental || !rental.payment_proof || !cb.checked) return;

            const btn = document.getElementById('btn-submit-legal-payment');
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            try {
                const res = await fetch(`/portal/transactions/${encodeURIComponent(rental.booking_code)}/confirm-payment`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.TX_CSRF,
                    },
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Gagal mengonfirmasi pembayaran.');
                    btn.disabled = false;
                    btn.innerText = 'Ya, Konfirmasi Sah';
                    return;
                }

                rental.is_paid = true;
                RentalinUI.closeModal('confirmPaymentModal');
                renderSelectedRentalPanel();
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            } finally {
                btn.innerText = 'Ya, Konfirmasi Sah';
            }
        }

        function openConfirmPickupModal() {
            const rental = window.selectedRental;
            if (!rental || !rental.is_paid || rental.is_picked_up) return;
            document.getElementById('pickup-modal-code').innerText = rental.booking_code;
            document.getElementById('pickup-modal-vehicle').innerText = rental.vehicle_name;
            document.getElementById('confirmPickupForm').reset();
            document.getElementById('collateral-placeholder').classList.remove('hidden');
            document.getElementById('collateral-placeholder').classList.add('flex');
            document.getElementById('collateral-preview-wrap').classList.add('hidden');
            RentalinUI.openModal('confirmPickupModal');
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

        async function submitConfirmPickup(e) {
            e.preventDefault();
            const rental = window.selectedRental;
            if (!rental || !rental.is_paid) return;

            const form = document.getElementById('confirmPickupForm');
            const submitBtn = document.getElementById('btn-submit-pickup');
            const formData = new FormData(form);

            submitBtn.disabled = true;
            submitBtn.innerText = 'Mengunggah...';

            try {
                const res = await fetch(`/portal/transactions/${encodeURIComponent(rental.booking_code)}/confirm-pickup`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.TX_CSRF,
                    },
                    body: formData,
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Gagal mengonfirmasi pengambilan unit.');
                    return;
                }

                rental.is_picked_up = true;
                rental.collateral_type = data.collateral_type || formData.get('collateral_type') || 'KTP Asli';
                RentalinUI.closeModal('confirmPickupModal');
                renderSelectedRentalPanel();
            } catch (err) {
                alert('Terjadi kesalahan jaringan saat mengunggah bukti jaminan.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Konfirmasi Unit Diambil';
            }
        }

        async function submitConfirmReturn() {
            const rental = window.selectedRental;
            if (!rental || !rental.is_picked_up || rental.is_completed) return;
            if (!confirm(`Selesaikan sewa ${rental.booking_code} dan kembalikan unit ${rental.vehicle_name} ke stok tersedia?`)) return;

            try {
                const res = await fetch(`/portal/transactions/${encodeURIComponent(rental.booking_code)}/confirm-return`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.TX_CSRF,
                    },
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Gagal menyelesaikan pengembalian unit.');
                    return;
                }

                rental.is_completed = true;
                renderSelectedRentalPanel();
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
        }

        function filterTransactionsTable(statusOverride) {
            const statusVal = typeof statusOverride === 'string'
                ? statusOverride
                : (document.getElementById('tx-filter-status')?.value || 'all');

            RentalinUI.filterItems({
                inputId: 'tx-search-input',
                clearBtnId: 'tx-search-input-clear',
                itemSelector: '#transactions-table tbody tr.rental-row',
                countId: 'tx-visible-count',
                emptyId: 'tx-empty-row',
                extraPredicate: (row) => statusVal === 'all' || row.dataset.state === statusVal,
            });
        }

        async function toggleCameraPreview() {
            const box = document.getElementById('qr-camera-box');
            const label = document.getElementById('cam-btn-label');

            if (!box.classList.contains('hidden')) {
                box.classList.add('hidden');
                label.innerText = 'Buka Kamera Scan';
                if (window.html5QrScanner) {
                    try { await window.html5QrScanner.stop(); } catch (e) {}
                    window.html5QrScanner = null;
                }
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                alert('Library scanner kamera belum siap.');
                return;
            }

            box.classList.remove('hidden');
            label.innerText = 'Tutup Kamera Scan';

            try {
                window.html5QrScanner = new Html5Qrcode('qr-reader-viewport');
                await window.html5QrScanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 140 } },
                    (decodedText) => {
                        const match = String(decodedText).match(/RNTL-[A-Z0-9]+/i);
                        const code = match ? match[0].toUpperCase() : decodedText.trim().toUpperCase();
                        selectTransactionByCode(code);
                        toggleCameraPreview();
                    }
                );
            } catch (err) {
                box.classList.add('hidden');
                label.innerText = 'Buka Kamera Scan';
                alert('Kamera tidak tersedia atau izin kamera ditolak browser.');
            }
        }

        renderSelectedRentalPanel();
    </script>
@endsection