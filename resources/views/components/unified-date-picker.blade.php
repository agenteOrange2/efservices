@props(['wireModel' => null, 'placeholder' => 'Select date', 'required' => false])

@php
    $id = 'date-picker-' . uniqid();
    // Detectar si se está usando wire:model o wireModel
    $modelAttribute = $attributes->get('wire:model') ?? $wireModel;
@endphp

<div x-data="{
    displayValue: '',
    picker: null,
    modelField: '{{ $modelAttribute }}',
    isInitialized: false,
    
    init() {
        this.$nextTick(() => {
            this.initializeDatePicker();
        });
    },
    
    initializeDatePicker() {
        try {
            // Check if Pikaday is available
            if (typeof Pikaday === 'undefined') {
                console.warn('Pikaday library not loaded. Date picker will not function.');
                return;
            }
            
            // Check if $wire is available
            if (typeof $wire === 'undefined') {
                console.warn('Livewire $wire not available. Date picker may not sync properly.');
            }
            
            // Initialize Pikaday with error handling
            this.picker = new Pikaday({
                field: this.$refs.input,
                format: 'MM/DD/YYYY',
                onSelect: (date) => {
                    try {
                        // Format for display (MM/DD/YYYY)
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const year = date.getFullYear();
                        this.displayValue = `${month}/${day}/${year}`;
                        
                        // Convert to Laravel format (YYYY-MM-DD) and update Livewire
                        const laravelDate = date.toISOString().split('T')[0];
                        if (this.modelField && typeof $wire !== 'undefined') {
                            $wire.set(this.modelField, laravelDate);
                        }
                    } catch (error) {
                        console.error('Error in date selection:', error);
                    }
                }
            });
            
            this.isInitialized = true;
            
            // Load existing value if any
            this.loadExistingValue();
            
        } catch (error) {
            console.error('Error initializing date picker:', error);
        }
    },
    
    loadExistingValue() {
        try {
            if (this.modelField && typeof $wire !== 'undefined') {
                const existingValue = $wire.get(this.modelField);
                if (existingValue) {
                    const date = new Date(existingValue);
                    if (!isNaN(date.getTime())) {
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const year = date.getFullYear();
                        this.displayValue = `${month}/${day}/${year}`;
                        if (this.picker) {
                            this.picker.setDate(date);
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error loading existing date value:', error);
        }
    },
    
    clearDate() {
        try {
            this.displayValue = '';
            if (this.picker) {
                this.picker.setDate(null);
            }
            if (this.modelField && typeof $wire !== 'undefined') {
                $wire.set(this.modelField, null);
            }
        } catch (error) {
            console.error('Error clearing date:', error);
        }
    }
}" class="relative unified-date-picker">
    <div class="flex items-center">
        <input     
            x-ref="input"
            x-model="displayValue"
            type="text" 
            placeholder="{{ $placeholder }}"
            class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" 
            readonly
            {{ $required ? 'required' : '' }}
        />
        
        <div class="absolute right-2 flex items-center space-x-1">
            <button type="button" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </button>
            
            <button 
                type="button" 
                @click="clearDate()" 
                x-show="displayValue" 
                class="text-gray-400 hover:text-red-500"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
<style>
.unified-date-picker .pika-single {
    z-index: 10000 !important;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    position: absolute;
}

.unified-date-picker .pika-single.is-hidden {
    display: none;
}

.unified-date-picker .pika-single.is-bound {
    position: absolute;
    box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.506);
}

/* Ensure date picker appears above modals and other overlays */
.unified-date-picker {
    position: relative;
    z-index: 1;
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