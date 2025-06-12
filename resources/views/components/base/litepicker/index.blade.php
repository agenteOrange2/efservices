@php
    // Define default date format
    $dateFormat = 'MM/DD/YYYY';
    
    // Handle date value conversion if needed
    $dateValue = $attributes['value'] ?? null;
    if ($dateValue && $dateValue instanceof \DateTime) {
        $dateValue = $dateValue->format('m/d/Y');
        $attributes = $attributes->merge(['value' => $dateValue]);
    }
@endphp

<x-base.form-input
    type="text"
    data-single-mode="true"
    data-format="{{ $dateFormat }}"
    {{ $attributes->class(merge(['datepicker', $attributes->whereStartsWith('class')->first()]))->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
/>

@pushOnce('styles')
    @vite('resources/css/vendors/litepicker.css')
@endPushOnce

@pushOnce('vendors')
    @vite('resources/js/vendors/dayjs.js')
    @vite('resources/js/vendors/litepicker.js')
@endPushOnce

@pushOnce('scripts')
    @vite('resources/js/components/base/litepicker.js')
@endPushOnce
