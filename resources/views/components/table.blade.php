@props([
    'id' => 'admin-table-' . uniqid(),
    'headers' => [], // Format: [['label' => 'Nama Unit', 'sortable' => true, 'key' => 'name'], ...]
    'selectable' => true,
])

<div class="bg-white rounded-3xl border border-gray-100/80 shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
        <table id="{{ $id }}" class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200/80 bg-brand-canvas/50 text-brand-ink text-xs font-extrabold tracking-tight select-none">
                    @if($selectable)
                        <th class="py-4 pl-5 pr-2 w-10">
                            <input type="checkbox" onchange="RentalinUI.toggleAllTableRows(this, '{{ $id }}')"
                                class="w-4 h-4 rounded border-gray-300 accent-brand-pink cursor-pointer">
                        </th>
                    @endif

                    @foreach($headers as $colIndex => $head)
                        @php
                            $isSortable = $head['sortable'] ?? false;
                            $alignClass = ($head['align'] ?? 'left') === 'right' ? 'text-right justify-end' : 'text-left';
                        @endphp
                        <th class="py-4 px-4 {{ ($head['align'] ?? 'left') === 'right' ? 'text-right' : '' }}">
                            @if($isSortable)
                                <button type="button" onclick="RentalinUI.sortTable('{{ $id }}', {{ $selectable ? $colIndex + 1 : $colIndex }}, this)"
                                    class="inline-flex items-center gap-1.5 hover:text-brand-pink transition group {{ $alignClass }}">
                                    <span>{{ $head['label'] }}</span>
                                    <span class="sort-indicator text-gray-400 group-hover:text-brand-pink text-[11px] font-normal">
                                        <i class="bi bi-arrow-down-up"></i>
                                    </span>
                                </button>
                            @else
                                <span>{{ $head['label'] }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-xs sm:text-sm text-brand-ink">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>