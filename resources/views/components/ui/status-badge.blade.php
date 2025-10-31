@props(['status', 'type' => 'default'])

@php
    $statusColors = [
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-red-100 text-red-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'expired' => 'bg-red-100 text-red-800',
        'valid' => 'bg-green-100 text-green-800',
        'invalid' => 'bg-red-100 text-red-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'processing' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'failed' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-blue-100 text-blue-800',
        'success' => 'bg-green-100 text-green-800',
        'error' => 'bg-red-100 text-red-800',
        'default' => 'bg-gray-100 text-gray-800'
    ];
    
    $statusIcons = [
        'active' => 'check-circle',
        'inactive' => 'x-circle',
        'pending' => 'clock',
        'expired' => 'alert-triangle',
        'valid' => 'check-circle',
        'invalid' => 'x-circle',
        'approved' => 'check-circle',
        'rejected' => 'x-circle',
        'processing' => 'loader',
        'completed' => 'check-circle',
        'failed' => 'x-circle',
        'warning' => 'alert-triangle',
        'info' => 'info',
        'success' => 'check-circle',
        'error' => 'x-circle',
        'default' => 'circle'
    ];
    
    $colorClass = $statusColors[strtolower($status)] ?? $statusColors['default'];
    $icon = $statusIcons[strtolower($status)] ?? $statusIcons['default'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $colorClass"]) }}>
    <x-base.lucide icon="{{ $icon }}" class="w-3 h-3 mr-1" />
    {{ ucfirst($status) }}
</span>