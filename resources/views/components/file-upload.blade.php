@props([
    'name',
    'label' => 'Upload File / Foto',
    'accept' => 'image/*',
    'multiple' => false,
    'hint' => 'Format JPG, PNG, WebP atau PDF (Maks. 2MB per file)',
    'id' => null,
])

@php
    $inputId = $id ?? 'file-' . str_replace(['[', ']'], '', $name) . '-' . uniqid();
@endphp

<div class="custom-fileupload-group" data-target="{{ $inputId }}">
    @if($label)
        <label class="block text-xs font-extrabold text-brand-ink mb-1.5">{{ $label }}</label>
    @endif

    <!-- Area Dropzone -->
    <div onclick="document.getElementById('{{ $inputId }}').click()"
         ondragover="event.preventDefault(); this.classList.add('border-brand-pink', 'bg-brand-pinkSoft/30');"
         ondragleave="this.classList.remove('border-brand-pink', 'bg-brand-pinkSoft/30');"
         ondrop="RentalinUI.handleFileDrop(event, '{{ $inputId }}', this)"
         class="border-2 border-dashed border-gray-200 hover:border-brand-pink bg-brand-canvas/40 hover:bg-brand-pinkSoft/20 rounded-2xl p-5 text-center cursor-pointer transition-all duration-200 group">
        
        <input type="file"
               id="{{ $inputId }}"
               name="{{ $name }}"
               accept="{{ $accept }}"
               {{ $multiple ? 'multiple' : '' }}
               onchange="RentalinUI.handleFileSelect(this)"
               class="hidden">

        <div class="w-11 h-11 rounded-2xl bg-white text-brand-pink shadow-2xs mx-auto mb-2.5 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
            <i class="bi bi-cloud-arrow-up-fill"></i>
        </div>
        <p class="text-xs font-bold text-brand-ink mb-0.5">
            Klik untuk pilih file <span class="font-normal text-gray-500">atau seret & lepas ke sini</span>
        </p>
        <p class="text-[11px] text-gray-400">{{ $hint }}</p>
    </div>

    <!-- Daftar Preview File Terpilih -->
    <div id="preview-{{ $inputId }}" class="hidden mt-3 space-y-2"></div>
</div>