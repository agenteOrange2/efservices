{{-- <div
    {{ $attributes->class(merge([
        'py-5 pl-5 pr-14 bg-white border border-slate-200/60 rounded-lg shadow-xl hidden flex', 
        $attributes->whereStartsWith('class')->first()]))->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}>
    {{ $slot }}
</div> --}}

<div 
    {{ $attributes->class([
        'py-5 pl-5 pr-14 rounded-lg shadow-xl flex hidden',
        'bg-green-100 border-green-200 text-green-800' => $type === 'success',
        'bg-red-100 border-red-200 text-red-800' => $type === 'error',
        'bg-yellow-100 border-yellow-200 text-yellow-800' => $type === 'warning',
    ])->merge(['class' => 'border']) }}
    id="{{ $id ?? '' }}"
>
    <svg class="w-5 h-5 mr-3">
        <use xlink:href="#icon-{{ $type }}"></use>
    </svg>
    <div>
        <div class="font-medium">{{ $message }}</div>
        @if (!empty($details))
            <div class="mt-1 text-sm">{{ $details }}</div>
        @endif
    </div>
</div>


@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@pushOnce('vendors')
    @vite('resources/js/vendors/toastify.js')
@endPushOnce
