@props(['light' => null])

<nav
    aria-label="breadcrumb"
    {{ $attributes->class(merge(['flex']))->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>
    <ol @class([
        'flex items-center text-theme-1',
        'text-white/90' => $light,
    ])>
        {{ $slot }}
    </ol>
</nav>
