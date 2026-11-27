<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('id')->get();
        $vehicles   = Vehicle::with('category')->orderBy('id')->get();

        return view('admin.vehicles', compact('categories', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => ['required', 'string', 'max:150'],
            'category_id'             => ['required', 'exists:categories,id'],
            'price_per_day'           => ['required', 'numeric', 'min:0'],
            'spec'                    => ['required', 'string', 'max:255'],
            'images.*'                => ['nullable', 'image', 'max:5120'],
            'units'                   => ['required', 'array', 'min:1'],
            'units.*.license_plate'   => ['required', 'string', 'max:30'],
            'units.*.color_year'      => ['nullable', 'string', 'max:100'],
            'units.*.status'          => ['required', 'in:tersedia,disewa,maintenance'],
        ]);

        $originalName = trim((string) ($request->input('original_name') ?: $request->input('name')));
        $existingGroup = Vehicle::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($originalName)])->get();

        // Gunakan foto lama jika admin tidak mengunggah foto baru saat edit
        $imagePaths = $existingGroup->first()?->images ?? [];
        if ($request->hasFile('images')) {
            $uploaded = [];
            foreach ($request->file('images') as $file) {
                $uploaded[] = $file->store('vehicles', 'public');
            }
            if (count($uploaded) > 0) {
                $imagePaths = $uploaded;
            }
        }

        DB::transaction(function () use ($request, $existingGroup, $imagePaths) {
            $keptIds = [];

            foreach ($request->input('units', []) as $idx => $unitData) {
                $unitId   = !empty($unitData['unit_id']) ? (int) $unitData['unit_id'] : null;
                $plate    = strtoupper(trim((string) ($unitData['license_plate'] ?? '')));
                $colorYr  = trim((string) ($unitData['color_year'] ?? ''));
                $uiStatus = $unitData['status'] ?? 'tersedia';

                if ($plate === '' || $plate === '-') {
                    $plate = 'UNIT-' . strtoupper(Str::random(5)) . '-' . ($idx + 1);
                }

                // Gabungkan spesifikasi utama & warna/tahun ke kolom spec
                $fullSpec = trim((string) $request->input('spec'));
                if ($colorYr !== '') {
                    $fullSpec .= ' | ' . $colorYr;
                }

                // Pemetaan status UI ke enum database ['tersedia', 'habis', 'maintenance']
                $dbStatus   = $uiStatus === 'disewa' ? 'habis' : $uiStatus;
                $availStock = $uiStatus === 'tersedia' ? 1 : 0;

                $vehicle = $unitId ? Vehicle::find($unitId) : null;
                if (!$vehicle) {
                    $vehicle = Vehicle::where('license_plate', $plate)->first();
                }

                $payload = [
                    'category_id'     => (int) $request->input('category_id'),
                    'name'            => trim((string) $request->input('name')),
                    'license_plate'   => $plate,
                    'spec'            => $fullSpec,
                    'price_per_day'   => (int) $request->input('price_per_day'),
                    'total_stock'     => 1,
                    'available_stock' => $availStock,
                    'status'          => $dbStatus,
                    'images'          => $imagePaths,
                ];

                if ($vehicle) {
                    $vehicle->update($payload);
                } else {
                    $vehicle = Vehicle::create($payload);
                }

                $keptIds[] = $vehicle->id;
            }

            // Hapus unit fisik yang dihapus dari daftar unit di modal (jika tidak sedang disewa aktif)
            if ($existingGroup->isNotEmpty()) {
                $toDelete = $existingGroup->whereNotIn('id', $keptIds);
                foreach ($toDelete as $oldUnit) {
                    if (!$oldUnit->rentals()->where('status', 'active')->exists()) {
                        $oldUnit->delete();
                    }
                }
            }
        });

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Data armada dan daftar unit berhasil disimpan ke database.');
    }

    public function destroy($id)
    {
        $target = Vehicle::findOrFail($id);
        $groupUnits = Vehicle::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($target->name))])->get();

        foreach ($groupUnits as $unit) {
            if ($unit->rentals()->where('status', 'active')->exists()) {
                return back()->with('error', "Armada {$target->name} ({$unit->license_plate}) masih dalam masa sewa aktif.");
            }
        }

        Vehicle::whereIn('id', $groupUnits->pluck('id'))->delete();

        return back()->with('success', "Armada {$target->name} beserta seluruh unitnya berhasil dihapus.");
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'kategori';
        $slug     = $baseSlug;
        $counter  = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category = Category::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
        ]);

        return response()->json([
            'success'  => true,
            'category' => $category,
        ]);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: $category->slug;
        $slug     = $baseSlug;
        $counter  = 2;

        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category->update([
            'name' => trim($validated['name']),
            'slug' => $slug,
        ]);

        return response()->json([
            'success'  => true,
            'category' => $category,
        ]);
    }

    public function destroyCategory(Category $category)
    {
        if ($category->vehicles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh armada.',
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true]);
    }
}