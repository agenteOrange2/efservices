@props(['links' => []])

<nav aria-label="breadcrumb" class="hidden sm:flex">
    <ol class="flex items-center text-theme-1">
        @foreach ($links as $index => $link)
            <x-base.breadcrumb.link
                :index="$index"
                :active="isset($link['active']) && $link['active']"
                href="{{ $link['url'] ?? '#' }}">
                {{ $link['label'] }}
            </x-base.breadcrumb.link>
        @endforeach
    </ol>
</nav>
