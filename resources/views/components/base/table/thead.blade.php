@props(['variant' => null])

<thead
    data-tw-merge
    {{ $attributes->class(
            merge([
                $variant === 'light' ? 'bg-slate-200/60' : null,
                $variant === 'dark' ? 'bg-dark text-white' : null,
            ]),
        )->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>
    {{ $slot }}
</thead>
