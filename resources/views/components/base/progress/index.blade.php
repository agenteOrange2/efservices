<div
    data-tw-merge
    {{ $attributes->class(['w-full h-2 bg-slate-200 rounded'])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>
    {{ $slot }}
</div>
