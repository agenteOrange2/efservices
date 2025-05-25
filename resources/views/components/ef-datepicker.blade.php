@props(['id', 'name', 'value' => null, 'required' => false, 'placeholder' => 'MM-DD-YYYY'])

@php
    $id = $id ?? $name ?? 'datepicker-' . uniqid();
@endphp

<div class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="text"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'ef-datepicker form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm']) }}
    />
</div>
