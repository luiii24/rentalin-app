@extends('layouts.admin')

@section('title', 'Scan Nota Pelanggan')
@section('header_title', 'Verifikasi & Scan Nota')

@section('content')
    <div class="max-w-2xl mx-auto">
        <!-- Box Input / Scan Barcode -->
        <div class="bg-white rounded-3xl border-2 border-brand-yellow p-6 sm:p-8 shadow-xs mb-6">
            <div class="text-center mb-6">
                <span class="w-12 h-12 rounded-2xl bg-brand-yellowSoft text-brand-ink inline-flex items-center justify-center text-xl mb-3">
                    <i class="fa-solid fa-barcode"></i>
                </span>
                <h1 class="text-xl font-extrabold">Scan atau Ketik Kode Booking</h1>
                <p class="text-xs text-gray-500 mt-1">Arahkan scanner barcode ke nota pelanggan atau ketik kode secara manual.</p>
            </div>

            <form action="{{ route('admin.scan') }}" method="GET" data-spa-form class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="code" value="{{ request('code') }}" autofocus required placeholder="RNTL-XXXXXX"
                    class="flex-1 border-2 border-gray-200 focus:border-brand-pink rounded-full px-5 py-3 font-mono text-sm uppercase font-bold outline-none">
                <button type="submit" class="bg-brand-pink hover:bg-brand-pinkHover text-white font-bold px-6 py-3 rounded-full text-sm transition">
                    Cari Nota
                </button>
            </form>
        </div>

        <!-- Hasil Pencarian Nota -->
        @if(isset($rental) && $rental)
            <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-xs">
                <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-5">
                    <div>
                        <span class="text-[11px] font-bold uppercase text-gray-400">Kode Transaksi</span>
                        <h2 class="text-xl font-mono font-extrabold text-brand-ink">{{ $rental->booking_code }}</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ ($rental->payment->status ?? '') === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ ($rental->payment->status ?? '') === 'paid' ? 'QRIS Lunas' : 'Belum Dibayar' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-6">
                    <div>
                        <span class="text-gray-400 block">Nama Penyewa</span>
                        <strong class="text-sm">{{ $rental->customer_name }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block">No. WhatsApp</span>
                        <strong class="text-sm">{{ $rental->customer_phone }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Unit Kendaraan</span>
                        <strong class="text-sm">{{ $rental->vehicle->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Jadwal & Durasi</span>
                        <strong class="text-sm">{{ \Carbon\Carbon::parse($rental->pickup_date)->format('d M Y') }} ({{ $rental->duration_days }} Hari)</strong>
                    </div>
                </div>

                @if($rental->status === 'pending')
                    <form action="{{ route('admin.handover', $rental->id) }}" method="POST" class="border-t border-gray-100 pt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor NIK KTP Asli (Jaminan)</label>
                            <input type="text" name="ktp_number" required maxlength="20" placeholder="Masukkan 16 digit NIK KTP penyewa"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-brand-pink">
                        </div>

                        <label class="flex items-center gap-2.5 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" name="ktp_verified" value="1" required class="w-4 h-4 accent-brand-pink">
                            <span>KTP asli telah diterima dan dicek sesuai wajah & nama pemesan.</span>
                        </label>

                        <button type="submit" class="w-full bg-brand-pink hover:bg-brand-pinkHover text-white font-bold py-3.5 rounded-full text-sm shadow-sm transition">
                            Verifikasi & Serahkan Kunci (Kurangi Stok)
                        </button>
                    </form>
                @else
                    <div class="bg-brand-canvas rounded-2xl p-4 text-center text-xs font-semibold text-gray-600">
                        Status transaksi ini sudah <span class="uppercase font-extrabold text-brand-ink">{{ $rental->status }}</span>.
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection