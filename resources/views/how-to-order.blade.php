@extends('layouts.app')

@section('title', 'Cara Pesan - Rentalin')

@section('content')
    <div class="relative overflow-hidden">
        <!-- Layer Dekorasi Background Halus -->
        <div class="absolute inset-0 pointer-events-none opacity-65 dark:opacity-30 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] dark:bg-[radial-gradient(#2e323d_1px,transparent_1px)] [background-size:22px_22px]"></div>
        <div class="absolute -top-24 -left-24 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-brand-yellow/20 dark:bg-brand-yellow/10 blur-3xl pointer-events-none"></div>
        <div class="absolute top-28 -right-24 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-brand-pink/15 dark:bg-brand-pink/10 blur-3xl pointer-events-none"></div>

        <!-- Konten Asli -->
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <div class="text-center max-w-xl mx-auto mb-12">
                <span class="inline-block bg-brand-yellowSoft text-brand-ink text-xs font-extrabold px-4 py-1.5 rounded-full mb-3">
                    Panduan Singkat
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-brand-ink mb-3">4 Langkah Mudah Sewa</h1>
                <p class="text-gray-500 text-xs sm:text-sm leading-relaxed">
                    Dari pilih kendaraan hingga kunci di tangan dalam hitungan menit tanpa proses pendaftaran akun yang rumit.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 mb-12">
                <div class="bg-white p-6 rounded-2xl text-center hover:shadow-md transition">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-brand-yellow text-brand-ink font-extrabold text-xs mb-4">01</span>
                    <h3 class="font-bold text-brand-ink text-base mb-2">Pilih Kendaraan</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Buka halaman <strong>Cari Armada</strong>, cek galeri foto kendaraan yang tersedia, lalu klik tombol <strong>Sewa</strong>.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl text-center hover:shadow-md transition">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-brand-yellow text-brand-ink font-extrabold text-xs mb-4">02</span>
                    <h3 class="font-bold text-brand-ink text-base mb-2">Isi Jadwal & Data</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Masukkan nama lengkap sesuai KTP, nomor WhatsApp aktif, tanggal ambil, dan durasi sewa.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl text-center hover:shadow-md transition">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-brand-pink text-white font-extrabold text-xs mb-4">03</span>
                    <h3 class="font-bold text-brand-ink text-base mb-2">Bayar QRIS & PDF</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Selesaikan pembayaran lewat QRIS pada nota digital yang terbit, lalu simpan nota sebagai PDF.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl text-center hover:shadow-md transition">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-brand-pink text-white font-extrabold text-xs mb-4">04</span>
                    <h3 class="font-bold text-brand-ink text-base mb-2">Scan Nota di Lokasi</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Tunjukkan barcode pada nota digital dan titipkan KTP asli ke petugas untuk serah terima kunci.
                    </p>
                </div>
            </div>

            <!-- Syarat Ringkas -->
            <div class="bg-brand-canvas rounded-3xl p-6 sm:p-8 border border-gray-200/70 max-w-3xl mx-auto mb-10">
                <h3 class="font-extrabold text-base text-brand-ink mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-brand-pink"></i> Syarat Pengambilan Unit di Lokasi
                </h3>
                <ul class="space-y-2.5 text-xs sm:text-sm text-gray-600">
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-check text-emerald-600 mt-1"></i>
                        <span>Menunjukkan <strong>Nota Digital (PDF / Screenshot Barcode)</strong> dengan kode booking aktif.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-check text-emerald-600 mt-1"></i>
                        <span>Menitipkan <strong>e-KTP Asli</strong> yang sesuai dengan nama pemesan selama masa sewa berlangsung.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-check text-emerald-600 mt-1"></i>
                        <span>Memiliki SIM A (untuk mobil) atau SIM C (untuk motor) yang masih berlaku.</span>
                    </li>
                </ul>
            </div>

            <div class="text-center">
                <a href="{{ route('vehicles.catalog') }}" data-public-spa class="bg-brand-pink hover:bg-brand-pinkHover text-white text-sm font-bold px-8 py-3.5 rounded-full shadow-sm transition inline-flex items-center gap-2">
                    <i class="fa-solid fa-car-side"></i> Mulai Cari Kendaraan
                </a>
            </div>
        </div>
    </div>
@endsection