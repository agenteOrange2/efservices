@props(['percentage' => 0, 'color' => 'blue', 'size' => 'md', 'showLabel' => true])

@php
    $percentage = max(0, min(100, $percentage)); // Ensure percentage is between 0 and 100
    
    $colorClasses = [
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-600',
        'yellow' => 'bg-yellow-600',
        'red' => 'bg-red-600',
        'purple' => 'bg-purple-600',
        'indigo' => 'bg-indigo-600',
        'gray' => 'bg-gray-600'
    ];
    
    $sizeClasses = [
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4',
        'xl' => 'h-6'
    ];
    
    $colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    
    // Auto color based on percentage
    if ($color === 'auto') {
        if ($percentage >= 80) {
            $colorClass = $colorClasses['green'];
        } elseif ($percentage >= 50) {
            $colorClass = $colorClasses['yellow'];
        } else {
            $colorClass = $colorClasses['red'];
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
        <div class="flex items-center justify-between mb-1">
            <span class="text-sm font-medium text-gray-700">{{ $slot->isNotEmpty() ? $slot : 'Progress' }}</span>
            <span class="text-sm font-semibold text-gray-900">{{ $percentage }}%</span>
        </div>
    @endif
    
    <div class="w-full bg-gray-200 rounded-full {{ $sizeClass }}">
        <div class="{{ $colorClass }} {{ $sizeClass }} rounded-full transition-all duration-500 ease-out" 
             style="width: {{ $percentage }}%"></div>
    </div>
    
    @if($percentage < 100 && $showLabel)
        <div class="mt-1 text-xs text-gray-500">
            @if($percentage >= 80)
                Almost complete
            @elseif($percentage >= 50)
                In progress
            @elseif($percentage > 0)
                Getting started
            @else
                Not started
            @endif
        </div>
    @endif
</div>