@props(['disabled' => false, 'formInputSize' => null, 'rounded' => null])

{{-- <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!}> --}}


<input
    data-tw-merge
    {{ $disabled ? 'disabled' : '' }} {{ $attributes->class(
            merge([
                'disabled:bg-slate-100 disabled:cursor-not-allowed',
                '[&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 [&[readonly]]:dark:border-transparent',
                'transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40',
                "[&[type='file']]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200",
                'group-[.form-inline]:flex-1',
                'group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10',
                $formInputSize == 'sm' ? 'text-xs py-1.5 px-2' : null,
                $formInputSize == 'lg' ? 'text-lg py-1.5 px-4' : null,
                $rounded ? 'rounded-full' : null,
                $attributes->whereStartsWith('class')->first(),
            ]),
        )->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>