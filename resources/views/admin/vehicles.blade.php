@extends('layouts.admin')

@section('title', 'Kelola Armada Kendaraan')
@section('page-title', 'Kelola Armada Kendaraan')

@section('content')
    @php
        $categorySelectOptions = ['all' => 'Semua Kategori'];
        $formCategoryOptions = [];
        $initialCategories = [];

        foreach ($categories as $cat) {
            $cId   = is_array($cat) ? ($cat['id'] ?? 0) : ($cat->id ?? 0);
            $cName = is_array($cat) ? ($cat['name'] ?? '') : ($cat->name ?? '');
            $cSlug = is_array($cat) ? ($cat['slug'] ?? '') : ($cat->slug ?? '');

            $categorySelectOptions[$cSlug] = $cName;
            $formCategoryOptions[$cId] = $cName;
            $initialCategories[] = [
                'id'   => $cId,
                'name' => $cName,
                'slug' => $cSlug,
            ];
        }
        $firstCategoryId = array_key_first($formCategoryOptions);

        // Kelompokkan unit fisik berdasarkan nama model armada
        $groupedVehicles = [];
        foreach ($vehicles as $v) {
            $vName      = is_array($v) ? ($v['name'] ?? '') : ($v->name ?? '');
            $groupKey   = strtolower(trim($vName));

            $vId        = is_array($v) ? ($v['id'] ?? 0) : ($v->id ?? 0);
            $vPlate     = is_array($v) ? ($v['license_plate'] ?? '-') : ($v->license_plate ?? '-');
            $rawStatus  = is_array($v) ? ($v['status'] ?? 'tersedia') : ($v->status ?? 'tersedia');
            $vStatus    = $rawStatus === 'habis' ? 'disewa' : $rawStatus;

            $vSpec      = is_array($v)
                ? (explode(' | ', $v['spec'] ?? '')[0] ?? '')
                : ($v->clean_spec ?? ($v->spec ?? ''));
            $vColorYear = is_array($v)
                ? (explode(' | ', $v['spec'] ?? '')[1] ?? ($v['color_year'] ?? ''))
                : ($v->color_year ?? '');

            $vPrice     = is_array($v) ? ($v['price_per_day'] ?? 0) : ($v->price_per_day ?? 0);
            $vImages    = is_array($v) ? ($v['images'] ?? []) : (is_array($v->images) ? $v->images : []);
            $vCatId     = is_array($v) ? ($v['category_id'] ?? ($v['category']['id'] ?? '')) : ($v->category_id ?? ($v->category->id ?? ''));
            $vCatName   = is_array($v) ? ($v['category']['name'] ?? 'Umum') : ($v->category->name ?? 'Umum');
            $vCatSlug   = is_array($v) ? ($v['category']['slug'] ?? 'all') : ($v->category->slug ?? 'all');

            if (!isset($groupedVehicles[$groupKey])) {
                $groupedVehicles[$groupKey] = [
                    'id'            => $vId,
                    'group_key'     => $groupKey,
                    'name'          => $vName,
                    'category_id'   => $vCatId,
                    'category_name' => $vCatName,
                    'category_slug' => $vCatSlug,
                    'price_per_day' => $vPrice,
                    'spec'          => $vSpec,
                    'images'        => $vImages,
                    'units'         => [],
                ];
            }

            $groupedVehicles[$groupKey]['units'][] = [
                'unit_id'       => $vId,
                'license_plate' => $vPlate,
                'color_year'    => $vColorYear,
                'status'        => $vStatus,
            ];
        }
        $groupedVehiclesList = array_values($groupedVehicles);
    @endphp

    <!-- HEADER HALAMAN -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-brand-ink">Daftar Armada</h1>
            <p class="text-xs text-gray-400 mt-0.5">1 baris armada mencakup seluruh unit fisik, plat nomor, serta rekap status general</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="openCategoryModal(false)"
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
            Menampilkan <strong id="fleet-visible-count" class="text-brand-ink">{{ count($groupedVehiclesList) }}</strong> dari <strong id="fleet-total-count" class="text-brand-ink">{{ count($groupedVehiclesList) }}</strong> armada
        </p>
    </div>

    <!-- TABEL ARMADA GLOBAL -->
    <x-table id="vehicles-table" :headers="[
        ['label' => 'Armada & Daftar Unit', 'sortable' => true],
        ['label' => 'Kategori', 'sortable' => true],
        ['label' => 'Tarif / Hari', 'sortable' => true],
        ['label' => 'Stok Unit', 'sortable' => true],
        ['label' => 'Status General', 'sortable' => true],
        ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
    ]">
        @foreach($groupedVehiclesList as $group)
            @php
                $thumb = count($group['images']) > 0
                    ? (str_starts_with($group['images'][0], 'http') ? $group['images'][0] : asset('storage/' . $group['images'][0]))
                    : 'https://placehold.co/120x90/f3f4f6/9ca3af?text=Unit';
                $totalUnits  = count($group['units']);
                $availUnits  = collect($group['units'])->where('status', 'tersedia')->count();
                $rentedUnits = collect($group['units'])->where('status', 'disewa')->count();
                $maintUnits  = collect($group['units'])->where('status', 'maintenance')->count();

                $allPlates  = collect($group['units'])->pluck('license_plate')->implode(' ');
                $allColors  = collect($group['units'])->pluck('color_year')->implode(' ');
                $searchHaystack = strtolower($group['name'] . ' ' . $allPlates . ' ' . $group['category_name'] . ' ' . $group['spec'] . ' ' . $allColors);
            @endphp
            <tr class="vehicle-row hover:bg-brand-canvas/40 transition"
                data-group-key="{{ $group['group_key'] }}"
                data-category="{{ $group['category_slug'] }}"
                data-category-id="{{ $group['category_id'] }}"
                data-avail="{{ $availUnits }}"
                data-rented="{{ $rentedUnits }}"
                data-maint="{{ $maintUnits }}"
                data-search="{{ $searchHaystack }}">
                <td class="py-3.5 pl-5 pr-2">
                    <input type="checkbox" class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>
                <td class="py-3.5 px-4" data-sort="{{ $group['name'] }}">
                    <div class="flex items-start gap-3.5">
                        <img src="{{ $thumb }}" alt="{{ $group['name'] }}"
                             class="w-12 h-10 rounded-xl object-cover bg-brand-canvas border border-gray-200/70 flex-shrink-0 mt-0.5">
                        <div class="min-w-0">
                            <span class="font-extrabold text-brand-ink text-xs sm:text-sm block">{{ $group['name'] }}</span>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $group['spec'] }}</p>
                            <div class="row-unit-badges flex flex-wrap items-center gap-1.5 mt-1.5">
                                @foreach($group['units'] as $u)
                                    <span class="inline-flex items-center gap-1 font-mono text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-brand-canvas border border-gray-200/80 text-brand-ink">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $u['status'] === 'tersedia' ? 'bg-emerald-500' : ($u['status'] === 'maintenance' ? 'bg-amber-500' : 'bg-sky-500') }}"></span>
                                        {{ $u['license_plate'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </td>
                <td class="py-3.5 px-4 vehicle-cat-cell" data-sort="{{ $group['category_name'] }}">
                    <x-badge variant="neutral">{{ $group['category_name'] }}</x-badge>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $group['price_per_day'] }}">
                    Rp {{ number_format($group['price_per_day'], 0, ',', '.') }}
                </td>
                <td class="py-3.5 px-4 font-bold text-brand-ink whitespace-nowrap row-stock-cell" data-sort="{{ $availUnits }}">
                    {{ $availUnits }} / {{ $totalUnits }} Unit
                </td>
                <td class="py-3.5 px-4 row-status-cell" data-sort="{{ $availUnits }}">
                    <div class="flex flex-wrap items-center gap-1.5">
                        @if($availUnits > 0)
                            <x-badge variant="success" :dot="true">Tersedia {{ $availUnits }}</x-badge>
                        @endif
                        @if($rentedUnits > 0)
                            <x-badge variant="info" :dot="true">Disewa {{ $rentedUnits }}</x-badge>
                        @endif
                        @if($maintUnits > 0)
                            <x-badge variant="warning" :dot="true">Maintenance {{ $maintUnits }}</x-badge>
                        @endif
                    </div>
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button"
                            onclick='openVehicleModal(@json($group))'
                            title="Edit Armada & Daftar Unit"
                            class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-yellowSoft text-brand-ink flex items-center justify-center transition cursor-pointer">
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </button>

                        <button type="button"
                            onclick="confirmDeleteVehicle('{{ route('admin.vehicles.destroy', $group['id']) }}', {{ json_encode($group['name']) }})"
                            title="Hapus Armada"
                            class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                            <i class="bi bi-trash-fill text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        <tr id="fleet-empty-row" data-empty-row="true" class="{{ count($groupedVehiclesList) === 0 ? '' : 'hidden' }}">
            <td colspan="7" class="py-10 text-center text-xs text-gray-400">
                Tidak ada armada yang sesuai dengan kata kunci atau filter.
            </td>
        </tr>
    </x-table>

    <!-- MODAL GLOBAL ARMADA + LIST UNIT DI BAWAH FORM -->
    <div id="vehicleFormModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="veh-modal-title" class="text-base font-extrabold text-brand-ink">Tambah Armada Kendaraan</h3>
                    <p class="text-[11px] text-gray-400">Isi informasi armada secara global, lalu kelola daftar unit (plat &amp; warna) di bawahnya</p>
                </div>
                <button type="button" onclick="RentalinUI.closeModal('vehicleFormModal')"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form id="vehicleFormElement" action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data"
                  onsubmit="submitVehicleFormAjax(event)" class="space-y-4">
                @csrf
                <input type="hidden" name="vehicle_id" id="veh-id">
                <input type="hidden" name="original_name" id="veh-original-name">
                <div id="veh-units-hidden-inputs"></div>

                <!-- BAGIAN 1: FORM GLOBAL ARMADA -->
                <div class="p-4 rounded-2xl bg-brand-canvas/50 border border-gray-200/70 space-y-3.5">
                    <div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-wider text-gray-400">
                        <i class="bi bi-car-front-fill text-brand-pink"></i>
                        <span>Informasi Global Armada</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Nama Kendaraan</label>
                            <input type="text" name="name" id="veh-name" required placeholder="Contoh: Toyota Avanza Veloz"
                                   class="w-full rounded-2xl px-4 py-2.5 text-xs font-semibold !bg-white">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-extrabold text-brand-ink">Kategori</label>
                                <button type="button" onclick="openCategoryModal(true)"
                                    class="inline-flex items-center gap-1 text-[11px] font-extrabold text-brand-pink hover:underline cursor-pointer">
                                    <i class="bi bi-pencil-square"></i> Atur / Tambah
                                </button>
                            </div>
                            <x-select
                                name="category_id"
                                id="veh-category"
                                :options="$formCategoryOptions"
                                :selected="$firstCategoryId"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Tarif Sewa / Hari (Rp)</label>
                            <input type="number" name="price_per_day" id="veh-price" required placeholder="350000"
                                   class="w-full rounded-2xl px-4 py-2.5 text-xs font-bold !bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Spesifikasi Singkat</label>
                            <input type="text" name="spec" id="veh-spec" required placeholder="Automatic • 7 Kursi • Bensin"
                                   class="w-full rounded-2xl px-4 py-2.5 text-xs font-semibold !bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Foto Armada (Opsional)</label>
                        <div id="veh-photo-preview-wrap" class="hidden flex items-center gap-2.5 mb-2 overflow-x-auto pb-1"></div>
                        <input type="file" name="images[]" id="veh-images" multiple accept="image/*"
                               onchange="previewSelectedVehiclePhotos(event)"
                               class="w-full rounded-2xl px-3.5 py-2 text-xs font-semibold !bg-white border border-gray-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-brand-pinkSoft file:text-brand-pink cursor-pointer">
                    </div>
                </div>

                <!-- BAGIAN 2: DAFTAR UNIT FISIK DI BAWAH FORM GLOBAL -->
                <div class="pt-1">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                        <div>
                            <h4 class="text-xs font-extrabold text-brand-ink flex items-center gap-2">
                                <span>Daftar Unit Fisik</span>
                                <span id="veh-unit-summary-badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600">
                                    1 Tersedia / 1 Unit
                                </span>
                            </h4>
                            <p class="text-[11px] text-gray-400">Setiap unit memiliki plat nomor, warna/tahun, dan kondisi kesiapan unit</p>
                        </div>

                        <button type="button" onclick="addVehicleUnitRow()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-brand-pinkSoft hover:bg-brand-pink text-brand-pink hover:text-white text-xs font-extrabold transition cursor-pointer">
                            <i class="bi bi-plus-circle-fill"></i> Tambah Unit
                        </button>
                    </div>

                    <div id="vehicle-units-list" class="space-y-2.5 max-h-60 overflow-y-auto pr-1"></div>
                </div>

                <!-- TOMBOL AKSI MODAL -->
                <div class="flex items-center gap-2.5 pt-2 border-t border-gray-100">
                    <button type="button" onclick="RentalinUI.closeModal('vehicleFormModal')"
                        class="flex-1 py-3 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-vehicle"
                        class="flex-[2] py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition cursor-pointer">
                        Simpan Armada &amp; Unit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KELOLA KATEGORI -->
    <div id="categoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-brand-ink">Kategori Armada</h3>
                    <p class="text-[11px] text-gray-400">Tambah baru, ubah nama, atau hapus kategori</p>
                </div>
                <button type="button" onclick="closeCategoryModal()"
                    class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form onsubmit="handleAddCategory(event)" class="mb-4">
                <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-400 mb-1.5">Tambah Kategori Baru</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="new-category-name" required placeholder="Contoh: Mobil SUV / Motor Sport"
                           class="flex-1 rounded-full px-4 py-2.5 text-xs font-bold">
                    <button type="submit"
                        class="px-4 py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition flex-shrink-0 cursor-pointer">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>
            </form>

            <div id="category-items-list" class="space-y-2 max-h-64 overflow-y-auto pr-1 mb-5"></div>

            <button type="button" onclick="closeCategoryModal()"
                class="w-full py-3 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold transition cursor-pointer">
                Selesai
            </button>
        </div>
    </div>

    <script>
        const STORAGE_BASE_URL = "{{ asset('storage') }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}";

        function escapeHtmlText(str) {
            return String(str || '').replace(/[&<>"']/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[m]));
        }

        window.fleetCategories = @json($initialCategories);
        window.editingCatId = null;
        window.openedCatFromVehicleModal = false;
        window.currentModalUnits = [];

        function renderCategoryListAndDropdowns(preferredVehCatId = null) {
            const listContainer = document.getElementById('category-items-list');
            if (listContainer) {
                if (window.fleetCategories.length === 0) {
                    listContainer.innerHTML = `<p class="text-center text-xs text-gray-400 py-4">Belum ada kategori. Tambahkan di atas.</p>`;
                } else {
                    listContainer.innerHTML = window.fleetCategories.map(cat => {
                        const isEditing = Number(window.editingCatId) === Number(cat.id);
                        if (isEditing) {
                            return `
                                <div class="flex items-center gap-2 bg-brand-canvas rounded-2xl px-3.5 py-2 text-xs border border-brand-pink/40">
                                    <input type="text" id="inline-edit-cat-${cat.id}" value="${escapeHtmlText(cat.name)}"
                                           onkeydown="if(event.key==='Enter'){event.preventDefault();saveInlineCategory(${cat.id});} if(event.key==='Escape'){cancelInlineCategory();}"
                                           class="flex-1 rounded-full px-3.5 py-1.5 text-xs font-bold !bg-white">
                                    <button type="button" onclick="saveInlineCategory(${cat.id})" title="Simpan"
                                        class="w-7 h-7 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 transition cursor-pointer">
                                        <i class="bi bi-check-lg text-xs"></i>
                                    </button>
                                    <button type="button" onclick="cancelInlineCategory()" title="Batal"
                                        class="w-7 h-7 rounded-full bg-white text-gray-500 hover:text-brand-ink flex items-center justify-center flex-shrink-0 transition cursor-pointer">
                                        <i class="bi bi-x-lg text-xs"></i>
                                    </button>
                                </div>
                            `;
                        }

                        return `
                            <div class="flex items-center justify-between gap-2 bg-brand-canvas rounded-2xl px-4 py-2.5 text-xs">
                                <div class="min-w-0">
                                    <span class="font-bold text-brand-ink block truncate">${escapeHtmlText(cat.name)}</span>
                                    <span class="font-mono text-[10px] text-gray-400">${escapeHtmlText(cat.slug)}</span>
                                </div>
                                <div class="inline-flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button" onclick="startInlineCategory(${cat.id})" title="Edit Kategori"
                                        class="w-7 h-7 rounded-full bg-white hover:bg-brand-yellowSoft text-brand-ink flex items-center justify-center transition cursor-pointer">
                                        <i class="bi bi-pencil-fill text-[11px]"></i>
                                    </button>
                                    <button type="button" onclick="removeCategory(${cat.id})" title="Hapus Kategori"
                                        class="w-7 h-7 rounded-full bg-white hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                                        <i class="bi bi-trash-fill text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            const vehInput = document.getElementById('veh-category');
            const vehGroup = vehInput?.closest('.custom-select-group');
            const vehMenu  = vehGroup?.querySelector('.custom-select-menu');
            if (vehMenu) {
                const currentVal = preferredVehCatId ?? vehInput.value ?? (window.fleetCategories[0]?.id ?? '');
                vehMenu.innerHTML = window.fleetCategories.map(cat => `
                    <button type="button"
                        data-value="${escapeHtmlText(cat.id)}"
                        data-label="${escapeHtmlText(cat.name)}"
                        onclick="RentalinUI.selectOption(this, this.dataset.value, this.dataset.label)"
                        class="custom-option w-full px-4 py-2.5 text-left text-xs font-semibold text-brand-ink hover:bg-brand-pinkSoft/60 hover:text-brand-pink flex items-center justify-between transition cursor-pointer">
                        <span>${escapeHtmlText(cat.name)}</span>
                        <i class="bi bi-check2 text-sm text-brand-pink hidden"></i>
                    </button>
                `).join('');

                const validTarget = window.fleetCategories.find(c => String(c.id) === String(currentVal))
                    ? currentVal
                    : (window.fleetCategories[0]?.id ?? '');

                if (validTarget !== '') {
                    RentalinUI.setSelectValue('veh-category', String(validTarget));
                }
            }

            const filterInput = document.getElementById('fleet-filter-category');
            const filterGroup = filterInput?.closest('.custom-select-group');
            const filterMenu  = filterGroup?.querySelector('.custom-select-menu');
            if (filterMenu) {
                const currentFilter = filterInput.value || 'all';
                const filterList = [{ slug: 'all', name: 'Semua Kategori' }, ...window.fleetCategories];

                filterMenu.innerHTML = filterList.map(item => `
                    <button type="button"
                        data-value="${escapeHtmlText(item.slug)}"
                        data-label="${escapeHtmlText(item.name)}"
                        onclick="RentalinUI.selectOption(this, this.dataset.value, this.dataset.label)"
                        class="custom-option w-full px-4 py-2.5 text-left text-xs font-semibold text-brand-ink hover:bg-brand-pinkSoft/60 hover:text-brand-pink flex items-center justify-between transition cursor-pointer">
                        <span>${escapeHtmlText(item.name)}</span>
                        <i class="bi bi-check2 text-sm text-brand-pink hidden"></i>
                    </button>
                `).join('');

                const validFilter = filterList.some(i => i.slug === currentFilter) ? currentFilter : 'all';
                RentalinUI.setSelectValue('fleet-filter-category', validFilter);
            }
        }

        function openCategoryModal(fromVehicleModal = false) {
            window.openedCatFromVehicleModal = fromVehicleModal;
            window.editingCatId = null;
            renderCategoryListAndDropdowns();
            RentalinUI.openModal('categoryModal');
            setTimeout(() => document.getElementById('new-category-name')?.focus(), 80);
        }

        function closeCategoryModal() {
            window.editingCatId = null;
            RentalinUI.closeModal('categoryModal');
        }

        async function handleAddCategory(e) {
            e.preventDefault();
            const input = document.getElementById('new-category-name');
            const name = (input?.value || '').trim();
            if (!name) return;

            try {
                const res = await fetch("{{ route('admin.categories.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ name }),
                });
                const data = await res.json();
                if (!res.ok) {
                    RentalinPopup.show({ type: 'failed', title: 'Gagal Menambah Kategori', message: data.message || 'Nama kategori sudah ada atau tidak valid.' });
                    return;
                }

                window.fleetCategories.push(data.category);
                input.value = '';
                renderCategoryListAndDropdowns(data.category.id);

                if (window.openedCatFromVehicleModal) {
                    closeCategoryModal();
                }

                RentalinPopup.show({
                    type: 'success',
                    title: 'Kategori Ditambahkan',
                    message: `Kategori "${data.category.name}" berhasil disimpan ke database.`,
                });
            } catch (err) {
                RentalinPopup.show({ type: 'failed', title: 'Kesalahan Jaringan', message: 'Tidak dapat terhubung ke server.' });
            }
        }

        function startInlineCategory(id) {
            window.editingCatId = id;
            renderCategoryListAndDropdowns();
            setTimeout(() => {
                const editInput = document.getElementById(`inline-edit-cat-${id}`);
                if (editInput) {
                    editInput.focus();
                    editInput.select();
                }
            }, 40);
        }

        function cancelInlineCategory() {
            window.editingCatId = null;
            renderCategoryListAndDropdowns();
        }

        async function saveInlineCategory(id) {
            const editInput = document.getElementById(`inline-edit-cat-${id}`);
            const newName = (editInput?.value || '').trim();
            if (!newName) return;

            try {
                const res = await fetch(`/portal/categories/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ name: newName }),
                });
                const data = await res.json();
                if (!res.ok) {
                    RentalinPopup.show({ type: 'failed', title: 'Gagal Memperbarui', message: data.message || 'Gagal memperbarui kategori.' });
                    return;
                }

                const target = window.fleetCategories.find(c => Number(c.id) === Number(id));
                if (target) {
                    target.name = data.category.name;
                    target.slug = data.category.slug;

                    document.querySelectorAll(`#vehicles-table tr.vehicle-row[data-category-id="${id}"]`).forEach(row => {
                        row.dataset.category = data.category.slug;
                        const badgeSpan = row.querySelector('.vehicle-cat-cell span');
                        if (badgeSpan) badgeSpan.innerText = data.category.name;
                    });
                }

                window.editingCatId = null;
                renderCategoryListAndDropdowns();
                RentalinPopup.show({ type: 'success', title: 'Kategori Diperbarui', message: `Nama kategori diubah menjadi "${data.category.name}".` });
            } catch (err) {
                RentalinPopup.show({ type: 'failed', title: 'Kesalahan Jaringan', message: 'Tidak dapat terhubung ke server.' });
            }
        }

        function removeCategory(id) {
            const target = window.fleetCategories.find(c => Number(c.id) === Number(id));
            if (!target) return;

            RentalinPopup.confirm({
                type: 'warning',
                title: 'Hapus Kategori?',
                message: `Kategori "${target.name}" akan dihapus permanen dari database. Lanjutkan?`,
                confirmText: 'Ya, Hapus',
                cancelText: 'Tidak',
                onConfirm: async () => {
                    try {
                        const res = await fetch(`/portal/categories/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                            },
                        });
                        const data = await res.json();
                        if (!res.ok) {
                            RentalinPopup.show({ type: 'failed', title: 'Tidak Dapat Menghapus', message: data.message || 'Kategori masih digunakan oleh armada.' });
                            return;
                        }

                        window.fleetCategories = window.fleetCategories.filter(c => Number(c.id) !== Number(id));
                        renderCategoryListAndDropdowns();
                        RentalinPopup.show({ type: 'success', title: 'Kategori Dihapus', message: `Kategori "${target.name}" berhasil dihapus.` });
                    } catch (err) {
                        RentalinPopup.show({ type: 'failed', title: 'Kesalahan Jaringan', message: 'Tidak dapat terhubung ke server.' });
                    }
                },
            });
        }

        // ================= MANAJEMEN DAFTAR UNIT DI BAWAH FORM GLOBAL =================
        function updateUnitSummaryBadge() {
            const total = window.currentModalUnits.length;
            const avail = window.currentModalUnits.filter(u => u.status === 'tersedia').length;
            const badge = document.getElementById('veh-unit-summary-badge');
            if (badge) {
                badge.innerText = `${avail} Tersedia / ${total} Unit`;
                badge.className = avail > 0
                    ? 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600'
                    : 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500/15 text-amber-600';
            }
        }

        function renderVehicleUnitsList() {
            const container = document.getElementById('vehicle-units-list');
            if (!container) return;

            // Hanya Tersedia & Maintenance (Disewa otomatis dari transaksi)
            const statusLabels = {
                tersedia: 'Tersedia',
                maintenance: 'Maintenance',
            };

            container.innerHTML = window.currentModalUnits.map((unit, idx) => {
                const isRented = unit.status === 'disewa';
                const currentStatus = statusLabels[unit.status] ? unit.status : 'tersedia';
                const currentLabel  = statusLabels[currentStatus];

                const statusControlHtml = isRented
                    ? `<span class="px-3.5 py-2 rounded-2xl bg-sky-500/15 text-sky-600 dark:text-sky-400 text-xs font-extrabold inline-flex items-center gap-1.5">
                           <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> Sedang Disewa
                       </span>`
                    : `<div class="w-36 relative custom-select-group" data-target="veh-unit-status-${idx}" data-onchange="onUnitStatusSelectChange">
                           <input type="hidden" id="veh-unit-status-${idx}" value="${currentStatus}">
                           <button type="button" onclick="RentalinUI.toggleDropdown(event, this)"
                               class="custom-select-trigger w-full bg-white border border-gray-200 rounded-2xl px-3.5 py-2 text-left text-xs font-semibold text-brand-ink flex items-center justify-between transition-all duration-200 cursor-pointer">
                               <span class="custom-select-label truncate">${currentLabel}</span>
                               <i class="bi bi-chevron-down text-xs text-gray-500 transition-transform duration-200"></i>
                           </button>
                           <div onclick="event.stopPropagation()" class="custom-select-menu hidden absolute left-0 right-0 mt-1.5 bg-white rounded-2xl shadow-[0_14px_34px_rgba(0,0,0,0.14)] border border-gray-100 py-1.5 z-50">
                               ${Object.entries(statusLabels).map(([val, lbl]) => {
                                   const active = val === currentStatus;
                                   return `
                                       <button type="button"
                                           data-value="${val}"
                                           data-label="${lbl}"
                                           onclick="RentalinUI.selectOption(this, this.dataset.value, this.dataset.label)"
                                           class="custom-option w-full text-left px-3.5 py-2 text-xs font-semibold transition flex items-center justify-between hover:bg-brand-pinkSoft/60 hover:text-brand-pink ${active ? 'text-brand-pink bg-brand-pinkSoft/40 font-bold' : 'text-brand-ink'}">
                                           <span>${lbl}</span>
                                           <i class="bi bi-check2 text-sm ${active ? '' : 'hidden'}"></i>
                                       </button>
                                   `;
                               }).join('')}
                           </div>
                       </div>`;

                return `
                    <div class="p-3 rounded-2xl bg-brand-canvas border border-gray-200/80 flex flex-col sm:flex-row sm:items-center gap-2.5">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-white text-brand-ink text-[11px] font-extrabold flex items-center justify-center flex-shrink-0 border border-gray-200/70">
                                ${idx + 1}
                            </span>
                            <input type="text"
                                   value="${escapeHtmlText(unit.license_plate)}"
                                   required
                                   placeholder="Plat Nomor (L 1428 AB)"
                                   oninput="updateVehicleUnitField(${idx}, 'license_plate', this.value.toUpperCase())"
                                   class="w-36 sm:w-40 rounded-xl px-3 py-2 text-xs font-mono font-extrabold uppercase !bg-white">
                            <input type="text"
                                   value="${escapeHtmlText(unit.color_year)}"
                                   placeholder="Warna & Tahun (Putih 2023)"
                                   oninput="updateVehicleUnitField(${idx}, 'color_year', this.value)"
                                   class="flex-1 min-w-0 rounded-xl px-3 py-2 text-xs font-semibold !bg-white">
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            ${statusControlHtml}

                            <button type="button"
                                onclick="removeVehicleUnitRow(${idx})"
                                ${(window.currentModalUnits.length <= 1 || isRented) ? 'disabled' : ''}
                                title="${isRented ? 'Unit sedang disewa' : (window.currentModalUnits.length <= 1 ? 'Minimal 1 unit' : 'Hapus Unit Ini')}"
                                class="w-8 h-8 rounded-full bg-white hover:bg-brand-pinkSoft disabled:opacity-35 text-gray-500 hover:text-brand-pink flex items-center justify-center flex-shrink-0 transition cursor-pointer">
                                <i class="bi bi-trash-fill text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            updateUnitSummaryBadge();
        }

        function onUnitStatusSelectChange(newVal, hiddenInputEl) {
            const match = hiddenInputEl?.id?.match(/^veh-unit-status-(\d+)$/);
            if (!match) return;
            updateVehicleUnitField(Number(match[1]), 'status', newVal);
        }

        function addVehicleUnitRow() {
            window.currentModalUnits.push({
                unit_id: null,
                license_plate: '',
                color_year: '',
                status: 'tersedia',
            });
            renderVehicleUnitsList();

            setTimeout(() => {
                const container = document.getElementById('vehicle-units-list');
                const inputs = container?.querySelectorAll('input[placeholder*="Plat Nomor"]');
                if (inputs && inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            }, 40);
        }

        function updateVehicleUnitField(index, field, value) {
            if (!window.currentModalUnits[index]) return;
            window.currentModalUnits[index][field] = value;
            if (field === 'status') {
                updateUnitSummaryBadge();
            }
        }

        function removeVehicleUnitRow(index) {
            if (window.currentModalUnits.length <= 1) return;
            if (window.currentModalUnits[index]?.status === 'disewa') return;
            window.currentModalUnits.splice(index, 1);
            renderVehicleUnitsList();
        }

        function buildVehicleTableRowElement(group, destroyUrl) {
            const thumb = (Array.isArray(group.images) && group.images.length > 0)
                ? (String(group.images[0]).startsWith('http') ? group.images[0] : `${STORAGE_BASE_URL}/${group.images[0]}`)
                : 'https://placehold.co/120x90/f3f4f6/9ca3af?text=Unit';

            const totalUnits  = group.units.length;
            const availUnits  = group.units.filter(u => u.status === 'tersedia').length;
            const rentedUnits = group.units.filter(u => u.status === 'disewa').length;
            const maintUnits  = group.units.filter(u => u.status === 'maintenance').length;

            const allPlates = group.units.map(u => u.license_plate).join(' ');
            const allColors = group.units.map(u => u.color_year || '').join(' ');
            const searchHaystack = `${group.name} ${allPlates} ${group.category_name} ${group.spec} ${allColors}`.toLowerCase();

            const unitBadgesHtml = group.units.map(u => {
                const dotColor = u.status === 'tersedia'
                    ? 'bg-emerald-500'
                    : (u.status === 'maintenance' ? 'bg-amber-500' : 'bg-sky-500');
                return `
                    <span class="inline-flex items-center gap-1 font-mono text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-brand-canvas border border-gray-200/80 text-brand-ink">
                        <span class="w-1.5 h-1.5 rounded-full ${dotColor}"></span>
                        ${escapeHtmlText(u.license_plate)}
                    </span>
                `;
            }).join('');

            let generalStatusHtml = '';
            if (availUnits > 0) {
                generalStatusHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-emerald-500/15 text-emerald-600 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia ${availUnits}</span>`;
            }
            if (rentedUnits > 0) {
                generalStatusHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-blue-500/15 text-blue-600 dark:text-blue-400"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Disewa ${rentedUnits}</span>`;
            }
            if (maintUnits > 0) {
                generalStatusHtml += `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-amber-500/15 text-amber-600 dark:text-amber-400"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Maintenance ${maintUnits}</span>`;
            }

            const tr = document.createElement('tr');
            tr.className = 'vehicle-row hover:bg-brand-canvas/40 transition';
            tr.dataset.groupKey = group.group_key;
            tr.dataset.category = group.category_slug;
            tr.dataset.categoryId = group.category_id;
            tr.dataset.avail = availUnits;
            tr.dataset.rented = rentedUnits;
            tr.dataset.maint = maintUnits;
            tr.dataset.search = searchHaystack;

            const groupJsonAttr = escapeHtmlText(JSON.stringify(group));
            const destroyUrlAttr = escapeHtmlText(destroyUrl);
            const nameJsonAttr   = escapeHtmlText(JSON.stringify(group.name));

            tr.innerHTML = `
                <td class="py-3.5 pl-5 pr-2">
                    <input type="checkbox" class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>
                <td class="py-3.5 px-4" data-sort="${escapeHtmlText(group.name)}">
                    <div class="flex items-start gap-3.5">
                        <img src="${escapeHtmlText(thumb)}" alt="${escapeHtmlText(group.name)}"
                             class="w-12 h-10 rounded-xl object-cover bg-brand-canvas border border-gray-200/70 flex-shrink-0 mt-0.5">
                        <div class="min-w-0">
                            <span class="font-extrabold text-brand-ink text-xs sm:text-sm block">${escapeHtmlText(group.name)}</span>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">${escapeHtmlText(group.spec)}</p>
                            <div class="row-unit-badges flex flex-wrap items-center gap-1.5 mt-1.5">${unitBadgesHtml}</div>
                        </div>
                    </div>
                </td>
                <td class="py-3.5 px-4 vehicle-cat-cell" data-sort="${escapeHtmlText(group.category_name)}">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap bg-brand-canvas text-brand-ink">${escapeHtmlText(group.category_name)}</span>
                </td>
                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="${group.price_per_day}">
                    Rp ${Number(group.price_per_day || 0).toLocaleString('id-ID')}
                </td>
                <td class="py-3.5 px-4 font-bold text-brand-ink whitespace-nowrap row-stock-cell" data-sort="${availUnits}">
                    ${availUnits} / ${totalUnits} Unit
                </td>
                <td class="py-3.5 px-4 row-status-cell" data-sort="${availUnits}">
                    <div class="flex flex-wrap items-center gap-1.5">${generalStatusHtml}</div>
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                        <button type="button"
                            onclick='openVehicleModal(${groupJsonAttr})'
                            title="Edit Armada & Daftar Unit"
                            class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-yellowSoft text-brand-ink flex items-center justify-center transition cursor-pointer">
                            <i class="bi bi-pencil-fill text-xs"></i>
                        </button>
                        <button type="button"
                            onclick='confirmDeleteVehicle("${destroyUrlAttr}", ${nameJsonAttr})'
                            title="Hapus Armada"
                            class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                            <i class="bi bi-trash-fill text-xs"></i>
                        </button>
                    </div>
                </td>
            `;

            return tr;
        }

        function updateTotalFleetCounter() {
            const totalRows = document.querySelectorAll('#vehicles-table tbody tr.vehicle-row').length;
            const totalEl = document.getElementById('fleet-total-count');
            if (totalEl) totalEl.innerText = totalRows;
            filterFleetTable();
        }

        async function submitVehicleFormAjax(e) {
            e.preventDefault();

            const units = window.currentModalUnits.length > 0
                ? window.currentModalUnits
                : [{ unit_id: '', license_plate: '-', color_year: '', status: 'tersedia' }];

            const hiddenWrap = document.getElementById('veh-units-hidden-inputs');
            if (hiddenWrap) {
                hiddenWrap.innerHTML = units.map((u, i) => `
                    <input type="hidden" name="units[${i}][unit_id]" value="${escapeHtmlText(u.unit_id || '')}">
                    <input type="hidden" name="units[${i}][license_plate]" value="${escapeHtmlText(u.license_plate)}">
                    <input type="hidden" name="units[${i}][color_year]" value="${escapeHtmlText(u.color_year)}">
                    <input type="hidden" name="units[${i}][status]" value="${escapeHtmlText(u.status)}">
                `).join('');
            }

            const form = document.getElementById('vehicleFormElement');
            const submitBtn = document.getElementById('btn-submit-vehicle');
            const formData = new FormData(form);

            submitBtn.disabled = true;
            submitBtn.innerText = 'Menyimpan...';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: formData,
                });
                const data = await res.json();

                if (!res.ok) {
                    const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                    RentalinPopup.show({
                        type: 'failed',
                        title: 'Gagal Menyimpan Armada',
                        message: firstErr || data.message || 'Periksa kembali isian form armada.',
                    });
                    return;
                }

                // Update / sisipkan baris tabel secara langsung tanpa reload halaman
                const tbody = document.querySelector('#vehicles-table tbody');
                const emptyRow = document.getElementById('fleet-empty-row');
                const newRow = buildVehicleTableRowElement(data.group, data.destroy_url);

                const oldRow = tbody?.querySelector(`tr.vehicle-row[data-group-key="${CSS.escape(data.old_group_key)}"]`)
                    || tbody?.querySelector(`tr.vehicle-row[data-group-key="${CSS.escape(data.group.group_key)}"]`);

                if (oldRow) {
                    oldRow.replaceWith(newRow);
                } else if (emptyRow) {
                    tbody.insertBefore(newRow, emptyRow);
                } else if (tbody) {
                    tbody.appendChild(newRow);
                }

                updateTotalFleetCounter();
                RentalinUI.closeModal('vehicleFormModal');

                RentalinPopup.show({
                    type: 'success',
                    title: 'Armada Tersimpan',
                    message: data.message || 'Data armada dan daftar unit berhasil disimpan.',
                    duration: 4000,
                });
            } catch (err) {
                RentalinPopup.show({
                    type: 'failed',
                    title: 'Kesalahan Jaringan',
                    message: 'Tidak dapat terhubung ke server saat menyimpan armada.',
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Simpan Armada & Unit';
            }
        }

        function confirmDeleteVehicle(destroyUrl, vehicleName) {
            RentalinPopup.confirm({
                type: 'warning',
                title: 'Hapus Armada?',
                message: `Armada "${vehicleName}" beserta seluruh unit di bawahnya akan dihapus permanen. Lanjutkan?`,
                confirmText: 'Ya, Hapus',
                cancelText: 'Tidak',
                onConfirm: async () => {
                    try {
                        const res = await fetch(destroyUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                            },
                        });
                        const data = await res.json();

                        if (!res.ok) {
                            RentalinPopup.show({
                                type: 'failed',
                                title: 'Gagal Menghapus',
                                message: data.message || 'Armada tidak dapat dihapus.',
                            });
                            return;
                        }

                        const targetRow = document.querySelector(`#vehicles-table tbody tr.vehicle-row[data-group-key="${CSS.escape(data.group_key)}"]`);
                        if (targetRow) {
                            targetRow.remove();
                        }
                        updateTotalFleetCounter();

                        RentalinPopup.show({
                            type: 'success',
                            title: 'Armada Dihapus',
                            message: data.message || `Armada "${vehicleName}" berhasil dihapus.`,
                            duration: 4000,
                        });
                    } catch (err) {
                        RentalinPopup.show({
                            type: 'failed',
                            title: 'Kesalahan Jaringan',
                            message: 'Tidak dapat terhubung ke server.',
                        });
                    }
                },
            });
        }

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
                    let matchStatus = true;
                    if (statusVal === 'tersedia') matchStatus = Number(row.dataset.avail || 0) > 0;
                    else if (statusVal === 'disewa') matchStatus = Number(row.dataset.rented || 0) > 0;
                    else if (statusVal === 'maintenance') matchStatus = Number(row.dataset.maint || 0) > 0;

                    return matchCat && matchStatus;
                },
            });
        }

        function renderPhotoPreviews(urls) {
            const wrap = document.getElementById('veh-photo-preview-wrap');
            if (!wrap) return;
            if (!Array.isArray(urls) || urls.length === 0) {
                wrap.innerHTML = '';
                wrap.classList.add('hidden');
                return;
            }
            wrap.innerHTML = urls.map(src => `
                <img src="${escapeHtmlText(src)}" alt="Preview Armada"
                     class="w-16 h-12 rounded-xl object-cover border border-gray-200 flex-shrink-0 bg-brand-canvas">
            `).join('');
            wrap.classList.remove('hidden');
        }

        function previewSelectedVehiclePhotos(event) {
            const files = Array.from(event.target?.files || []);
            if (files.length === 0) return;
            const blobUrls = files.map(f => URL.createObjectURL(f));
            renderPhotoPreviews(blobUrls);
        }

        function openVehicleModal(vehicleGroup = null) {
            document.getElementById('veh-modal-title').innerText = vehicleGroup ? 'Edit Armada & Daftar Unit' : 'Tambah Armada Kendaraan';
            document.getElementById('veh-id').value = vehicleGroup?.id || '';
            document.getElementById('veh-original-name').value = vehicleGroup?.name || '';
            document.getElementById('veh-name').value = vehicleGroup?.name || '';
            document.getElementById('veh-price').value = vehicleGroup?.price_per_day || '';
            document.getElementById('veh-spec').value = vehicleGroup?.spec || '';

            const fileInput = document.getElementById('veh-images');
            if (fileInput) fileInput.value = '';

            if (vehicleGroup && Array.isArray(vehicleGroup.images) && vehicleGroup.images.length > 0) {
                const existingUrls = vehicleGroup.images.map(img =>
                    String(img).startsWith('http') ? img : `${STORAGE_BASE_URL}/${img}`
                );
                renderPhotoPreviews(existingUrls);
            } else {
                renderPhotoPreviews([]);
            }

            const targetCatId = vehicleGroup?.category_id || (window.fleetCategories[0]?.id ?? '');
            renderCategoryListAndDropdowns(targetCatId);

            if (vehicleGroup && Array.isArray(vehicleGroup.units) && vehicleGroup.units.length > 0) {
                window.currentModalUnits = JSON.parse(JSON.stringify(vehicleGroup.units));
            } else {
                window.currentModalUnits = [{
                    unit_id: null,
                    license_plate: '',
                    color_year: '',
                    status: 'tersedia',
                }];
            }

            renderVehicleUnitsList();
            RentalinUI.openModal('vehicleFormModal');
        }

        renderCategoryListAndDropdowns();
    </script>
@endsection