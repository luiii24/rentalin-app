<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Digital {{ $rental->booking_code }} - Rentalin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            #invoice-sheet { box-shadow: none !important; border: 1px solid #e5e7eb !important; max-width: 100% !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-screen py-8 px-4">

    <!-- Navigasi Atas (Tidak ikut tercetak saat Save PDF) -->
    <div class="max-w-md mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('home') }}" class="text-xs font-semibold text-gray-600 hover:text-black flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
        <button onclick="window.print()" class="text-xs font-semibold bg-black hover:bg-gray-800 text-white px-4 py-2 rounded-full shadow-sm flex items-center gap-2 transition">
            <i class="fa-solid fa-file-pdf"></i> Simpan PDF
        </button>
    </div>

    @if(session('success'))
        <div class="max-w-md mx-auto mb-4 bg-green-600 text-white text-xs font-semibold px-4 py-3 rounded-2xl shadow-sm no-print">
            <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <!-- LEMBAR NOTA DIGITAL (E-TICKET) -->
    <div id="invoice-sheet" class="max-w-md mx-auto bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 p-6 sm:p-8">
        
        <!-- Header Nota -->
        <div class="text-center border-b-2 border-dashed border-gray-200 pb-6 mb-6">
            <div class="inline-flex items-center gap-2 mb-1">
                <i class="fa-solid fa-car-side text-black text-xl"></i>
                <span class="text-2xl font-extrabold tracking-tight text-black">RENTALIN</span>
            </div>
            <p class="text-[11px] text-gray-400 uppercase tracking-widest">Digital Rental E-Ticket</p>

            <!-- Status Pembayaran -->
            <div class="mt-4">
                @if($rental->payment_status === 'paid')
                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 font-bold px-4 py-1.5 rounded-full text-xs uppercase tracking-wide">
                        <i class="fa-solid fa-circle-check"></i> Status: Lunas (QRIS)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 font-bold px-4 py-1.5 rounded-full text-xs uppercase tracking-wide">
                        <i class="fa-solid fa-clock"></i> Status: Belum Dibayar
                    </span>
                @endif
            </div>
        </div>

        <!-- Kode Booking & Barcode -->
        <div class="text-center mb-6">
            <p class="text-xs text-gray-400 mb-1">Kode Booking Anda</p>
            <p class="text-3xl font-mono font-extrabold text-black tracking-widest mb-3">{{ $rental->booking_code }}</p>
            
            <!-- Barcode Visual -->
            <div class="bg-white border border-gray-200 rounded-xl p-3 inline-block">
                <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $rental->booking_code }}&scale=2&height=12&includetext=false"
                     alt="Barcode {{ $rental->booking_code }}"
                     class="h-14 mx-auto object-contain">
            </div>
            <p class="text-[11px] text-gray-400 mt-2">Tunjukkan barcode ini & KTP asli saat pengambilan unit</p>
        </div>

        <!-- Rincian Penyewa & Kendaraan -->
        <div class="bg-gray-50 rounded-2xl p-4 mb-6 space-y-2.5 text-sm">
            <h4 class="font-bold text-xs uppercase tracking-wider text-gray-400 border-b border-gray-200 pb-2">Rincian Penyewaan</h4>
            
            <div class="flex justify-between">
                <span class="text-gray-500">Penyewa</span>
                <span class="font-semibold text-black">{{ $rental->customer_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">No. WhatsApp</span>
                <span class="font-medium text-black">{{ $rental->customer_phone }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Kendaraan</span>
                <span class="font-semibold text-black text-right">{{ $rental->vehicle->name }} ({{ $rental->vehicle->license_plate }})</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tgl Ambil</span>
                <span class="font-medium text-black">{{ $rental->pickup_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Durasi Sewa</span>
                <span class="font-medium text-black">{{ $rental->duration_days }} Hari</span>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                <span class="font-bold text-gray-900">Total Tagihan</span>
                <span class="font-extrabold text-xl text-black">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Area QRIS Pembayaran -->
        @if($rental->payment->payment_status === 'unpaid')
            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-5 text-center">
                <h4 class="font-bold text-sm text-black mb-2">Scan QRIS untuk Membayar</h4>
                <div class="bg-white p-2 inline-block rounded-xl border border-gray-200 shadow-sm mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=QRIS-RENTALIN-{{ $rental->booking_code }}-{{ (int)$rental->total_price }}"
                         alt="QRIS Payment"
                         class="w-36 h-36 mx-auto">
                </div>
                <p class="text-xs text-red-600 font-medium leading-relaxed mb-4">
                    Penting: Jika pembayaran belum diselesaikan, pesanan tidak dapat diproses saat di lokasi rental.
                </p>

                <!-- Tombol Simulasi Pelunasan QRIS -->
                <form action="{{ route('booking.pay', $rental->booking_code) }}" method="POST" class="no-print">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-3 px-4 rounded-xl transition">
                        <i class="fa-solid fa-check-circle mr-1"></i> Konfirmasi Pembayaran QRIS (Simulasi Lunas)
                    </button>
                </form>
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
                <p class="text-xs text-green-800 font-medium">
                    Pembayaran diterima pada <strong>{{ $rental->payment->paid_at->format('d M Y, H:i') }} WIB</strong>.<br>
                    Silakan simpan nota ini sebagai PDF dan tunjukkan ke petugas lokasi.
                </p>
            </div>
        @endif

    </div>

</body>
</html>