@props([
    'variant' => 'primary', // primary, success, warning, danger, info, dark, slate
])

@php
    $variantClasses = [
        'primary' => 'bg-primary/10 text-primary border-primary/10',
        'success' => 'bg-success/10 text-success border-success/10',
        'warning' => 'bg-warning/10 text-warning border-warning/10',
        'danger' => 'bg-danger/10 text-danger border-danger/10',
        'info' => 'bg-info/10 text-info border-info/10',
        'dark' => 'bg-dark/10 text-dark border-dark/10',
        'slate' => 'bg-slate-200 text-slate-700 border-slate-200',
        'outline-primary' => 'bg-transparent text-primary border-primary',
        'outline-success' => 'bg-transparent text-success border-success',
        'outline-warning' => 'bg-transparent text-warning border-warning',
        'outline-danger' => 'bg-transparent text-danger border-danger',
        'outline-info' => 'bg-transparent text-info border-info',
        'outline-dark' => 'bg-transparent text-dark border-dark',
    ];

    $classes = $variantClasses[$variant] ?? $variantClasses['primary'];
@endphp

<span {{ $attributes->merge(['class' => 'px-2 py-0.5 rounded-full text-xs border font-medium inline-flex items-center ' . $classes]) }}>
    {{ $slot }}
</span>