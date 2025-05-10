<div
    {{ $attributes->class(['h-px my-2 -mx-2 bg-slate-200/60'])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}>
</div>
