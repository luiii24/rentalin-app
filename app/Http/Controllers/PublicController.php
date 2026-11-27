<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicController extends Controller
{
    /**
     * Mengelompokkan unit fisik berdasarkan nama model armada menjadi 1 item general
     * agar tidak tampil duplikat di halaman Beranda maupun Katalog.
     */
    private function groupVehiclesGeneral(Collection $rawVehicles): Collection
    {
        return $rawVehicles
            ->groupBy(fn ($v) => strtolower(trim((string) $v->name)))
            ->map(function (Collection $units) {
                $availableUnits = $units->filter(fn ($u) => $u->status === 'tersedia');
                $primaryUnit    = $availableUnits->first() ?? $units->first();

                $unitWithImages = $units->first(fn ($u) => is_array($u->images) && count($u->images) > 0);
                $images         = $unitWithImages ? $unitWithImages->images : [];

                $cleanSpec  = trim(explode(' | ', (string) ($primaryUnit->spec ?? ''))[0]);
                $availCount = $availableUnits->count();
                $totalCount = $units->count();
                $category   = $primaryUnit->category;

                return (object) [
                    'id'                 => $primaryUnit->id,
                    'available_unit_ids' => $availableUnits->pluck('id')->values()->all(),
                    'name'               => $primaryUnit->name,
                    'license_plate'      => $primaryUnit->license_plate,
                    'spec'               => $cleanSpec,
                    'clean_spec'         => $cleanSpec,
                    'price_per_day'      => (int) $primaryUnit->price_per_day,
                    'total_stock'        => $totalCount,
                    'available_stock'    => $availCount,
                    'status'             => $availCount > 0 ? 'tersedia' : 'habis',
                    'images'             => $images,
                    'category_id'        => $primaryUnit->category_id,
                    'category'           => (object) [
                        'id'   => $category?->id ?? 0,
                        'name' => $category?->name ?? 'Umum',
                        'slug' => $category?->slug ?? 'all',
                    ],
                ];
            })
            ->sortBy(fn ($item) => $item->available_stock > 0 ? 0 : 1)
            ->values();
    }

    public function home()
    {
        $categories  = Category::orderBy('id')->get();
        $rawVehicles = Vehicle::with('category')->orderBy('id')->get();
        $vehicles    = $this->groupVehiclesGeneral($rawVehicles);

        return view('welcome', compact('categories', 'vehicles'));
    }

    public function howToOrder()
    {
        return view('how-to-order');
    }

    public function catalog()
    {
        $categories  = Category::orderBy('id')->get();
        $rawVehicles = Vehicle::with('category')->orderBy('id')->get();
        $vehicles    = $this->groupVehiclesGeneral($rawVehicles);

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
            $requestedVehicle = Vehicle::findOrFail($validated['vehicle_id']);

            // Kunci unit yang dipilih jika masih tersedia
            $vehicle = Vehicle::where('id', $requestedVehicle->id)
                ->where('status', 'tersedia')
                ->lockForUpdate()
                ->first();

            // Jika unit utama kebetulan sudah dibooking, otomatis ambil unit lain dengan model yang sama
            if (!$vehicle) {
                $vehicle = Vehicle::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($requestedVehicle->name))])
                    ->where('status', 'tersedia')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$vehicle || $vehicle->available_stock < 1) {
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
                ->with('error', 'Maaf, seluruh unit untuk armada ini baru saja dipesan atau sedang tidak tersedia.');
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