@props(['as' => 'div'])

<{{ $as }}
    {{ $attributes->class(['flex items-center px-5 py-3 border-b border-slate-200/60'])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>{{ $slot }}</{{ $as }}>
