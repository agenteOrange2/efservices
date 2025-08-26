@props([
    'name' => '',
    'value' => '',
    'label' => '',
    'required' => false,
    'placeholder' => 'MM/DD/YYYY',
    'minDate' => null,
    'maxDate' => null,
    'wireModel' => null,
    'id' => null,
    'class' => '',
    'disabled' => false,
    'readonly' => false,
    'helpText' => null,
    'errorField' => null
])

@php
    use App\Helpers\DateHelper;
    
    $componentId = $id ?? 'date-picker-' . uniqid();
    $inputId = $componentId . '-input';
    $errorFieldName = $errorField ?? $name;
    
    // Format the value for display
    $displayValue = $value ? DateHelper::toDisplay($value) : '';
    
    // Set default min/max dates if not provided
    $minDateFormatted = $minDate ? DateHelper::toDisplay($minDate) : null;
    $maxDateFormatted = $maxDate ? DateHelper::toDisplay($maxDate) : null;
@endphp

@vite('resources/js/unified-date-picker.js')

<div class="unified-date-picker {{ $class }}" x-data="unifiedDatePicker({
    value: '{{ $displayValue }}',
    inputId: '{{ $inputId }}',
    minDate: {{ $minDateFormatted ? "'$minDateFormatted'" : 'null' }},
    maxDate: {{ $maxDateFormatted ? "'$maxDateFormatted'" : 'null' }},
    wireModel: '{{ $attributes->wire('model')->value() }}'
})" x-init="init()">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        <input
            type="text"
            id="{{ $inputId }}"
            name="{{ $name }}"
            x-model="value"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $readonly ? 'readonly' : '' }}
            @if($wireModel)
                wire:model="{{ $wireModel }}"
            @endif
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : '' }} {{ $readonly ? 'bg-gray-50' : '' }}"
            autocomplete="off"
        >
        
        <!-- Calendar Icon -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        <!-- Clear Button -->
        <button
            type="button"
            x-show="value && !disabled && !readonly"
            @click="clearDate()"
            class="absolute inset-y-0 right-8 flex items-center pr-1 text-gray-400 hover:text-gray-600 transition-colors duration-200"
            tabindex="-1"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    
    @if($helpText)
        <p class="mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
    
    @error($errorFieldName)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
<style>
.unified-date-picker .pika-single {
    z-index: 9999;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.unified-date-picker .pika-single.is-hidden {
    display: none;
}

.unified-date-picker .pika-single.is-bound {
    position: absolute;
    box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.506);
}

@media (max-width: 640px) {
    .unified-date-picker .pika-single {
        font-size: 16px; /* Prevent zoom on iOS */
    }
    
    .unified-date-picker input {
        font-size: 16px; /* Prevent zoom on iOS */
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
@endpush