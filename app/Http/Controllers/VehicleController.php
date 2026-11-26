<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    /**
     * Menampilkan daftar armada & stok di Portal Admin
     */
    public function index()
    {
        $vehicles = Vehicle::with('category')->latest()->get();
        $categories = Category::all();

        return view('vehicles.index', compact('vehicles', 'categories'));
    }

    /**
     * Menyimpan unit kendaraan baru beserta 7-8 foto ke public/storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'spec'          => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'price_per_day' => 'required|numeric|min:0',
            'total_stock'   => 'required|integer|min:1',
            'images'        => 'required|array|min:1|max:10',
            'images.*'      => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // Simpan langsung ke storage/app/public/vehicles -> public/storage/vehicles
                $imagePaths[] = $file->store('vehicles', 'public');
            }
        }

        Vehicle::create([
            'category_id'     => $request->category_id,
            'name'            => $request->name,
            'spec'            => $request->spec,
            'license_plate'   => strtoupper($request->license_plate),
            'price_per_day'   => $request->price_per_day,
            'total_stock'     => $request->total_stock,
            'available_stock' => $request->total_stock,
            'status'          => 'tersedia',
            'images'          => $imagePaths,
        ]);

        return back()->with('success', 'Unit kendaraan & ' . count($imagePaths) . ' foto berhasil ditambahkan!');
    }

    /**
     * Memperbarui harga, stok unit, status, atau menambah foto baru
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'price_per_day'   => 'required|numeric|min:0',
            'total_stock'     => 'required|integer|min:1',
            'available_stock' => 'required|integer|min:0|lte:total_stock',
            'status'          => 'required|in:tersedia,habis,maintenance',
            'images.*'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePaths = $vehicle->images ?? [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('vehicles', 'public');
            }
        }

        // Sinkronisasi otomatis status jika bukan maintenance
        $status = $request->status;
        if ($status !== 'maintenance') {
            $status = $request->available_stock > 0 ? 'tersedia' : 'habis';
        }

        $vehicle->update([
            'price_per_day'   => $request->price_per_day,
            'total_stock'     => $request->total_stock,
            'available_stock' => $request->available_stock,
            'status'          => $status,
            'images'          => $imagePaths,
        ]);

        return back()->with('success', 'Data stok & informasi kendaraan berhasil diperbarui!');
    }

    /**
     * Menghapus unit kendaraan beserta file fotonya di storage
     */
    public function destroy(Vehicle $vehicle)
    {
        if (is_array($vehicle->images)) {
            foreach ($vehicle->images as $path) {
                if (!str_starts_with($path, 'http')) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $vehicle->delete();
        return back()->with('success', 'Data kendaraan berhasil dihapus.');
    }
}