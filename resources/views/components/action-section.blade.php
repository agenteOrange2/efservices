<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    @isset($title) {{-- Solo renderiza si $title está definido --}}
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
        </x-section-title>
    @endisset

    <div class="{{ isset($title) ? 'mt-5 md:mt-0 md:col-span-2' : 'md:col-span-3' }}">
        <div class="px-4 py-5 sm:p-6 bg-white">
            {{ $content }}
        </div>
    </div>
</div>
