@extends('../themes/' . $activeTheme)

@section('subhead')
    <title>Tailwise - Admin Dashboard Template</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Reviews
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary"
                    >
                        <x-base.lucide
                            class="mr-2 h-4 w-4 stroke-[1.3]"
                            icon="ShieldOff"
                        />
                        Flag Review
                    </x-base.button>
                </div>
            </div>
            <div class="mt-3.5">
                <div class="box box--stacked flex flex-col">
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div>
                            <div class="relative">
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search"
                                />
                                <x-base.form-input
                                    class="rounded-[0.5rem] pl-9 sm:w-64"
                                    type="text"
                                    placeholder="Search reviews..."
                                />
                            </div>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            <x-base.menu>
                                <x-base.menu.button
                                    class="w-full sm:w-auto"
                                    as="x-base.button"
                                    variant="outline-secondary"
                                >
                                    <x-base.lucide
                                        class="mr-2 h-4 w-4 stroke-[1.3]"
                                        icon="Download"
                                    />
                                    Export
                                    <x-base.lucide
                                        class="ml-2 h-4 w-4 stroke-[1.3]"
                                        icon="ChevronDown"
                                    />
                                </x-base.menu.button>
                                <x-base.menu.items class="w-40">
                                    <x-base.menu.item>
                                        <x-base.lucide
                                            class="mr-2 h-4 w-4"
                                            icon="FileBarChart"
                                        />
                                        PDF
                                    </x-base.menu.item>
                                    <x-base.menu.item>
                                        <x-base.lucide
                                            class="mr-2 h-4 w-4"
                                            icon="FileBarChart"
                                        />
                                        CSV
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu>
                            <x-base.popover class="inline-block">
                                <x-base.popover.button
                                    class="w-full sm:w-auto"
                                    as="x-base.button"
                                    variant="outline-secondary"
                                >
                                    <x-base.lucide
                                        class="mr-2 h-4 w-4 stroke-[1.3]"
                                        icon="ArrowDownWideNarrow"
                                    />
                                    Filter
                                    <span
                                        class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium"
                                    >
                                        3
                                    </span>
                                </x-base.popover.button>
                                <x-base.popover.panel>
                                    <div class="p-2">
                                        <div>
                                            <div class="text-left text-slate-500">User</div>
                                            <x-base.tom-select
                                                class="mt-2 flex-1"
                                                data-placeholder="Search user"
                                            >
                                                @foreach ($users as $fakerKey => $faker)
                                                    <option value="{{ $fakerKey }}">
                                                        {{ $faker['name'] }}
                                                    </option>
                                                @endforeach
                                            </x-base.tom-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Review Rate
                                            </div>
                                            <x-base.form-select class="mt-2 flex-1">
                                                <option value="3+">3+</option>
                                                <option value="4+">4+</option>
                                                <option value="5">5</option>
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-4 flex items-center">
                                            <x-base.button
                                                class="ml-auto w-32"
                                                variant="secondary"
                                            >
                                                Close
                                            </x-base.button>
                                            <x-base.button
                                                class="ml-2 w-32"
                                                variant="primary"
                                            >
                                                Apply
                                            </x-base.button>
                                        </div>
                                    </div>
                                </x-base.popover.panel>
                            </x-base.popover>
                        </div>
                    </div>
                    <div class="overflow-auto xl:overflow-visible">
                        <x-base.table class="border-b border-slate-200/60">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.td
                                        class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500"
                                    >
                                        <x-base.form-check.input type="checkbox" />
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500"
                                    >
                                        Name
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500"
                                    >
                                        Comment
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500"
                                    >
                                        Review Rate
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500"
                                    >
                                        Posted Date
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="w-36 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500"
                                    >
                                        Action
                                    </x-base.table.td>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach ($reviews->take(10) as $fakerKey => $faker)
                                    <x-base.table.tr class="[&_td]:last:border-b-0">
                                        <x-base.table.td class="border-dashed py-4">
                                            <x-base.form-check.input type="checkbox" />
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="flex items-center">
                                                <div class="image-fit zoom-in h-9 w-9">
                                                    <x-base.tippy
                                                        class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                                        src="{{ Vite::asset($faker['user']['photo']) }}"
                                                        alt="Tailwise - Admin Dashboard Template"
                                                        as="img"
                                                        content="{{ $faker['user']['name'] }}"
                                                    />
                                                </div>
                                                <div class="ml-3.5">
                                                    <a
                                                        class="whitespace-nowrap font-medium"
                                                        href=""
                                                    >
                                                        {{ $faker['user']['name'] }}
                                                    </a>
                                                    <div class="mt-0.5 flex whitespace-nowrap text-xs text-slate-500">
                                                        Product:
                                                        <a
                                                            class="ml-1 block w-44 truncate"
                                                            href=""
                                                        >
                                                            {{ $faker['product']['name'] }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="w-72 truncate italic leading-relaxed">
                                                "{{ $faker['comment'] }}"
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4 text-center">
                                            <div class="flex items-center justify-center px-5">
                                                <div class="flex items-center">
                                                    <x-base.lucide
                                                        class="mr-1 h-3.5 w-3.5 fill-pending/30 text-pending"
                                                        icon="Star"
                                                    />
                                                    <x-base.lucide
                                                        class="mr-1 h-3.5 w-3.5 fill-pending/30 text-pending"
                                                        icon="Star"
                                                    />
                                                    <x-base.lucide
                                                        class="mr-1 h-3.5 w-3.5 fill-pending/30 text-pending"
                                                        icon="Star"
                                                    />
                                                    <x-base.lucide
                                                        class="mr-1 h-3.5 w-3.5 fill-pending/30 text-pending"
                                                        icon="Star"
                                                    />
                                                    <x-base.lucide
                                                        class="fill-slate/30 mr-1 h-3.5 w-3.5 text-slate-400"
                                                        icon="Star"
                                                    />
                                                </div>
                                                <div class="ml-1 text-xs text-slate-500">
                                                    ({{ mt_rand(3, 4) }}.{{ mt_rand(1, 5) }}+)
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="whitespace-nowrap">{{ $faker['date'] }}</div>
                                        </x-base.table.td>
                                        <x-base.table.td class="relative border-dashed py-4">
                                            <div class="flex items-center justify-center">
                                                <x-base.menu class="h-5">
                                                    <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                        <x-base.lucide
                                                            class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                            icon="MoreVertical"
                                                        />
                                                    </x-base.menu.button>
                                                    <x-base.menu.items class="w-40">
                                                        <x-base.menu.item>
                                                            <x-base.lucide
                                                                class="mr-2 h-4 w-4"
                                                                icon="CheckSquare"
                                                            />
                                                            Edit
                                                        </x-base.menu.item>
                                                        <x-base.menu.item class="text-danger">
                                                            <x-base.lucide
                                                                class="mr-2 h-4 w-4"
                                                                icon="Trash2"
                                                            />
                                                            Delete
                                                        </x-base.menu.item>
                                                    </x-base.menu.items>
                                                </x-base.menu>
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
                        <x-base.pagination class="mr-auto w-full flex-1 sm:w-auto">
                            <x-base.pagination.link>
                                <x-base.lucide
                                    class="h-4 w-4"
                                    icon="ChevronsLeft"
                                />
                            </x-base.pagination.link>
                            <x-base.pagination.link>
                                <x-base.lucide
                                    class="h-4 w-4"
                                    icon="ChevronLeft"
                                />
                            </x-base.pagination.link>
                            <x-base.pagination.link>...</x-base.pagination.link>
                            <x-base.pagination.link>1</x-base.pagination.link>
                            <x-base.pagination.link active>2</x-base.pagination.link>
                            <x-base.pagination.link>3</x-base.pagination.link>
                            <x-base.pagination.link>...</x-base.pagination.link>
                            <x-base.pagination.link>
                                <x-base.lucide
                                    class="h-4 w-4"
                                    icon="ChevronRight"
                                />
                            </x-base.pagination.link>
                            <x-base.pagination.link>
                                <x-base.lucide
                                    class="h-4 w-4"
                                    icon="ChevronsRight"
                                />
                            </x-base.pagination.link>
                        </x-base.pagination>
                        <x-base.form-select class="rounded-[0.5rem] sm:w-20">
                            <option>10</option>
                            <option>25</option>
                            <option>35</option>
                            <option>50</option>
                        </x-base.form-select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
