@props([
    'name',
    'label' => null,
    'value' => null,
    'min' => null,
    'onchange' => null,
    'id' => null,
])

@php
    $inputId = $id ?? 'date-' . $name . '-' . uniqid();
    $defaultVal = $value ?: date('Y-m-d');
@endphp

<div class="relative custom-datepicker-group" data-target="{{ $inputId }}" data-min="{{ $min ?? '' }}" @if($onchange) data-onchange="{{ $onchange }}" @endif>
    @if($label)
        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $defaultVal }}">

    <button type="button" onclick="RentalinUI.toggleDatePicker(event, this)"
        class="datepicker-trigger w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-left text-xs font-bold text-brand-ink flex items-center justify-between shadow-2xs transition-all duration-200">
        <span class="flex items-center gap-2.5 truncate">
            <i class="bi bi-calendar3 text-brand-pink text-sm"></i>
            <span class="datepicker-display-text">{{ $defaultVal }}</span>
        </span>
        <i class="datepicker-chevron bi bi-chevron-down text-xs text-gray-500 transition-transform duration-200"></i>
    </button>

    <div onclick="event.stopPropagation()" class="custom-calendar-card hidden absolute left-0 right-0 min-w-[270px] mt-2 bg-white rounded-3xl shadow-[0_18px_40px_rgba(0,0,0,0.16)] border border-gray-100 p-4 z-50">
        <div class="flex items-center justify-between mb-3 px-1">
            <button type="button" onclick="RentalinUI.changeMonth(this, -1)" class="w-7 h-7 rounded-full hover:bg-brand-canvas flex items-center justify-center text-xs text-brand-ink transition">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="calendar-month-label text-xs font-extrabold text-brand-ink tracking-wide"></span>
            <button type="button" onclick="RentalinUI.changeMonth(this, 1)" class="w-7 h-7 rounded-full hover:bg-brand-canvas flex items-center justify-center text-xs text-brand-ink transition">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <div class="grid grid-cols-7 text-center text-[10px] font-extrabold uppercase tracking-wider mb-2">
            <span class="text-brand-pink">SUN</span>
            <span class="text-gray-400">MON</span>
            <span class="text-gray-400">TUE</span>
            <span class="text-gray-400">WED</span>
            <span class="text-gray-400">THU</span>
            <span class="text-gray-400">FRI</span>
            <span class="text-gray-400">SAT</span>
        </div>

        <div class="calendar-days-grid grid grid-cols-7 gap-y-1 text-center text-xs font-semibold mb-4"></div>

        <button type="button" onclick="RentalinUI.confirmDate(this)"
            class="w-full py-2.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold shadow-xs transition">
            Done
        </button>
    </div>
</div>