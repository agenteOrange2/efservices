@props(['as' => 'div'])

<{{ $as }}
    {{ $attributes->class(['px-5 py-3 text-right border-t border-slate-200/60'])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}
>{{ $slot }}</{{ $as }}>
