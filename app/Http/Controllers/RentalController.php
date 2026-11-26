<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RentalController extends Controller
{
    /**
     * 1. Halaman Utama Publik (Menampilkan armada langsung dari MySQL)
     */
    public function landing()
    {
        $categories = Category::all();
        $vehicles = Vehicle::with('category')->latest()->get();

        return view('welcome', compact('categories', 'vehicles'));
    }

    /**
     * 2. Proses Pemesanan Tanpa Login (Guest Checkout) -> Terbit Nota & QRIS
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'pickup_date'    => 'required|date',
            'duration_days'  => 'required|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($request) {
            $vehicle = Vehicle::lockForUpdate()->findOrFail($request->vehicle_id);

            // Pastikan stok masih tersedia dan tidak sedang maintenance
            if ($vehicle->available_stock < 1 || $vehicle->status !== 'tersedia') {
                return back()->with('error', 'Maaf, stok unit kendaraan ini baru saja habis.');
            }

            $totalPrice = $vehicle->price_per_day * $request->duration_days;
            $bookingCode = 'RNTL-' . strtoupper(Str::random(6));

            // Simpan ke Tabel Transaksi Rentals
            $rental = Rental::create([
                'booking_code'   => $bookingCode,
                'vehicle_id'     => $vehicle->id,
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'pickup_date'    => $request->pickup_date,
                'duration_days'  => $request->duration_days,
                'total_price'    => $totalPrice,
                'status'         => 'pending',
            ]);

            // Simpan ke Tabel Transaksi Payments (Tagihan QRIS)
            Payment::create([
                'rental_id'      => $rental->id,
                'payment_method' => 'QRIS',
                'amount'         => $totalPrice,
                'payment_status' => 'unpaid',
            ]);

            return redirect()->route('admin.invoice', $bookingCode);
        });
    }

    /**
     * 3. Halaman Nota Digital (QRIS + Barcode + Tombol Simpan PDF)
     */
    public function invoice(string $booking_code)
    {
        $rental = Rental::with(['vehicle.category', 'payment'])
            ->where('booking_code', strtoupper($booking_code))
            ->firstOrFail();

        return view('rentals.invoice', compact('rental'));
    }

    /**
     * 4. Simulasi / Konfirmasi Pelunasan QRIS oleh Pelanggan
     */
    public function confirmQris(string $booking_code)
    {
        $rental = Rental::with('payment')
            ->where('booking_code', strtoupper($booking_code))
            ->firstOrFail();

        $rental->payment->update([
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ]);

        return back()->with('success', 'Pembayaran QRIS berhasil dikonfirmasi! Nota siap ditunjukkan di lokasi.');
    }

    /**
     * 5. Dashboard & Halaman Scan Nota di Portal Admin
     */
    public function dashboard()
    {
        $totalAvailable = Vehicle::where('status', 'tersedia')->sum('available_stock');
        $activeRentals  = Rental::where('status', 'active')->count();
        $pendingNotas   = Rental::where('status', 'pending')->count();
        $recentRentals  = Rental::with(['vehicle', 'payment'])->latest()->take(10)->get();

        return view('dashboard', compact('totalAvailable', 'activeRentals', 'pendingNotas', 'recentRentals'));
    }

    /**
     * 6. Cari & Validasi Kode Booking dari Barcode Scanner Kasir
     */
    public function scanSearch(Request $request)
    {
        $scannedRental = null;
        if ($request->filled('booking_code')) {
            $scannedRental = Rental::with(['vehicle.category', 'payment'])
                ->where('booking_code', strtoupper(trim($request->booking_code)))
                ->first();

            if (!$scannedRental) {
                return back()->with('error', 'Kode Booking tidak ditemukan di sistem!');
            }
        }

        return view('rentals.scan', compact('scannedRental'));
    }

    /**
     * 7. Serah Terima Kunci di Lokasi -> Validasi Lunas + KTP -> Kurangi Stok Unit
     */
    public function handover(Request $request, Rental $rental)
    {
        $request->validate([
            'ktp_verified' => 'accepted',
        ], [
            'ktp_verified.accepted' => 'Wajib mencentang bukti penerimaan KTP asli penyewa!',
        ]);

        if ($rental->payment->payment_status !== 'paid') {
            return back()->with('error', 'Nota belum dibayar! Transaksi tidak dapat diproses.');
        }

        if ($rental->status !== 'pending') {
            return back()->with('error', 'Nota ini sudah pernah diproses sebelumnya.');
        }

        DB::transaction(function () use ($rental) {
            $rental->update([
                'admin_id'     => auth()->id() ?? 1,
                'ktp_verified' => true,
                'status'       => 'active',
            ]);

            // Kurangi stok unit & ubah status otomatis jika stok menyentuh 0
            $rental->vehicle->decrementStock(1);
        });

        return redirect()->route('portal.scan')->with('success', 'Nota valid & KTP terverifikasi. Stok unit otomatis dikurangi!');
    }

    /**
     * 8. Pengembalian Kendaraan -> Selesaikan Transaksi & Kembalikan Stok Unit
     */
    public function complete(Rental $rental)
    {
        if ($rental->status !== 'active') {
            return back()->with('error', 'Transaksi ini tidak dalam status aktif.');
        }

        DB::transaction(function () use ($rental) {
            $rental->update(['status' => 'completed']);
            $rental->vehicle->incrementStock(1);
        });

        return back()->with('success', 'Kendaraan telah dikembalikan. Stok unit otomatis bertambah kembali!');
    }
}