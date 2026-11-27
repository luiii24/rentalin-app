<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'revenue'     => (int) Payment::where('payment_status', 'paid')->sum('amount'),
            'rented'      => (int) Rental::where('status', 'active')->count(),
            'pending'     => (int) Rental::where('status', 'pending')->count(),
            'available'   => (int) Vehicle::where('status', 'tersedia')->sum('available_stock'),
            'maintenance' => (int) Vehicle::where('status', 'maintenance')->count(),
            'total_fleet' => (int) Vehicle::count(),
        ];

        $recentRentals = Rental::with(['vehicle', 'payment'])
            ->latest('id')
            ->take(5)
            ->get();

        $storeQrisUrl = $this->getStoreQrisUrl();

        return view('admin.dashboard', compact('stats', 'recentRentals', 'storeQrisUrl'));
    }

    public function uploadQris(Request $request)
    {
        $request->validate([
            'qris_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        foreach (Storage::disk('public')->files('settings') as $file) {
            if (str_starts_with(basename($file), 'store-qris.')) {
                Storage::disk('public')->delete($file);
            }
        }

        $ext = strtolower($request->file('qris_image')->getClientOriginalExtension() ?: 'png');
        $request->file('qris_image')->storeAs('settings', 'store-qris.' . $ext, 'public');

        return back()->with('success', 'Foto QRIS pembayaran berhasil diperbarui dan otomatis tampil di semua Nota Digital.');
    }

    public function resetQris()
    {
        foreach (Storage::disk('public')->files('settings') as $file) {
            if (str_starts_with(basename($file), 'store-qris.')) {
                Storage::disk('public')->delete($file);
            }
        }

        return back()->with('success', 'Foto QRIS dikembalikan ke QRIS default.');
    }

    private function getStoreQrisUrl(): ?string
    {
        foreach (Storage::disk('public')->files('settings') as $file) {
            if (str_starts_with(basename($file), 'store-qris.')) {
                return asset('storage/' . $file) . '?v=' . Storage::disk('public')->lastModified($file);
            }
        }

        return null;
    }
}