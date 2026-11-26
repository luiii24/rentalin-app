@props([
    'id' => 'global-search',
    'placeholder' => 'Cari data...',
    'target' => '',
    'countId' => '',
    'emptyId' => '',
    'oninput' => '',
    'value' => '',
    'widthClass' => 'w-full max-w-md',
])

@php
    $clearBtnId = $id . '-clear';
    $defaultHandler = $oninput !== ''
        ? $oninput . '()'
        : "RentalinUI.filterItems({ inputId: '{$id}', clearBtnId: '{$clearBtnId}', itemSelector: '{$target}', countId: '{$countId}', emptyId: '{$emptyId}' })";
    $clearHandler = $oninput !== ''
        ? "RentalinUI.clearSearch('{$id}', {$oninput})"
        : "RentalinUI.clearSearch('{$id}', () => RentalinUI.filterItems({ inputId: '{$id}', clearBtnId: '{$clearBtnId}', itemSelector: '{$target}', countId: '{$countId}', emptyId: '{$emptyId}' }))";
@endphp

<div {{ $attributes->merge(['class' => "flex items-center bg-white border border-gray-200/80 focus-within:border-brand-pink focus-within:ring-4 focus-within:ring-brand-pink/15 rounded-full p-1.5 shadow-2xs transition {$widthClass}"]) }}>
    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-brand-canvas text-brand-pink flex items-center justify-center flex-shrink-0">
        <i class="bi bi-search text-xs sm:text-sm"></i>
    </div>

    <input type="text"
           id="{{ $id }}"
           value="{{ $value }}"
           oninput="{{ $defaultHandler }}"
           placeholder="{{ $placeholder }}"
           class="pill-search-input flex-1 px-3 sm:px-3.5 text-xs sm:text-sm font-semibold text-brand-ink placeholder:font-normal placeholder:text-gray-400">

    <button type="button"
            id="{{ $clearBtnId }}"
            onclick="{{ $clearHandler }}"
            title="Hapus pencarian"
            class="{{ $value === '' ? 'hidden' : '' }} w-7 h-7 sm:w-8 sm:h-8 rounded-full text-gray-400 hover:text-brand-ink flex items-center justify-center transition cursor-pointer flex-shrink-0">
        <i class="bi bi-x-circle-fill text-xs sm:text-sm"></i>
    </button>

    {{ $slot }}
</div>