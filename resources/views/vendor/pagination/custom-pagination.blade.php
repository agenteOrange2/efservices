@if ($paginator->hasPages())
    <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
        <!-- Contenedor de la paginación -->
        <nav class="flex w-full mr-0 sm:mr-auto sm:w-auto">
            <ul class="flex w-full">
                <!-- Botón para ir a la primera página -->
                <li class="flex-1 sm:flex-initial">
                    <button
                        class="transition duration-200 border py-2 rounded-md cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed min-w-0 sm:min-w-[40px] shadow-none font-normal flex items-center justify-center border-transparent text-slate-800 sm:mr-2 px-1 sm:px-3"
                        {{ $paginator->onFirstPage() ? 'disabled' : '' }} wire:click="gotoPage(1)">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 12H5m7 7l-7-7 7-7" />
                        </svg>
                    </button>
                </li>

                <!-- Botón para ir a la página anterior -->
                <li class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 sm:flex-row">
                    <x-base.pagination class="mr-auto w-full flex-1 sm:w-auto" wire:click="previousPage">
                        <x-base.pagination.link>

                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </x-base.pagination.link>
                    </x-base.pagination>
                </li>

                <!-- Páginas -->
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="flex-1 sm:flex-initial">
                            <span class="px-1 sm:px-3 text-slate-800">{{ $element }}</span>
                        </li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li class="flex-1 sm:flex-initial">
                                @if ($page == $paginator->currentPage())
                                    <x-base.button
                                        class="transition duration-200 border py-2 rounded-md cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed !box min-w-0 sm:min-w-[40px] shadow-none flex items-center justify-center border-transparent text-slate-800 sm:mr-2 px-1 sm:px-3 !box font-medium">
                                        {{ $page }}
                                    </x-base.button>
                                @else
                                    <x-base.button
                                        class="transition duration-200 border py-2 rounded-md cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed min-w-0 sm:min-w-[40px] shadow-none font-normal flex items-center justify-center border-transparent text-slate-800 sm:mr-2 px-1 sm:px-3"
                                        wire:click="gotoPage({{ $page }})">
                                        {{ $page }}
                                    </x-base.button>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                <!-- Botón para ir a la página siguiente -->

                <li class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 sm:flex-row">
                    <x-base.pagination class="mr-auto w-full flex-1 sm:w-auto" wire:click="nextPage">
                        <x-base.pagination.link>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </x-base.pagination.link>
                    </x-base.pagination>

                </li>


                <!-- Botón para ir a la última página -->
                <li class="flex-1 sm:flex-initial">
                    <button
                        class="transition duration-200 border py-2 rounded-md cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed min-w-0 sm:min-w-[40px] shadow-none font-normal flex items-center justify-center border-transparent text-slate-800 sm:mr-2 px-1 sm:px-3"
                        {{ !$paginator->hasMorePages() ? 'disabled' : '' }}
                        wire:click="gotoPage({{ $paginator->lastPage() }})">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14m-7-7l7 7-7-7" />
                        </svg>
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Selector de elementos por página -->
        <div>
            <select
                class="disabled:bg-slate-100 disabled:cursor-not-allowed transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary sm:w-20"
                wire:model.live="perPage"> <!-- IMPORTANTE: wire:model.live -->
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif
