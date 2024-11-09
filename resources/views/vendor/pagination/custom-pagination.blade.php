@if ($paginator->hasPages())
    <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
        <x-base.pagination class="mr-auto w-full flex-1 sm:w-auto">
            <x-base.pagination.link :disabled="$paginator->onFirstPage()" wire:click="gotoPage(1)">
                <x-base.lucide class="h-4 w-4" icon="ChevronsLeft" />
            </x-base.pagination.link>

            <x-base.pagination.link :disabled="$paginator->onFirstPage()" wire:click="previousPage">
                <x-base.lucide class="h-4 w-4" icon="ChevronLeft" />
            </x-base.pagination.link>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <x-base.pagination.link>{{ $element }}</x-base.pagination.link>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <x-base.pagination.link active>{{ $page }}</x-base.pagination.link>
                        @else
                            <x-base.pagination.link wire:click="gotoPage({{ $page }})">{{ $page }}</x-base.pagination.link>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <x-base.pagination.link :disabled="!$paginator->hasMorePages()" wire:click="nextPage">
                <x-base.lucide class="h-4 w-4" icon="ChevronRight" />
            </x-base.pagination.link>

            <x-base.pagination.link :disabled="!$paginator->hasMorePages()" wire:click="gotoPage({{ $paginator->lastPage() }})">
                <x-base.lucide class="h-4 w-4" icon="ChevronsRight" />
            </x-base.pagination.link>
        </x-base.pagination>

        <x-base.form-select class="rounded-[0.5rem] sm:w-20" wire:model="perPage">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="35">35</option>
            <option value="50">50</option>
        </x-base.form-select>
    </div>
@endif
