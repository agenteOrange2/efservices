<!-- resources/views/components/step-indicator.blade.php -->
@props(['status', 'step', 'activeStep', 'label'])

@php
    $statusClasses = [
        'completed' => 'bg-green-500 border-green-500 text-white',
        'pending' => 'bg-yellow-500 border-yellow-500 text-white',
        'missing' => 'bg-red-500 border-red-500 text-white',
    ];
    
    $statusClass = $statusClasses[$status] ?? 'bg-gray-200 border-gray-300 text-gray-700';
    $isActive = $step === $activeStep;
    
    if ($isActive) {
        $statusClass .= ' ring-2 ring-offset-2 ring-primary';
    }
    
    $statusIcons = [
        'completed' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>',
        'pending' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z" clip-rule="evenodd"></path></svg>',
        'missing' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
    ];
    
    $statusIcon = $statusIcons[$status] ?? '';
    
    $tooltips = [
        'completed' => 'All information completed',
        'pending' => 'Partially completed, needs attention',
        'missing' => 'Missing required information'
    ];
    
    $tooltip = $tooltips[$status] ?? '';
@endphp

<div class="flex flex-col items-center group relative" x-data="{ showTooltip: false }">
    <button type="button" 
        class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-semibold text-sm transition-all {{ $statusClass }}"
        @click="activeTab = '{{ $label }}'"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false">
        
        @if($status === 'completed' || $status === 'pending' || $status === 'missing')
            {!! $statusIcon !!}
        @else
            {{ $step }}
        @endif
    </button>
    
    <div class="mt-2 text-xs font-medium {{ $isActive ? 'text-primary' : 'text-gray-600' }}">{{ ucfirst($label) }}</div>
    
    <!-- Tooltip -->
    <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute -top-10 z-10 w-32 p-2 bg-black bg-opacity-80 text-white text-xs rounded-md text-center">
        {{ $tooltip }}
    </div>
</div>