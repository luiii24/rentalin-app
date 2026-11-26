@extends('layouts.app')

@section('title', 'Beranda - Rentalin')

@section('content')
    <!-- ================= HERO BANNER DENGAN GELOMBANG ORGANIK ================= -->
    <div class="relative bg-brand-canvas overflow-hidden">
        <!-- Aksen Blok Kuning & Pink di Sudut -->
        <div class="absolute -left-14 -top-10 sm:-left-20 sm:top-8 w-40 h-40 sm:w-64 md:w-72 sm:h-64 md:h-72 rounded-full bg-brand-yellow/90 pointer-events-none transition-all"></div>
        <div class="absolute -right-14 -bottom-6 sm:-right-24 sm:-bottom-10 w-44 h-44 sm:w-72 md:w-80 sm:h-72 md:h-80 rounded-full bg-brand-pink/90 pointer-events-none transition-all"></div>
        <div class="absolute right-8 top-6 sm:right-16 sm:top-10 w-20 h-20 sm:w-32 sm:h-32 rounded-full bg-brand-yellow/25 pointer-events-none"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-24 sm:pt-20 sm:pb-32 text-center">
            <span class="inline-block bg-white/95 backdrop-blur-xs border border-gray-200/80 text-brand-ink text-[11px] font-extrabold uppercase tracking-wider px-4 py-1.5 rounded-full mb-5 shadow-2xs">
                Sewa Mobil & Motor Tanpa Login
            </span>
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold text-brand-ink tracking-tight mb-5 leading-[1.15]">
                Perjalanan terbaik,<br />tanpa ribet bikin akun.
            </h1>
            <p class="text-sm sm:text-base text-gray-600 max-w-xl mx-auto mb-8 leading-relaxed">
                Pilih armada mobil atau motor terawat sesuai kebutuhanmu, bayar instan lewat QRIS, dan ambil kunci di lokasi cukup dengan nota digital.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-3 max-w-xs sm:max-w-none mx-auto">
                <a href="{{ route('vehicles.catalog') }}" data-public-spa class="bg-brand-pink hover:bg-brand-pinkHover text-white text-sm font-bold px-8 py-3.5 rounded-full shadow-sm transition">
                    Pilih Armada Sekarang
                </a>
                <a href="{{ route('how-to-order') }}" data-public-spa class="bg-white/95 hover:bg-gray-50 border border-gray-200 text-brand-ink text-sm font-bold px-8 py-3.5 rounded-full transition">
                    Pelajari Cara Pesan
                </a>
            </div>
        </div>

        <!-- Pembatas Gelombang Berlapis (Pengganti Garis Lurus Kaku) -->
        <div class="absolute bottom-0 inset-x-0 pointer-events-none leading-none">
            <svg class="w-full h-12 sm:h-20" viewBox="0 0 1440 100" preserveAspectRatio="none">
                <!-- Lapisan gelombang transparan -->
                <path d="M0,35 C360,95 1080,-10 1440,55 L1440,100 L0,100 Z" class="fill-white/40 dark:fill-[#1B1D22]/40"></path>
                <!-- Lapisan gelombang utama menyatu ke section bawah -->
                <path d="M0,60 C420,105 980,10 1440,70 L1440,100 L0,100 Z" class="fill-white dark:fill-[#1B1D22]"></path>
            </svg>
        </div>
    </div>

    <!-- ================= TRUST STRIP (IKON BULAT MENGALIR TANPA GARIS KAKU) ================= -->
    <div class="relative bg-white pt-4 pb-10 sm:pb-14">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-8">
                Standar layanan di setiap perjalanan
            </p>

            <!-- Baris Ikon Lingkaran ala Referensi -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 sm:gap-8">
                <div class="flex flex-col items-center group">
                    <div class="w-14 h-14 rounded-full bg-brand-pink/15 text-brand-pink flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <span class="font-extrabold text-xs text-brand-ink tracking-wide">QRIS INSTAN</span>
                    <span class="text-[11px] text-gray-400 mt-0.5">Konfirmasi Otomatis</span>
                </div>

                <div class="flex flex-col items-center group">
                    <div class="w-14 h-14 rounded-full bg-brand-yellow/25 text-brand-ink flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <span class="font-extrabold text-xs text-brand-ink tracking-wide">UNIT TERAWAT</span>
                    <span class="text-[11px] text-gray-400 mt-0.5">Servis Berkala</span>
                </div>

                <div class="flex flex-col items-center group">
                    <div class="w-14 h-14 rounded-full bg-brand-pink/15 text-brand-pink flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <span class="font-extrabold text-xs text-brand-ink tracking-wide">NOTA PDF</span>
                    <span class="text-[11px] text-gray-400 mt-0.5">Barcode Siap Scan</span>
                </div>

                <div class="flex flex-col items-center group">
                    <div class="w-14 h-14 rounded-full bg-brand-yellow/25 text-brand-ink flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <span class="font-extrabold text-xs text-brand-ink tracking-wide">PROSES 2 MENIT</span>
                    <span class="text-[11px] text-gray-400 mt-0.5">Tanpa Bikin Akun</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TRANSISI LENGKUNG KE GRID KEUNGGULAN ================= -->
    <div class="relative bg-brand-canvas pt-16 pb-20 sm:pt-20 sm:pb-24 overflow-hidden">
        <!-- Lengkungan Lembut di Bagian Atas (Pengganti Garis Pembagi Lurus) -->
        <div class="absolute top-0 inset-x-0 pointer-events-none leading-none">
            <svg class="w-full h-10 sm:h-16" viewBox="0 0 1440 80" preserveAspectRatio="none">
                <path d="M0,0 L1440,0 L1440,25 Q720,85 0,25 Z" class="fill-white dark:fill-[#1B1D22]"></path>
            </svg>
        </div>

        <!-- Dekorasi Cahaya Halus di Latar Belakang -->
        <div class="absolute -left-20 top-1/3 w-64 h-64 rounded-full bg-brand-yellow/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -right-20 bottom-1/4 w-64 h-64 rounded-full bg-brand-pink/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-xl mx-auto mb-10 sm:mb-12">
                <span class="inline-block text-[11px] font-extrabold uppercase tracking-widest text-brand-pink mb-2">
                    Kenapa Rentalin?
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-brand-ink mb-2">
                    Cara cerdas menyewa kendaraan
                </h2>
                <p class="text-xs sm:text-sm text-gray-500">
                    Semua kebutuhan sewa kendaraan diringkas dalam satu alur tanpa login.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute bottom-1 left-1 w-7 h-7 bg-brand-yellow rounded-lg"></span>
                        <i class="fa-solid fa-bolt relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Tanpa Login / Daftar</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Langsung isi jadwal dan data penyewa dalam hitungan detik tanpa perlu membuat akun atau mengingat kata sandi.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute bottom-1 left-1 w-7 h-7 bg-brand-yellow rounded-lg"></span>
                        <i class="fa-solid fa-qrcode relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Nota PDF & QRIS</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Dapatkan nota digital berisi kode pembayaran QRIS dan barcode pengambilan yang bisa langsung disimpan sebagai PDF.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute bottom-1 left-1 w-7 h-7 bg-brand-yellow rounded-lg"></span>
                        <i class="fa-regular fa-images relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Galeri Foto Detail</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Periksa kondisi bodi kendaraan dari tampak depan, samping, hingga interior sebelum melakukan pemesanan.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute top-1 right-1 w-7 h-7 bg-brand-pink/80 rounded-lg"></span>
                        <i class="fa-solid fa-layer-group relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Stok Real-Time</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Ketersediaan unit diperbarui secara otomatis setiap kali terjadi transaksi atau pengembalian armada.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute top-1 right-1 w-7 h-7 bg-brand-pink/80 rounded-lg"></span>
                        <i class="fa-solid fa-id-card relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Verifikasi Cepat</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Cukup tunjukkan barcode nota dan titipkan KTP asli di lokasi untuk serah terima kunci tanpa antre lama.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="bg-white rounded-3xl p-6 sm:p-7 text-center shadow-2xs hover:shadow-md border border-gray-100/80 transition duration-300">
                    <div class="relative w-14 h-14 mx-auto mb-4 flex items-center justify-center">
                        <span class="absolute top-1 right-1 w-7 h-7 bg-brand-pink/80 rounded-lg"></span>
                        <i class="fa-solid fa-tags relative z-10 text-2xl text-brand-ink"></i>
                    </div>
                    <h3 class="font-bold text-base text-brand-ink mb-2">Tarif Harian Jelas</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Harga sewa transparan per hari dengan kalkulasi total otomatis sejak awal sebelum nota diterbitkan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Lengkungan Halus Bawah Menuju Footer -->
        <div class="absolute bottom-0 inset-x-0 pointer-events-none leading-none">
            <svg class="w-full h-10 sm:h-14" viewBox="0 0 1440 70" preserveAspectRatio="none">
                <path d="M0,45 C480,0 960,80 1440,30 L1440,70 L0,70 Z" class="fill-white dark:fill-[#1B1D22]"></path>
            </svg>
        </div>
    </div>
@endsection