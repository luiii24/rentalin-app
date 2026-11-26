@props([
    'variant' => 'neutral', // success, warning, info, danger, neutral, plate
    'dot' => false,
])

@php
    $styles = [
        'success' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
        'warning' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
        'info'    => 'bg-blue-500/15 text-blue-600 dark:text-blue-400',
        'danger'  => 'bg-brand-pinkSoft text-brand-pink',
        'neutral' => 'bg-brand-canvas text-gray-600 dark:text-gray-300',
        'plate'   => 'bg-brand-canvas text-brand-ink font-mono tracking-wide border border-gray-200/80',
    ];
    $dotColors = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'info'    => 'bg-blue-500',
        'danger'  => 'bg-brand-pink',
        'neutral' => 'bg-gray-400',
        'plate'   => 'bg-brand-ink',
    ];
    $cls = $styles[$variant] ?? $styles['neutral'];
    $dotCls = $dotColors[$variant] ?? $dotColors['neutral'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold whitespace-nowrap {$cls}"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotCls }}"></span>
    @endif
    {{ $slot }}
</span>