<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicController extends Controller
{
    public function home()
    {
        $categories = Category::orderBy('id')->get();
        $vehicles   = Vehicle::with('category')
            ->orderByRaw("CASE WHEN status = 'tersedia' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        return view('welcome', compact('categories', 'vehicles'));
    }

    public function howToOrder()
    {
        return view('how-to-order');
    }

    public function catalog()
    {
        $categories = Category::orderBy('id')->get();
        $vehicles   = Vehicle::with('category')
            ->orderByRaw("CASE WHEN status = 'tersedia' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        return view('catalog', compact('categories', 'vehicles'));
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'     => ['required', 'exists:vehicles,id'],
            'customer_name'  => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'pickup_date'    => ['required', 'date'],
            'pickup_time'    => ['nullable', 'string', 'max:10'],
            'duration_days'  => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $pickupDateTime = Carbon::parse($validated['pickup_date']);
        if (!empty($validated['pickup_time'])) {
            $pickupDateTime = Carbon::parse($validated['pickup_date'] . ' ' . $validated['pickup_time']);
        }

        $rental = DB::transaction(function () use ($validated, $pickupDateTime) {
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($vehicle->status !== 'tersedia' || $vehicle->available_stock < 1) {
                return null;
            }

            $durationDays = (int) $validated['duration_days'];
            $totalPrice   = $vehicle->price_per_day * $durationDays;

            $rental = Rental::create([
                'vehicle_id'     => $vehicle->id,
                'customer_name'  => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'pickup_date'    => $pickupDateTime,
                'duration_days'  => $durationDays,
                'total_price'    => $totalPrice,
                'ktp_verified'   => false,
                'status'         => 'pending',
            ]);

            Payment::create([
                'rental_id'      => $rental->id,
                'payment_method' => 'QRIS',
                'amount'         => $totalPrice,
                'payment_status' => 'unpaid',
            ]);

            // Kunci unit agar tidak dibooking ganda selama menunggu pembayaran & verifikasi
            $newStock = max(0, $vehicle->available_stock - 1);
            $vehicle->update([
                'available_stock' => $newStock,
                'status'          => $newStock === 0 ? 'habis' : 'tersedia',
            ]);

            return $rental;
        });

        if (!$rental) {
            return back()
                ->withInput()
                ->with('error', 'Maaf, unit kendaraan ini baru saja dipesan atau sedang tidak tersedia.');
        }

        return redirect()->route('booking.invoice', $rental->booking_code);
    }

    public function invoice(string $code)
    {
        $cleanCode = strtoupper(trim($code));
        $rental = Rental::with(['vehicle.category', 'payment'])
            ->where('booking_code', $cleanCode)
            ->firstOrFail();

        $storeQrisUrl = null;
        foreach (Storage::disk('public')->files('settings') as $file) {
            if (str_starts_with(basename($file), 'store-qris.')) {
                $storeQrisUrl = asset('storage/' . $file) . '?v=' . Storage::disk('public')->lastModified($file);
                break;
            }
        }

        return view('admin.invoice', [
            'code'         => $rental->booking_code,
            'rental'       => $rental,
            'storeQrisUrl' => $storeQrisUrl,
        ]);
    }

    public function uploadPaymentProof(Request $request, string $code)
    {
        $cleanCode = strtoupper(trim($code));
        $rental = Rental::with('payment')
            ->where('booking_code', $cleanCode)
            ->firstOrFail();

        $payment = $rental->payment ?? Payment::create([
            'rental_id'      => $rental->id,
            'payment_method' => 'QRIS',
            'amount'         => $rental->total_price,
            'payment_status' => 'unpaid',
        ]);

        if ($payment->payment_status === 'paid' || !empty($payment->proof_image)) {
            return redirect()
                ->route('booking.invoice', $rental->booking_code)
                ->with('error', 'Bukti pembayaran sudah terkunci dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $payment->update([
            'payment_method' => 'QRIS|' . $path,
        ]);

        return redirect()
            ->route('booking.invoice', $rental->booking_code)
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
    }

    public function checkBooking(Request $request)
    {
        $query = trim((string) $request->input('code'));
        if ($query === '') {
            return back()->with('error', 'Masukkan kode nota (RNTL-...) atau nomor WhatsApp.');
        }

        $rental = Rental::where('booking_code', strtoupper($query))
            ->orWhere('customer_phone', $query)
            ->latest('id')
            ->first();

        if (!$rental) {
            return back()->with('error', "Nota dengan kode atau nomor \"{$query}\" tidak ditemukan.");
        }

        return redirect()->route('booking.invoice', $rental->booking_code);
    }
}