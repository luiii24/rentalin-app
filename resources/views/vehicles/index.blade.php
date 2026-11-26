@extends('layouts.admin')

@section('title', 'Kelola Armada Kendaraan')
@section('page-title', 'Kelola Armada Kendaraan')

@section('content')
    @php
        $categorySelectOptions = ['all' => 'Semua Kategori'];
        $formCategoryOptions = [];
        foreach ($categories as $cat) {
            $categorySelectOptions[$cat->slug] = $cat->name;
            $formCategoryOptions[$cat->id] = $cat->name;
        }
    @endphp

    <!-- HEADER HALAMAN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-brand-ink">Daftar Armada</h1>
            <p class="text-xs text-gray-400 mt-0.5">Klik judul kolom bertanda panah untuk mengurutkan data unit</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="RentalinUI.openModal('categoryModal')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-white hover:bg-brand-canvas border border-gray-200/80 text-brand-ink text-xs font-extrabold transition shadow-2xs cursor-pointer">
                <i class="bi bi-tag-fill text-brand-pink"></i> Kelola Kategori
            </button>

            <button type="button" onclick="openVehicleModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition shadow-xs cursor-pointer">
                <i class="bi bi-plus-lg"></i> Tambah Armada
            </button>
        </div>
    </div>

    <!-- BARIS GLOBAL SEARCH BAR & FILTER -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 flex-1">
            <x-search-bar
                id="fleet-search-input"
                placeholder="Cari nama kendaraan, plat nomor, kategori, warna..."
                oninput="filterFleetTable"
                widthClass="w-full sm:max-w-md"
            />

            <div class="flex items-center gap-2.5">
                <div class="w-44">
                    <x-select
                        name="fleet_cat_filter"
                        id="fleet-filter-category"
                        :options="$categorySelectOptions"
                        selected="all"
                        onchange="filterFleetTable"
                    />
                </div>
                <div class="w-40">
                    <x-select
                        name="fleet_status_filter"
                        id="fleet-filter-status"
                        :options="[
                            'all' => 'Semua Status',
                            'tersedia' => 'Tersedia',
                            'disewa' => 'Disewa',
                            'maintenance' => 'Maintenance',
                        ]"
                        selected="all"
                        onchange="filterFleetTable"
                    />
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-400">
            Menampilkan <strong id="fleet-visible-count" class="text-brand-ink">{{ count($vehicles) }}</strong> dari <strong class="text-brand-ink">{{ count($vehicles) }}</strong> unit
        </p>
    </div>

    <!-- TABEL ARMADA GLOBAL -->
    <x-table id="vehicles-table" :headers="[
        ['label' => 'Nama Kendaraan', 'sortable' => true],
        ['label' => 'Kategori', 'sortable' => true],
        ['label' => 'Tarif / Hari', 'sortable' => true],
        ['label' => 'Stok Tersedia', 'sortable' => true],
        ['label' => 'Status', 'sortable' => true],
        ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
    ]">
        @foreach($vehicles as $vehicle)
            @php
                $thumb = is_array($vehicle->images) && count($vehicle->images) > 0
                    ? (str_starts_with($vehicle->images[0], 'http') ? $vehicle->images[0] : asset('storage/' . $vehicle->images[0]))
                    : 'https://placehold.co/120x90/f3f4f6/9ca3af?text=Unit';
                $catSlug = $vehicle->category->slug ?? 'all';
                $catName = $vehicle->category->name ?? 'Umum';
                $plate   = $vehicle->license_plate ?? '-';
                $metaStr = ($vehicle->spec ?? '') . ($vehicle->color ? ' • ' . $vehicle->color : '') . ($vehicle->year ? ' (' . $vehicle->year . ')' : '');
                $searchHaystack = strtolower($vehicle->name . ' ' . $plate . ' ' . $catName . ' ' . $metaStr);
            @endphp
            <tr class="vehicle-row hover:bg-brand-canvas/40 transition"
                data-category="{{ $catSlug }}"
                data-status="{{ $vehicle->status }}"
                data-search="{{ $searchHaystack }}">
                <td class="py-3.5 pl-5 pr-2">
                    <input type="checkbox" class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $vehicle->name }}">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ $thumb }}" alt="{{ $vehicle->name }}"
                             class="w-12 h-10 rounded-xl object-cover bg-brand-canvas border border-gray-200/70 flex-shrink-0">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-extrabold text-brand-ink text-xs sm:text-sm">{{ $vehicle->name }}</span>
                                <x-badge variant="plate" class="!text-[10px] !py-0.5 !px-2">{{ $plate }}</x-badge>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $metaStr }}</p>
                        </div>
                    </div>
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $catName }}">
                    <x-badge variant="neutral">{{ $catName }}</x-badge>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $vehicle->price_per_day }}" data-format-rupiah="{{ $vehicle->price_per_day }}">
                    Rp {{ number_format($vehicle->price_per_day, 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-4 font-bold text-brand-ink whitespace-nowrap" data-sort="{{ $vehicle->available_stock }}">
                    {{ $vehicle->available_stock }} / {{ $vehicle->total_stock }} Unit
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $vehicle->status }}">
                    @if($vehicle->status === 'tersedia')
                        <x-badge variant="success" :dot="true">Tersedia</x-badge>
                    @elseif($vehicle->status === 'maintenance')
                        <x-badge variant="warning" :dot="true">Maintenance</x-badge>
                    @else
                        <x-badge variant="info" :dot="true">Disewa</x-badge>
                    @endif
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button"
                            onclick='openVehicleModal(@json($vehicle))'
                            title="Edit Unit"
                            class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-yellowSoft text-brand-ink flex items-center justify-center transition cursor-pointer">
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </button>

                        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST"
                              onsubmit="return confirm('Hapus unit {{ addslashes($vehicle->name) }} ({{ $plate }})?')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Unit"
                                class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                                <i class="bi bi-trash-fill text-xs"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        <tr id="fleet-empty-row" data-empty-row="true" class="hidden">
            <td colspan="7" class="py-10 text-center text-xs text-gray-400">
                Tidak ada unit armada yang sesuai dengan kata kunci atau filter.
            </td>
        </tr>
    </x-table>

    <!-- MODAL TAMBAH / EDIT ARMADA -->
    <div id="vehicleFormModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="veh-modal-title" class="text-base font-extrabold text-brand-ink">Tambah Unit Armada</h3>
                    <p class="text-[11px] text-gray-400">1 unit fisik mewakili 1 plat nomor unik</p>
                </div>
                <button type="button" onclick="RentalinUI.closeModal('vehicleFormModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="vehicle_id" id="veh-id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Nama Kendaraan</label>
                        <input type="text" name="name" id="veh-name" required placeholder="Contoh: Toyota Avanza Veloz"
                               class="w-full rounded-2xl px-4 py-2.5 text-xs font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Plat Nomor</label>
                        <input type="text" name="license_plate" id="veh-plate" required placeholder="Contoh: L 1428 AB"
                               class="w-full rounded-2xl px-4 py-2.5 text-xs font-mono font-extrabold uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <x-select
                        name="category_id"
                        id="veh-category"
                        label="Kategori"
                        :options="$formCategoryOptions"
                    />
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Tarif Sewa / Hari (Rp)</label>
                        <input type="number" name="price_per_day" id="veh-price" required placeholder="350000"
                               class="w-full rounded-2xl px-4 py-2.5 text-xs font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Warna &amp; Tahun</label>
                        <input type="text" name="color_year" id="veh-color" placeholder="Putih Mutiara (2023)"
                               class="w-full rounded-2xl px-4 py-2.5 text-xs font-semibold">
                    </div>
                    <x-select
                        name="status"
                        id="veh-status"
                        label="Status Unit"
                        :options="[
                            'tersedia' => 'Tersedia',
                            'disewa' => 'Sedang Disewa',
                            'maintenance' => 'Maintenance',
                        ]"
                        selected="tersedia"
                    />
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Spesifikasi Singkat</label>
                    <input type="text" name="spec" id="veh-spec" required placeholder="Automatic • 7 Kursi • Bensin"
                           class="w-full rounded-2xl px-4 py-2.5 text-xs font-semibold">
                </div>

                <div class="flex items-center gap-2.5 pt-2">
                    <button type="button" onclick="RentalinUI.closeModal('vehicleFormModal')"
                        class="flex-1 py-3 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition cursor-pointer">
                        Simpan Unit Armada
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KELOLA KATEGORI -->
    <div id="categoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-extrabold text-brand-ink">Kategori Armada</h3>
                <button type="button" onclick="RentalinUI.closeModal('categoryModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="space-y-2 mb-4">
                @foreach($categories as $cat)
                    <div class="flex items-center justify-between bg-brand-canvas rounded-2xl px-4 py-2.5 text-xs">
                        <span class="font-bold text-brand-ink">{{ $cat->name }}</span>
                        <span class="font-mono text-[11px] text-gray-400">{{ $cat->slug }}</span>
                    </div>
                @endforeach
            </div>

            <button type="button" onclick="RentalinUI.closeModal('categoryModal')"
                class="w-full py-2.5 rounded-full bg-brand-pink text-white text-xs font-extrabold transition cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <script>
        function filterFleetTable() {
            const catVal = document.getElementById('fleet-filter-category')?.value || 'all';
            const statusVal = document.getElementById('fleet-filter-status')?.value || 'all';

            RentalinUI.filterItems({
                inputId: 'fleet-search-input',
                clearBtnId: 'fleet-search-input-clear',
                itemSelector: '#vehicles-table tbody tr.vehicle-row',
                countId: 'fleet-visible-count',
                emptyId: 'fleet-empty-row',
                extraPredicate: (row) => {
                    const matchCat = catVal === 'all' || row.dataset.category === catVal;
                    const matchStatus = statusVal === 'all' || row.dataset.status === statusVal;
                    return matchCat && matchStatus;
                },
            });
        }

        function openVehicleModal(vehicle = null) {
            document.getElementById('veh-modal-title').innerText = vehicle ? 'Edit Unit Armada' : 'Tambah Unit Armada';
            document.getElementById('veh-id').value = vehicle?.id || '';
            document.getElementById('veh-name').value = vehicle?.name || '';
            document.getElementById('veh-plate').value = vehicle?.license_plate || '';
            document.getElementById('veh-price').value = vehicle?.price_per_day || '';
            document.getElementById('veh-color').value = vehicle?.color ? `${vehicle.color}${vehicle.year ? ' (' + vehicle.year + ')' : ''}` : '';
            document.getElementById('veh-spec').value = vehicle?.spec || '';

            if (vehicle?.category?.id) {
                RentalinUI.setSelectValue('veh-category', String(vehicle.category.id));
            }
            RentalinUI.setSelectValue('veh-status', vehicle?.status || 'tersedia');
            RentalinUI.openModal('vehicleFormModal');
        }
    </script>
@endsection