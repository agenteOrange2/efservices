<div
    data-tw-merge
    {{ $attributes->class([
            'py-2 px-3 bg-slate-100 border shadow-sm border-slate-200 text-slate-600',
            'group-[.input-group]:rounded-none group-[.input-group]:[&:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r',
        ])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>
    {{ $slot }}
</div>
