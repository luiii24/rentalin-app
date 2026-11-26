@props([
    'name',
    'label' => null,
    'options' => [], // Format: ['value' => 'Label']
    'selected' => null,
    'placeholder' => 'Pilih opsi',
    'onchange' => null,
    'id' => null,
])

@php
    $inputId = $id ?? 'input-' . $name . '-' . uniqid();
    $selectedLabel = $options[$selected] ?? $placeholder;
@endphp

<div class="relative custom-select-group" data-target="{{ $inputId }}" @if($onchange) data-onchange="{{ $onchange }}" @endif>
    @if($label)
        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $selected }}">

    <button type="button" onclick="RentalinUI.toggleDropdown(event, this)"
        class="custom-select-trigger w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-left text-xs font-semibold text-brand-ink flex items-center justify-between transition-all duration-200">
        <span class="custom-select-label truncate">{{ $selectedLabel }}</span>
        <i class="bi bi-chevron-down text-xs text-gray-500 transition-transform duration-200"></i>
    </button>

    <div onclick="event.stopPropagation()" class="custom-select-menu hidden absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-[0_14px_34px_rgba(0,0,0,0.14)] border border-gray-100 py-2 z-50 max-h-56 overflow-y-auto">
        @foreach($options as $val => $text)
            @php $isActive = (string)$selected === (string)$val; @endphp
            <button type="button" onclick="RentalinUI.selectOption(this, '{{ $val }}', '{{ addslashes($text) }}')"
                class="custom-option w-full text-left px-4 py-2.5 text-xs font-semibold transition flex items-center justify-between hover:bg-brand-pinkSoft/60 hover:text-brand-pink {{ $isActive ? 'text-brand-pink bg-brand-pinkSoft/40 font-bold' : 'text-brand-ink' }}">
                <span>{{ $text }}</span>
                <i class="bi bi-check2 text-sm {{ $isActive ? '' : 'hidden' }}"></i>
            </button>
        @endforeach
    </div>
</div>