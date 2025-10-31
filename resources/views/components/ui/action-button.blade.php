@props(['href' => null, 'icon' => null, 'variant' => 'primary', 'size' => 'md', 'disabled' => false])

@php
    $variantClasses = [
        'primary' => 'border-transparent text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'warning' => 'border-transparent text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        'info' => 'border-transparent text-white bg-blue-500 hover:bg-blue-600 focus:ring-blue-500',
        'outline' => 'border-gray-300 text-gray-700 bg-transparent hover:bg-gray-50 focus:ring-blue-500',
        'ghost' => 'border-transparent text-gray-700 bg-transparent hover:bg-gray-100 focus:ring-blue-500'
    ];
    
    $sizeClasses = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm leading-4',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-4 py-2 text-base',
        'xl' => 'px-6 py-3 text-base'
    ];
    
    $iconSizes = [
        'xs' => 'w-3 h-3',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-5 h-5'
    ];
    
    $variantClass = $variantClasses[$variant] ?? $variantClasses['primary'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $iconSize = $iconSizes[$size] ?? $iconSizes['md'];
    
    $baseClasses = 'inline-flex items-center border font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    if ($disabled) {
        $variantClass = 'border-gray-300 text-gray-400 bg-gray-100 cursor-not-allowed';
    }
    
    $classes = "$baseClasses $sizeClass $variantClass";
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-base.lucide icon="{{ $icon }}" class="{{ $iconSize }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }}" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}>
        @if($icon)
            <x-base.lucide icon="{{ $icon }}" class="{{ $iconSize }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }}" />
        @endif
        {{ $slot }}
    </button>
@endif