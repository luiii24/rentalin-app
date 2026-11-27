<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $rentals = Rental::with(['vehicle', 'payment'])
            ->latest('id')
            ->get();

        $selectedCode = strtoupper(trim((string) $request->query('code', '')));

        return view('admin.transactions', compact('rentals', 'selectedCode'));
    }

    public function confirmPayment(Request $request, string $code)
    {
        $rental = Rental::with('payment')
            ->where('booking_code', strtoupper(trim($code)))
            ->firstOrFail();

        $payment = $rental->payment ?? Payment::create([
            'rental_id'      => $rental->id,
            'payment_method' => 'QRIS',
            'amount'         => $rental->total_price,
            'payment_status' => 'unpaid',
        ]);

        if (empty($payment->proof_image)) {
            return response()->json([
                'success' => false,
                'message' => 'Penyewa belum mengunggah foto bukti pembayaran QRIS.',
            ], 422);
        }

        if ($payment->payment_status !== 'paid') {
            $payment->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
            ]);

            if (Auth::check()) {
                $rental->update(['admin_id' => Auth::id()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran QRIS berhasil diverifikasi sah.',
        ]);
    }

    public function confirmPickup(Request $request, string $code)
    {
        $validated = $request->validate([
            'collateral_type' => ['required', 'string', 'max:100'],
            'collateral_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $rental = Rental::with(['payment', 'vehicle'])
            ->where('booking_code', strtoupper(trim($code)))
            ->firstOrFail();

        if ($rental->payment?->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Konfirmasi pembayaran terlebih dahulu sebelum menyerahkan unit.',
            ], 422);
        }

        DB::transaction(function () use ($request, $validated, $rental) {
            // Hapus file jaminan lama jika ada untuk booking code ini
            foreach (Storage::disk('public')->files('collaterals') as $oldFile) {
                if (str_starts_with(basename($oldFile), $rental->booking_code . '__')) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $safeType = Str::slug($validated['collateral_type'], '_');
            $ext      = strtolower($request->file('collateral_file')->getClientOriginalExtension() ?: 'jpg');
            $filename = $rental->booking_code . '__' . $safeType . '.' . $ext;

            $request->file('collateral_file')->storeAs('collaterals', $filename, 'public');

            $rental->update([
                'ktp_verified' => true,
                'status'       => 'active',
                'admin_id'     => Auth::id() ?? $rental->admin_id,
            ]);

            if ($rental->vehicle) {
                $rental->vehicle->update([
                    'available_stock' => 0,
                    'status'          => 'habis',
                ]);
            }
        });

        return response()->json([
            'success'         => true,
            'message'         => 'Unit berhasil diserahkan dan dokumen jaminan tersimpan.',
            'collateral_type' => $validated['collateral_type'],
        ]);
    }

    public function confirmReturn(string $code)
    {
        $rental = Rental::with('vehicle')
            ->where('booking_code', strtoupper(trim($code)))
            ->firstOrFail();

        if ($rental->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini tidak dalam status sedang disewa.',
            ], 422);
        }

        DB::transaction(function () use ($rental) {
            $rental->update([
                'status'   => 'completed',
                'admin_id' => Auth::id() ?? $rental->admin_id,
            ]);

            if ($rental->vehicle && $rental->vehicle->status !== 'maintenance') {
                $rental->vehicle->update([
                    'available_stock' => 1,
                    'status'          => 'tersedia',
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian unit selesai. Stok armada otomatis tersedia kembali.',
        ]);
    }
}