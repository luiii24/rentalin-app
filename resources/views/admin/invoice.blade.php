<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Digital {{ $rental->booking_code ?? 'RNTL-DEMO01' }} - Rentalin</title>
    @include('partials.theme-engine')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            body { background: #FFFFFF !important; }
            .no-print { display: none !important; }
            .ticket-card { box-shadow: none !important; max-width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-brand-canvas text-brand-ink font-sans antialiased min-h-screen py-6 sm:py-10 px-4">
    @php
        $bookingCode   = $rental->booking_code ?? 'RNTL-DEMO01';
        $customerName  = $rental->customer_name ?? 'Budi Santoso';
        $customerPhone = $rental->customer_phone ?? '081234567890';
        $vehicleName   = $rental->vehicle_name ?? ($rental->vehicle->name ?? 'Toyota Avanza Veloz (L 1428 AB)');
        $pickupDate    = isset($rental->pickup_date)
                            ? \Carbon\Carbon::parse($rental->pickup_date)->translatedFormat('d M Y')
                            : '26 Nov 2026';
        $durationDays  = $rental->duration_days ?? 2;
        $totalAmount   = $rental->total_amount ?? ($rental->total_price ?? 700000);
        $paymentStatus = strtolower($rental->payment_status ?? ($rental->status ?? 'pending'));
        $isPaid        = in_array($paymentStatus, ['paid', 'lunas', 'aktif', 'verified', 'disewa', 'siap_diambil']);
        $existingProof = $rental->payment_proof ?? null;
        $isWaiting     = !$isPaid && (!empty($existingProof) || $paymentStatus === 'menunggu_konfirmasi');
        $isLocked      = $isPaid || !empty($existingProof);
    @endphp

    <!-- BAR AKSI ATAS -->
    <div class="no-print max-w-xl mx-auto flex items-center justify-between gap-3 mb-5">
        <a href="{{ Route::has('home') ? route('home') : url('/') }}"
           class="inline-flex items-center gap-2 bg-white hover:opacity-90 text-brand-ink text-xs font-extrabold px-4 py-2.5 rounded-full shadow-2xs transition">
            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
        </a>

        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 bg-[#111216] hover:opacity-90 text-white text-xs font-extrabold px-4 py-2.5 rounded-full shadow-2xs transition cursor-pointer">
            <i class="bi bi-file-earmark-pdf-fill"></i> Simpan PDF
        </button>
    </div>

    <!-- KARTU NOTA DIGITAL -->
    <div class="ticket-card max-w-xl mx-auto bg-white rounded-3xl p-6 sm:p-8 shadow-sm">
        <!-- HEADER BRAND -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center gap-2 text-brand-ink font-extrabold text-xl tracking-tight">
                <i class="bi bi-car-front-fill text-lg"></i>
                <span>RENTALIN</span>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-gray-400 mt-1">
                DIGITAL RENTAL E-TICKET
            </p>

            <!-- BADGE STATUS PEMBAYARAN -->
            <div class="mt-4">
                <span id="invoice-status-badge"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[11px] font-extrabold uppercase tracking-wide {{ $isPaid ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : ($isWaiting ? 'bg-blue-500/15 text-blue-600 dark:text-blue-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400') }}">
                    <i id="invoice-status-icon" class="bi {{ $isPaid ? 'bi-check-circle-fill' : ($isWaiting ? 'bi-hourglass-split' : 'bi-clock-fill') }}"></i>
                    <span id="invoice-status-text">
                        {{ $isPaid ? 'STATUS: LUNAS (QRIS)' : ($isWaiting ? 'STATUS: MENUNGGU KONFIRMASI' : 'STATUS: MENUNGGU PEMBAYARAN QRIS') }}
                    </span>
                </span>
            </div>
        </div>

        <!-- PERINGATAN WAJIB UPLOAD BUKTI PEMBAYARAN -->
        <div id="proof-reminder-banner"
             class="no-print mt-5 bg-amber-500/15 rounded-2xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 {{ ($isPaid || $isWaiting) ? 'hidden' : '' }}">
            <div class="flex items-start gap-2.5">
                <i class="bi bi-exclamation-triangle-fill text-amber-600 dark:text-amber-400 text-base mt-0.5 flex-shrink-0"></i>
                <div class="text-xs leading-relaxed">
                    <p class="font-extrabold text-amber-800 dark:text-amber-300">
                        Wajib Upload Bukti Pembayaran
                    </p>
                    <p class="text-[11px] text-amber-700/90 dark:text-amber-200/80 mt-0.5">
                        Setelah melakukan pembayaran QRIS, gulir ke bagian paling bawah nota ini untuk mengunggah bukti pembayaran agar pesanan dapat dikonfirmasi.
                    </p>
                </div>
            </div>

            <button type="button" onclick="scrollToUploadProof()"
                class="self-start sm:self-center flex-shrink-0 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-[11px] font-extrabold px-3.5 py-2 rounded-full transition cursor-pointer">
                <span>Upload Bukti</span>
                <i class="bi bi-arrow-down"></i>
            </button>
        </div>

        <!-- GARIS POTONG TIKET -->
        <div class="my-6 border-t-2 border-dashed !border-gray-200 dark:!border-white/10 keep-border"></div>

        <!-- KODE BOOKING -->
        <div class="text-center mb-5">
            <p class="text-[11px] font-semibold text-gray-400">Kode Booking Anda</p>
            <h1 class="font-mono text-2xl sm:text-3xl font-extrabold tracking-[0.18em] text-brand-ink mt-1">
                {{ $bookingCode }}
            </h1>
        </div>

        <!-- SUSUNAN ATAS - BAWAH: 1. BARCODE KONFIRMASI (ATAS), 2. QRIS PEMBAYARAN (BAWAH) -->
        <div class="space-y-4 mb-6">
            <!-- ATAS: BARCODE KONFIRMASI -->
            <div class="bg-brand-canvas rounded-2xl p-4 sm:p-5 flex flex-col items-center text-center">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 mb-2.5">
                    Barcode Konfirmasi Pengambilan
                </span>
                <div class="bg-white rounded-2xl px-5 py-3.5 w-full max-w-sm flex items-center justify-center">
                    <svg id="booking-barcode-svg" class="w-full max-w-[240px] h-20" viewBox="0 0 200 70" xmlns="http://www.w3.org/2000/svg"></svg>
                </div>
                <p class="text-[11px] text-gray-500 mt-2.5">
                    Tunjukkan barcode ini &amp; KTP asli saat pengambilan unit
                </p>
            </div>

            <!-- BAWAH: QRIS PEMBAYARAN KOTAK PERSEGI BESAR -->
            <!-- BAWAH: QRIS PEMBAYARAN -->
            <div class="bg-brand-canvas rounded-2xl p-5 sm:p-6 flex flex-col items-center text-center">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-brand-pink mb-3">
                    QRIS Pembayaran
                </span>
                <div class="bg-white rounded-2xl p-4 sm:p-5 flex items-center justify-center shadow-2xs">
                    @if(!empty($storeQrisUrl))
                        <img src="{{ $storeQrisUrl }}" alt="QRIS Pembayaran Rentalin" class="max-h-64 w-auto rounded-xl object-contain">
                    @else
                        <div id="qris-qrcode-box" class="w-48 h-48 sm:w-52 sm:h-52 flex items-center justify-center"></div>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    Scan QRIS di atas untuk membayar <strong class="text-brand-ink">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                </p>
            </div>
        </div>

        <!-- RINCIAN PENYEWAAN -->
        <div class="bg-brand-canvas rounded-2xl p-4 sm:p-5 space-y-3">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">
                Rincian Penyewaan
            </p>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-500 font-medium">Penyewa</span>
                    <span class="font-extrabold text-brand-ink text-right">{{ $customerName }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-500 font-medium">No. WhatsApp</span>
                    <span class="font-bold text-brand-ink text-right">{{ $customerPhone }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-500 font-medium">Kendaraan</span>
                    <span class="font-extrabold text-brand-ink text-right">{{ $vehicleName }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-500 font-medium">Tgl Ambil</span>
                    <span class="font-bold text-brand-ink text-right">{{ $pickupDate }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-gray-500 font-medium">Durasi Sewa</span>
                    <span class="font-bold text-brand-ink text-right">{{ $durationDays }} Hari</span>
                </div>
            </div>

            <div class="pt-3 mt-1 border-t border-dashed !border-gray-300/70 dark:!border-white/10 keep-border flex items-center justify-between">
                <span class="text-xs font-extrabold text-brand-ink">Total Tagihan</span>
                <span class="text-base sm:text-lg font-extrabold text-brand-ink">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- FORM UPLOAD BUKTI PEMBAYARAN (TERKUNCI JIKA SUDAH DIUPLOAD ATAU STATUS LUNAS) -->
        <div id="upload-bukti-section" class="no-print mt-5 bg-brand-canvas rounded-2xl p-4 sm:p-5 transition-all">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div>
                    <h2 class="text-xs font-extrabold text-brand-ink">Upload Bukti Pembayaran</h2>
                    <p id="proof-helper-subtitle" class="text-[11px] text-gray-500">
                        {{ $isPaid ? 'Pembayaran sudah lunas dan terverifikasi' : ($existingProof ? 'Bukti pembayaran sudah diunggah dan terkunci' : 'Unggah screenshot / foto struk pembayaran QRIS Anda') }}
                    </p>
                </div>
                <span id="proof-uploaded-tag"
                    class="{{ $isLocked ? '' : 'hidden' }} {{ $isPaid ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400' }} text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                    {{ $isPaid ? 'Lunas' : 'Menunggu Konfirmasi' }}
                </span>
            </div>

            <form id="paymentProofForm"
                  action="{{ Route::has('portal.payment.upload') ? route('portal.payment.upload', $bookingCode) : '' }}"
                  method="POST"
                  enctype="multipart/form-data"
                  onsubmit="handlePaymentProofSubmit(event)"
                  class="space-y-3">
                @csrf

                <input type="file" id="payment-proof-input" name="payment_proof" accept="image/*" required
                    {{ $isLocked ? 'disabled' : '' }}
                    class="hidden" onchange="previewPaymentProof(this)">

                <div id="proof-dropzone"
                     onclick="triggerProofPicker()"
                     class="bg-white rounded-2xl p-4 text-center transition {{ $isLocked ? 'cursor-not-allowed opacity-80' : 'hover:opacity-95 cursor-pointer' }}">
                    @if($isPaid && empty($existingProof))
                        <div class="flex flex-col items-center justify-center py-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base mb-2">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <p class="text-xs font-bold text-brand-ink">Pembayaran Telah Lunas</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Unggah bukti pembayaran tidak diperlukan lagi</p>
                        </div>
                    @else
                        <div id="proof-placeholder" class="{{ $existingProof ? 'hidden' : 'flex' }} flex-col items-center justify-center py-2">
                            <div class="w-10 h-10 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-base mb-2">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <p class="text-xs font-bold text-brand-ink">Klik untuk pilih foto bukti bayar</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Format JPG, PNG, atau WEBP (Maks. 5MB)</p>
                        </div>

                        <div id="proof-preview-wrap" class="{{ $existingProof ? 'flex' : 'hidden' }} flex-col items-center gap-2">
                            <img id="proof-preview-img"
                                 src="{{ $existingProof ? asset('storage/' . $existingProof) : '' }}"
                                 alt="Bukti Pembayaran"
                                 class="max-h-52 rounded-xl object-contain bg-brand-canvas">
                            <span id="proof-file-name" class="text-[11px] font-bold text-gray-500">
                                {{ $existingProof ? 'Bukti pembayaran telah diunggah' : '' }}
                            </span>
                        </div>
                    @endif
                </div>

                <button type="submit" id="btn-submit-proof"
                    {{ $isLocked ? 'disabled' : '' }}
                    class="w-full py-3 rounded-full text-xs font-extrabold transition-all {{ $isLocked ? 'bg-gray-200 dark:bg-white/10 text-gray-400 cursor-not-allowed' : 'bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white cursor-pointer' }}">
                    {{ $isPaid ? 'Pembayaran Sudah Lunas' : ($existingProof ? 'Bukti Pembayaran Sudah Diupload' : 'Kirim Bukti Pembayaran') }}
                </button>
            </form>
        </div>
    </div>

    <!-- POP-UP KONFIRMASI SAAT BARU MEMBUKA NOTA (1 TOMBOL YA, TANPA CHECKBOX) -->
    <div id="invoiceNoticeModal" class="no-print fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm p-6 text-center shadow-2xl">
            <div class="w-12 h-12 rounded-2xl bg-brand-pinkSoft text-brand-pink flex items-center justify-center text-xl mx-auto mb-3.5">
                <i class="bi bi-info-circle-fill"></i>
            </div>

            <h3 class="text-base font-extrabold text-brand-ink">Informasi Penting</h3>

            <div class="bg-brand-canvas rounded-2xl p-4 my-4 text-xs text-gray-600 leading-relaxed space-y-2.5 text-left">
                <p class="flex items-start gap-2.5">
                    <i class="bi bi-receipt-cutoff text-brand-pink mt-0.5 flex-shrink-0"></i>
                    <span>Harap <strong>konfirmasi pembayaran</strong> dengan mengunggah bukti bayar pada tombol di bagian paling bawah halaman ini.</span>
                </p>
                <p class="flex items-start gap-2.5">
                    <i class="bi bi-bookmark-check-fill text-brand-pink mt-0.5 flex-shrink-0"></i>
                    <span>Harap <strong>simpan nota ini</strong> menggunakan fitur tombol <strong>Simpan PDF</strong> yang tersedia di bagian atas halaman sebagai bukti pengambilan unit kendaraan.</span>
                </p>
            </div>

            <button type="button" onclick="closeInvoiceNoticeModal()"
                class="w-full py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition-all cursor-pointer">
                Ya
            </button>
        </div>
    </div>

    <script>
        const invoiceBookingCode = @json($bookingCode);
        const invoiceTotalAmount = @json($totalAmount);
        let isProofLocked        = @json($isLocked);

        function closeInvoiceNoticeModal() {
            const modal = document.getElementById('invoiceNoticeModal');
            if (modal) modal.classList.add('hidden');
        }

        function scrollToUploadProof() {
            const target = document.getElementById('upload-bukti-section');
            if (!target) return;
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            target.classList.add('ring-2', 'ring-brand-pink');
            setTimeout(() => target.classList.remove('ring-2', 'ring-brand-pink'), 1800);
        }

        (function initInvoiceCodes() {
            // 1. Render 1D Barcode Konfirmasi (Atas)
            const svg = document.getElementById('booking-barcode-svg');
            if (svg) {
                let x = 6;
                const bars = [];
                for (let i = 0; i < invoiceBookingCode.length; i++) {
                    const charCode = invoiceBookingCode.charCodeAt(i);
                    for (let bit = 0; bit < 5; bit++) {
                        const isWide = ((charCode >> bit) & 1) === 1;
                        const w = isWide ? 3.2 : 1.6;
                        if ((i + bit) % 2 === 0 || isWide) {
                            bars.push(`<rect x="${x}" y="4" width="${w}" height="62" fill="#111216" rx="0.5"/>`);
                        }
                        x += w + 1.8;
                        if (x > 192) break;
                    }
                    if (x > 192) break;
                }
                svg.innerHTML = bars.join('');
            }

            // 2. Render QRIS Square QR Code Besar & Jelas (Bawah)
            const qrContainer = document.getElementById('qris-qrcode-box');
            if (qrContainer && typeof QRCode !== 'undefined') {
                const qrisPayload = `00020101021226580014ID.CO.QRIS.WWW0215ID10202600000015204551153033605406${invoiceTotalAmount}5802ID5908RENTALIN6008SURABAYA62150111${invoiceBookingCode}6304ABCD`;
                new QRCode(qrContainer, {
                    text: qrisPayload,
                    width: 200,
                    height: 200,
                    colorDark: '#111216',
                    colorLight: '#FFFFFF',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }

            const savedProof = localStorage.getItem(`rentalin_proof_${invoiceBookingCode}`);
            if (savedProof && !isProofLocked) {
                applyLockedProofUI(savedProof, 'Bukti pembayaran telah diunggah');
            }
        })();

        function triggerProofPicker() {
            if (isProofLocked) return;
            const input = document.getElementById('payment-proof-input');
            if (input) input.click();
        }

        function previewPaymentProof(input) {
            if (isProofLocked || !input.files || !input.files[0]) return;
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('proof-placeholder').classList.add('hidden');
                document.getElementById('proof-placeholder').classList.remove('flex');

                const wrap = document.getElementById('proof-preview-wrap');
                wrap.classList.remove('hidden');
                wrap.classList.add('flex');

                document.getElementById('proof-preview-img').src = evt.target.result;
                document.getElementById('proof-file-name').innerText = `${file.name} (Klik untuk ganti sebelum dikirim)`;
            };
            reader.readAsDataURL(file);
        }

        function applyLockedProofUI(imageDataUrl, captionText) {
            isProofLocked = true;

            const reminder = document.getElementById('proof-reminder-banner');
            if (reminder) reminder.classList.add('hidden');

            const input = document.getElementById('payment-proof-input');
            const dropzone = document.getElementById('proof-dropzone');
            const placeholder = document.getElementById('proof-placeholder');
            const wrap = document.getElementById('proof-preview-wrap');
            const previewImg = document.getElementById('proof-preview-img');
            const fileNameEl = document.getElementById('proof-file-name');
            const btn = document.getElementById('btn-submit-proof');
            const tag = document.getElementById('proof-uploaded-tag');
            const subtitle = document.getElementById('proof-helper-subtitle');

            if (input) input.disabled = true;
            if (dropzone) {
                dropzone.classList.remove('hover:opacity-95', 'cursor-pointer');
                dropzone.classList.add('cursor-not-allowed', 'opacity-80');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
                placeholder.classList.remove('flex');
            }
            if (wrap && previewImg && imageDataUrl) {
                previewImg.src = imageDataUrl;
                wrap.classList.remove('hidden');
                wrap.classList.add('flex');
            }
            if (fileNameEl) fileNameEl.innerText = captionText || 'Bukti pembayaran telah diunggah';
            if (subtitle) subtitle.innerText = 'Bukti pembayaran sudah diunggah dan tidak dapat diubah lagi';
            if (tag) tag.classList.remove('hidden');

            if (btn) {
                btn.disabled = true;
                btn.className = 'w-full py-3 rounded-full bg-gray-200 dark:bg-white/10 text-gray-400 text-xs font-extrabold cursor-not-allowed transition-all';
                btn.innerText = 'Bukti Pembayaran Sudah Diupload';
            }

            const badge = document.getElementById('invoice-status-badge');
            const icon  = document.getElementById('invoice-status-icon');
            const text  = document.getElementById('invoice-status-text');
            if (badge && icon && text && !text.innerText.includes('LUNAS')) {
                badge.className = 'inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[11px] font-extrabold uppercase tracking-wide bg-blue-500/15 text-blue-600 dark:text-blue-400';
                icon.className  = 'bi bi-hourglass-split';
                text.innerText  = 'STATUS: MENUNGGU KONFIRMASI';
            }
        }

        function handlePaymentProofSubmit(e) {
            if (isProofLocked) {
                e.preventDefault();
                return;
            }

            const form = e.target;
            const actionUrl = form.getAttribute('action');
            const previewImg = document.getElementById('proof-preview-img');
            const currentSrc = previewImg ? previewImg.src : '';

            if (!actionUrl) {
                e.preventDefault();
                if (!currentSrc) return;
                try {
                    localStorage.setItem(`rentalin_proof_${invoiceBookingCode}`, currentSrc);
                } catch (err) {}
                applyLockedProofUI(currentSrc, 'Bukti pembayaran telah diunggah');
            }
        }
    </script>
</body>
</html>