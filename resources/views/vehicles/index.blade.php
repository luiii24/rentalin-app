@extends('layouts.admin')

@section('title', 'Kelola Armada')
@section('page-title', 'Kelola Armada Kendaraan')

@section('content')
    @php
        $categoryOptions = [];
        foreach ($categories as $cat) {
            $categoryOptions[$cat->id] = $cat->name;
        }
    @endphp

    @if(session('success'))
        <div class="mb-5 bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-sm"></i> {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 cursor-pointer"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 bg-brand-pinkSoft text-brand-pink px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-between">
            <span class="flex items-center gap-2"><i class="bi bi-exclamation-circle-fill text-sm"></i> {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 cursor-pointer"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-brand-ink">Daftar Armada</h1>
            <p class="text-xs text-gray-500">Klik judul kolom bertanda panah untuk mengurutkan data unit</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="openCategoryModal()"
                class="inline-flex items-center justify-center gap-2 bg-white hover:bg-brand-canvas active:scale-95 text-brand-ink text-xs font-extrabold px-4 py-3 rounded-full shadow-2xs transition-all cursor-pointer">
                <i class="bi bi-tags-fill text-brand-pink"></i> Kelola Kategori
            </button>

            <button type="button" onclick="openAddVehicleModal()"
                class="inline-flex items-center justify-center gap-2 bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold px-5 py-3 rounded-full shadow-xs transition-all cursor-pointer">
                <i class="bi bi-plus-lg"></i> Tambah Armada
            </button>
        </div>
    </div>

    <x-table id="vehicles-table" :headers="[
        ['label' => 'Nama Kendaraan', 'sortable' => true],
        ['label' => 'Kategori', 'sortable' => true],
        ['label' => 'Tarif / Hari', 'sortable' => true],
        ['label' => 'Stok Tersedia', 'sortable' => true],
        ['label' => 'Status', 'sortable' => true],
        ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
    ]">
        @forelse($vehicles as $vehicle)
            @php
                $images = is_array($vehicle->images) && count($vehicle->images) > 0
                    ? array_map(fn($img) => str_starts_with($img, 'http') ? $img : asset('storage/' . $img), $vehicle->images)
                    : ['https://placehold.co/120x90/f3f4f6/9ca3af?text=Unit'];
            @endphp
            <tr class="hover:bg-brand-canvas/60 transition">
                <td class="py-3.5 pl-5 pr-2">
                    <input type="checkbox" onchange="this.closest('tr').classList.toggle('bg-brand-pinkSoft/25', this.checked)"
                        class="w-4 h-4 rounded bg-brand-canvas accent-brand-pink cursor-pointer">
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $vehicle->name }}">
                    <div class="flex items-center gap-3">
                        <img src="{{ $images[0] }}" alt="{{ $vehicle->name }}" class="w-11 h-9 rounded-xl object-cover bg-brand-canvas flex-shrink-0">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-brand-ink truncate">{{ $vehicle->name }}</p>
                                @if(!empty($vehicle->license_plate))
                                    <span class="bg-brand-canvas text-brand-ink font-mono text-[10px] font-extrabold px-2 py-0.5 rounded-md flex-shrink-0">
                                        {{ $vehicle->license_plate }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-400 truncate">
                                {{ $vehicle->spec }}
                                @if(!empty($vehicle->color))
                                    &bull; {{ $vehicle->color }}
                                @endif
                                @if(!empty($vehicle->year))
                                    ({{ $vehicle->year }})
                                @endif
                            </p>
                        </div>
                    </div>
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $vehicle->category->name ?? '' }}">
                    <span class="bg-brand-canvas text-gray-600 text-[11px] font-bold px-3 py-1 rounded-full">
                        {{ $vehicle->category->name ?? '-' }}
                    </span>
                </td>

                <td class="py-3.5 px-4 font-extrabold text-brand-ink" data-sort="{{ $vehicle->price_per_day }}">
                    Rp {{ number_format($vehicle->price_per_day, 0, ',', '.') }}
                </td>

                <td class="py-3.5 px-4 font-bold" data-sort="{{ $vehicle->available_stock }}">
                    {{ $vehicle->available_stock }} / {{ $vehicle->total_stock }} Unit
                </td>

                <td class="py-3.5 px-4" data-sort="{{ $vehicle->status }}">
                    @if($vehicle->status === 'tersedia')
                        <span class="inline-flex items-center gap-1.5 bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-[11px] font-extrabold px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 bg-amber-500/15 text-amber-600 dark:text-amber-400 text-[11px] font-extrabold px-3 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Maintenance
                        </span>
                    @endif
                </td>

                <td class="py-3.5 px-4 text-right">
                    <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Hapus armada ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-full bg-brand-canvas hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink inline-flex items-center justify-center transition cursor-pointer" title="Hapus">
                            <i class="bi bi-trash3-fill text-xs"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-12 text-center text-gray-400 text-xs">
                    Belum ada data kendaraan yang ditambahkan.
                </td>
            </tr>
        @endforelse
    </x-table>

    <!-- MODAL KELOLA KATEGORI KENDARAAN (TAMBAH, EDIT INLINE, HAPUS) -->
    <div id="manageCategoriesModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-md max-h-[88vh] overflow-y-auto shadow-2xl">
            <div class="p-5 sm:p-6 flex justify-between items-center sticky top-0 bg-white z-10">
                <div>
                    <h2 class="text-lg font-extrabold text-brand-ink">Kelola Kategori</h2>
                    <p class="text-[11px] text-gray-400">Tambah atau ubah tipe kendaraan yang tersedia</p>
                </div>
                <button type="button" onclick="closeCategoryModal()" class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="px-5 sm:px-6 pb-6 space-y-5">
                <!-- Form Tambah Kategori Baru -->
                <form action="{{ Route::has('admin.categories.store') ? route('admin.categories.store') : url('/admin/categories') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Tipe baru (misal: SUV, Minibus, Sport...)"
                        class="flex-1 rounded-2xl px-4 py-3 text-xs font-semibold">
                    <button type="submit"
                        class="px-4 py-3 rounded-2xl bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold whitespace-nowrap transition-all cursor-pointer">
                        + Tambah
                    </button>
                </form>

                <!-- Daftar Kategori Aktif -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400">Daftar Tipe Kendaraan</span>
                        <span class="text-[11px] font-bold text-gray-400">{{ count($categories) }} Kategori</span>
                    </div>

                    @forelse($categories as $cat)
                        <div class="bg-brand-canvas rounded-2xl p-2.5 transition-all" id="cat-row-{{ $cat->id }}">
                            <!-- Mode Tampil Normal -->
                            <div class="cat-view-mode flex items-center justify-between gap-2 pl-2">
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold text-brand-ink truncate">{{ $cat->name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $cat->vehicles_count ?? $cat->vehicles()->count() }} unit terdaftar</p>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button type="button" onclick="toggleEditCategory({{ $cat->id }}, true)" title="Edit nama kategori"
                                        class="w-8 h-8 rounded-xl bg-white hover:opacity-90 text-brand-ink flex items-center justify-center transition cursor-pointer">
                                        <i class="bi bi-pencil-fill text-[11px]"></i>
                                    </button>

                                    <form action="{{ Route::has('admin.categories.destroy') ? route('admin.categories.destroy', $cat->id) : url('/admin/categories/' . $cat->id) }}"
                                          method="POST" onsubmit="return confirm('Hapus kategori {{ addslashes($cat->name) }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus kategori"
                                            class="w-8 h-8 rounded-xl bg-white hover:bg-brand-pinkSoft text-gray-500 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                                            <i class="bi bi-trash3-fill text-[11px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Mode Edit Inline -->
                            <form action="{{ Route::has('admin.categories.update') ? route('admin.categories.update', $cat->id) : url('/admin/categories/' . $cat->id) }}"
                                  method="POST" class="cat-edit-mode hidden flex items-center gap-1.5">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $cat->name }}" required
                                    class="flex-1 !bg-white rounded-xl px-3 py-2 text-xs font-bold">
                                <button type="submit" title="Simpan perubahan"
                                    class="w-8 h-8 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center transition cursor-pointer">
                                    <i class="bi bi-check-lg text-xs"></i>
                                </button>
                                <button type="button" onclick="toggleEditCategory({{ $cat->id }}, false)" title="Batal"
                                    class="w-8 h-8 rounded-xl bg-white text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                                    <i class="bi bi-x-lg text-[11px]"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="bg-brand-canvas rounded-2xl p-6 text-center text-xs text-gray-400">
                            Belum ada kategori kendaraan. Tambahkan tipe pertama di atas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ARMADA (BORDERLESS + BATCH UNIT) -->
    <div id="addVehicleModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="p-5 sm:p-6 flex justify-between items-center sticky top-0 bg-white z-10">
                <div>
                    <h2 class="text-lg font-extrabold text-brand-ink">Tambah Armada Baru</h2>
                    <p class="text-[11px] text-gray-400">Isi info umum kendaraan sekali, lalu masukkan daftar plat nomor tiap unit</p>
                </div>
                <button type="button" onclick="closeAddVehicleModal()" class="w-8 h-8 rounded-full bg-brand-canvas text-gray-500 hover:text-brand-ink flex items-center justify-center transition cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form action="{{ route('admin.vehicles.store') }}" method="POST" enctype="multipart/form-data" class="px-5 sm:px-6 pb-6 space-y-5">
                @csrf

                <!-- BAGIAN 1: INFORMASI UMUM KENDARAAN (1X INPUT) -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Nama Kendaraan</label>
                        <input type="text" name="name" required placeholder="Contoh: Toyota Avanza Veloz"
                            class="w-full rounded-2xl px-4 py-3 text-xs font-semibold">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-extrabold text-brand-ink">Kategori</label>
                                <button type="button" onclick="openCategoryModal()"
                                    class="text-[11px] font-extrabold text-brand-pink hover:underline cursor-pointer">
                                    + Edit / Tambah Tipe
                                </button>
                            </div>
                            <x-select
                                name="category_id"
                                placeholder="Pilih kategori"
                                :options="$categoryOptions"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Tarif per Hari (Rp)</label>
                            <input type="number" name="price_per_day" required min="10000" placeholder="350000"
                                class="w-full rounded-2xl px-4 py-3 text-xs font-semibold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">Spesifikasi Singkat</label>
                        <input type="text" name="spec" required placeholder="Contoh: Automatic • 7 Kursi • Bensin"
                            class="w-full rounded-2xl px-4 py-3 text-xs font-semibold">
                    </div>

                    <x-file-upload
                        name="images[]"
                        label="Galeri Foto Kendaraan"
                        accept="image/*"
                        :multiple="true"
                        hint="Bisa pilih lebih dari 1 foto (tampak depan, samping, interior) — otomatis dipakai semua unit di bawah"
                    />
                </div>

                <!-- BAGIAN 2: DAFTAR UNIT FISIK & PLAT NOMOR (BISA 1 ATAU BANYAK SEKALIGUS) -->
                <div class="bg-brand-canvas rounded-3xl p-4 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-xs font-extrabold text-brand-ink">Daftar Unit Fisik & Plat Nomor</h3>
                            <p class="text-[11px] text-gray-500">Atur jumlah baris sesuai banyaknya unit fisik yang masuk</p>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <input type="number" id="batch-unit-count" value="1" min="1" max="30"
                                class="w-12 !bg-white rounded-xl px-2 py-1.5 text-xs font-extrabold text-center">
                            <button type="button" onclick="applyBatchUnitCount()"
                                class="bg-white hover:opacity-90 text-brand-ink text-[11px] font-extrabold px-3 py-1.5 rounded-xl transition cursor-pointer">
                                Set
                            </button>
                            <button type="button" onclick="addUnitRow()"
                                class="bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-[11px] font-extrabold px-3 py-1.5 rounded-xl transition cursor-pointer">
                                + Unit
                            </button>
                        </div>
                    </div>

                    <div class="hidden sm:grid sm:grid-cols-[28px_1.35fr_0.95fr_0.8fr_1.35fr_32px] gap-2 px-3 text-[10px] font-extrabold uppercase tracking-wider text-gray-400">
                        <div class="text-center">#</div>
                        <div>Plat Nomor</div>
                        <div>Warna</div>
                        <div>Tahun</div>
                        <div>Status</div>
                        <div></div>
                    </div>

                    <div id="unit-rows-container" class="space-y-2"></div>
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="w-full py-3.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold shadow-xs transition-all cursor-pointer">
                        Simpan Armada (<span id="submit-unit-label">1</span> Unit)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCategoryModal() {
            const modal = document.getElementById('manageCategoriesModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeCategoryModal() {
            const modal = document.getElementById('manageCategoriesModal');
            if (modal) modal.classList.add('hidden');
        }

        function toggleEditCategory(id, isEditing) {
            const row = document.getElementById(`cat-row-${id}`);
            if (!row) return;
            const viewMode = row.querySelector('.cat-view-mode');
            const editMode = row.querySelector('.cat-edit-mode');

            if (isEditing) {
                viewMode.classList.add('hidden');
                editMode.classList.remove('hidden');
                const input = editMode.querySelector('input[name="name"]');
                if (input) {
                    input.focus();
                    input.select();
                }
            } else {
                editMode.classList.add('hidden');
                viewMode.classList.remove('hidden');
            }
        }

        (function initVehicleBatchRows() {
            let rowCounter = 0;
            const container = document.getElementById('unit-rows-container');
            const countInput = document.getElementById('batch-unit-count');
            const submitLabel = document.getElementById('submit-unit-label');

            function createUnitRowHTML(idx, num) {
                const statusInputId = `unit-status-${idx}`;
                return `
                    <div class="unit-row bg-white rounded-2xl p-2.5 grid grid-cols-12 sm:grid-cols-[28px_1.35fr_0.95fr_0.8fr_1.35fr_32px] gap-2 items-center transition-all">
                        <div class="col-span-2 sm:col-span-1 text-[11px] font-extrabold text-gray-400 text-center row-num">
                            ${num}
                        </div>
                        <div class="col-span-10 sm:col-span-1">
                            <input type="text" name="units[${idx}][license_plate]" required
                                placeholder="L 1234 AB"
                                class="w-full rounded-xl px-2.5 py-2 font-mono text-xs font-bold uppercase">
                        </div>
                        <div class="col-span-4 sm:col-span-1">
                            <input type="text" name="units[${idx}][color]"
                                placeholder="Warna"
                                class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold">
                        </div>
                        <div class="col-span-3 sm:col-span-1">
                            <input type="text" name="units[${idx}][year]" maxlength="4" inputmode="numeric"
                                placeholder="Tahun"
                                class="w-full rounded-xl px-2.5 py-2 text-xs font-semibold text-center">
                        </div>
                        <div class="col-span-4 sm:col-span-1">
                            <div class="custom-select-group relative" data-target="${statusInputId}">
                                <input type="hidden" name="units[${idx}][status]" id="${statusInputId}" value="tersedia">
                                <button type="button" onclick="RentalinUI.toggleDropdown(event, this)"
                                    class="custom-select-trigger w-full bg-brand-canvas rounded-xl px-2.5 py-2 text-xs font-bold text-brand-ink flex items-center justify-between gap-1.5 transition cursor-pointer">
                                    <span class="custom-select-label truncate">Tersedia</span>
                                    <i class="bi bi-chevron-down text-[10px] text-gray-400 transition-transform duration-200 flex-shrink-0"></i>
                                </button>
                                <div class="custom-select-menu hidden absolute right-0 mt-1.5 w-36 bg-white rounded-2xl shadow-xl p-1.5 z-40">
                                    <button type="button" data-value="tersedia" data-label="Tersedia"
                                        onclick="RentalinUI.selectOption(this, 'tersedia', 'Tersedia')"
                                        class="custom-option w-full px-3 py-2 rounded-xl text-left text-xs font-bold text-brand-pink bg-brand-pinkSoft/40 flex items-center justify-between transition cursor-pointer">
                                        <span>Tersedia</span>
                                        <i class="bi bi-check2 text-xs"></i>
                                    </button>
                                    <button type="button" data-value="maintenance" data-label="Maintenance"
                                        onclick="RentalinUI.selectOption(this, 'maintenance', 'Maintenance')"
                                        class="custom-option w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-brand-ink hover:bg-brand-canvas flex items-center justify-between transition cursor-pointer">
                                        <span>Maintenance</span>
                                        <i class="bi bi-check2 text-xs hidden"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 flex justify-center">
                            <button type="button" onclick="removeUnitRow(this)" title="Hapus baris"
                                class="btn-del-row w-7 h-7 rounded-full hover:bg-brand-pinkSoft text-gray-400 hover:text-brand-pink flex items-center justify-center transition cursor-pointer">
                                <i class="bi bi-x-lg text-[11px]"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            function syncRowIndexes() {
                if (!container) return;
                const rows = container.querySelectorAll('.unit-row');
                rows.forEach((row, i) => {
                    row.querySelector('.row-num').innerText = i + 1;
                    const delBtn = row.querySelector('.btn-del-row');
                    if (delBtn) delBtn.classList.toggle('invisible', rows.length === 1);
                });
                if (countInput) countInput.value = rows.length;
                if (submitLabel) submitLabel.innerText = rows.length;
            }

            window.addUnitRow = function() {
                if (!container) return;
                const current = container.querySelectorAll('.unit-row').length;
                container.insertAdjacentHTML('beforeend', createUnitRowHTML(rowCounter++, current + 1));
                syncRowIndexes();
            };

            window.removeUnitRow = function(btn) {
                if (!container || container.querySelectorAll('.unit-row').length <= 1) return;
                btn.closest('.unit-row').remove();
                syncRowIndexes();
            };

            window.applyBatchUnitCount = function() {
                if (!container || !countInput) return;
                const target = Math.min(Math.max(parseInt(countInput.value || 1, 10), 1), 30);
                const currentRows = container.querySelectorAll('.unit-row');

                if (target > currentRows.length) {
                    for (let i = currentRows.length; i < target; i++) {
                        container.insertAdjacentHTML('beforeend', createUnitRowHTML(rowCounter++, i + 1));
                    }
                } else if (target < currentRows.length) {
                    for (let i = currentRows.length - 1; i >= target; i--) {
                        currentRows[i].remove();
                    }
                }
                syncRowIndexes();
            };

            window.openAddVehicleModal = function() {
                const modal = document.getElementById('addVehicleModal');
                if (modal) modal.classList.remove('hidden');
                if (container && container.children.length === 0) {
                    window.addUnitRow();
                }
            };

            window.closeAddVehicleModal = function() {
                const modal = document.getElementById('addVehicleModal');
                if (modal) modal.classList.add('hidden');
            };

            if (container && container.children.length === 0) {
                window.addUnitRow();
            }
        })();
    </script>
@endsection