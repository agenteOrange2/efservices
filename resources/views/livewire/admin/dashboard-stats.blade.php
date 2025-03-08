<div>

    <div class="grid grid-cols-10 gap-x-6 gap-y-10">
        <div class="col-span-12 gap-y-10 2xl:col-span-3">
            <div class="grid grid-cols-12 gap-x-6 gap-y-10">
                <div class="col-span-12 md:col-span-6 2xl:col-span-12">
                    {{-- Bloque Grafica --}}
                    <div class="box box--stacked mt-3.5 p-5">
                        <x-base.tab.group class="mt-1">
                            <x-base.tab.list class="mx-auto w-3/4 rounded-[0.6rem] border-slate-200 bg-white shadow-sm"
                                variant="boxed-tabs">
                                <x-base.tab
                                    class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current"
                                    id="example-1-tab" selected>
                                    <x-base.tab.button class="w-full whitespace-nowrap rounded-[0.6rem] text-slate-500"
                                        as="button">
                                        Daily
                                    </x-base.tab.button>
                                </x-base.tab>
                                <x-base.tab
                                    class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current"
                                    id="example-2-tab">
                                    <x-base.tab.button class="w-full whitespace-nowrap rounded-[0.6rem] text-slate-500"
                                        as="button">
                                        Weekly
                                    </x-base.tab.button>
                                </x-base.tab>
                                <x-base.tab
                                    class="bg-slate-50 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem] [&[aria-selected='true']_button]:text-current"
                                    id="example-3-tab">
                                    <x-base.tab.button class="w-full whitespace-nowrap rounded-[0.6rem] text-slate-500"
                                        as="button">
                                        Monthly
                                    </x-base.tab.button>
                                </x-base.tab>
                            </x-base.tab.list>
                            <x-base.tab.panels class="mt-8">
                                <x-base.tab.panel id="example-1" selected>
                                    <div class="relative mx-auto w-4/5 [&>div]:!h-[200px] [&>div]:sm:!h-[160px] [&>div]:2xl:!h-[200px]">
                                        <x-report-donut-chart-5 
                                            class="relative z-10" 
                                            height="h-[200px]"
                                            data-values="{{ json_encode([$activeUserCarriers, $pendingUserCarriers, $inactiveUserCarriers]) }}"
                                        />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-center">
                                                <div class="text-lg font-medium text-slate-600/90">
                                                    {{ $totalUserCarriers }}
                                                </div>
                                                <div class="mt-1 text-slate-500">Total Users</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-7 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                                        <div class="flex items-center text-slate-500">
                                            <div class="mr-2 h-2 w-2 rounded-full border border-primary/60 bg-success/60">
                                            </div>
                                            Active
                                        </div>
                                        <div class="flex items-center text-slate-500">
                                            <div class="mr-2 h-2 w-2 rounded-full border border-success/60 bg-warning/60 ">
                                            </div>
                                            Pending
                                        </div>
                                        <div class="flex items-center text-slate-500">
                                            <div class="mr-2 h-2 w-2 rounded-full border border-warning/60 bg-danger/60">
                                            </div>
                                            Inactive
                                        </div>
                                    </div>
                                    <x-base.button class="mt-9 w-full border-dashed border-slate-300 hover:bg-slate-50">
                                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ExternalLink" />
                                        Export Report
                                    </x-base.button>
                                </x-base.tab.panel>
                            </x-base.tab.panels>
                        </x-base.tab.group>
                    </div>                  
                              
                </div> 
            </div>
        </div>
        <div class="col-span-12 flex flex-col gap-y-10 2xl:col-span-7">
            {{-- Metrics General --}}
            <div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="flex flex-col gap-y-5 lg:flex-row lg:items-center">
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row sm:items-center">
                            <div class="relative">
                                <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                    icon="CalendarCheck2" />
                                <x-base.form-select class="pl-9 sm:w-44">
                                    <option value="custom-date">Custom Date</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </x-base.form-select>
                            </div>
                            <div class="relative">
                                <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                    icon="Calendar" />
                                <x-base.litepicker class="rounded-[0.3rem] pl-9 sm:w-64" />
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5 lg:ml-auto">
                            <a class="flex items-center text-slate-500" href="">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="Printer" />
                                <div
                                    class="ml-1.5 whitespace-nowrap underline decoration-slate-300 decoration-dotted underline-offset-[3px]">
                                    Export to PDF
                                </div>
                            </a>
                            <a class="flex items-center text-primary" href="">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                <div
                                    class="ml-1.5 whitespace-nowrap underline decoration-primary/30 decoration-dotted underline-offset-[3px]">
                                    Show full report
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mt-5 rounded-md border border-dashed border-slate-300/70 py-5">
                        <div class="flex flex-col md:flex-row">
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="KanbanSquare" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Total Super Admin</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">{{ $totalSuperAdmins }}</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                            11%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronUp" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="PersonStanding" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Total Carriers</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">{{ $totalCarriers }}</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                            2%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronUp" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="Banknote" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Total Drivers</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">{{ $totalUserDrivers }}</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-danger">
                                            4%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronDown" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mx-5 my-5 h-px border-t border-dashed border-slate-300/70"></div>
                        <div class="flex flex-col md:flex-row">
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="Coffee" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Documents Uploads</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">{{ $totalDocuments }}</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                            11%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronUp" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="CreditCard" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Total Vehicles</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">Pendiente</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-success">
                                            2%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronUp" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex flex-1 items-center justify-center border-dashed border-slate-300/70 py-3 last:border-0 md:border-r">
                                <div @class([
                                    'group flex items-center justify-center w-10 h-10 border rounded-full mr-5',
                                    '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                    '[&.success]:border-success/10 [&.success]:bg-success/10',
                                    ['primary', 'success'][mt_rand(0, 1)],
                                ])>
                                    <x-base.lucide icon="PackageSearch" @class([
                                        'w-5 h-5',
                                        'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                        'group-[.success]:text-success group-[.success]:fill-success/10',
                                    ]) />
                                </div>
                                <div class="flex-start flex flex-col">
                                    <div class="text-slate-500">Total Mantinence</div>
                                    <div class="mt-1.5 flex items-center">
                                        <div class="text-base font-medium">Pendiente</div>
                                        {{-- <div class="-mr-1 ml-2 flex items-center text-xs text-danger">
                                            4%
                                            <x-base.lucide class="ml-px h-4 w-4" icon="ChevronDown" />
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div>
                <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                    <div class="text-base font-medium">Revenue Analysis</div>
                </div>
                <div class="box box--stacked mt-3.5 p-5">
                    <div class="flex flex-col gap-y-5 lg:flex-row lg:items-center">
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row sm:items-center">
                            <div class="relative">
                                <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                    icon="CalendarCheck2" />
                                <x-base.form-select class="pl-9 sm:w-44">
                                    <option value="custom-date">Custom Date</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </x-base.form-select>
                            </div>
                            <div class="relative">
                                <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                    icon="Calendar" />
                                <x-base.litepicker class="rounded-[0.3rem] pl-9 sm:w-64" />
                            </div>
                        </div>
                        <div class="flex items-center gap-3.5 lg:ml-auto">
                            <a class="flex items-center text-slate-500" href="">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="Printer" />
                                <div
                                    class="ml-1.5 whitespace-nowrap underline decoration-slate-300 decoration-dotted underline-offset-[3px]">
                                    Export to PDF
                                </div>
                            </a>
                            <a class="flex items-center text-primary" href="">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                <div
                                    class="ml-1.5 whitespace-nowrap underline decoration-primary/30 decoration-dotted underline-offset-[3px]">
                                    Show full report
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="mb-1 mt-7">
                        <x-report-bar-chart-5 height="h-[220px]" />
                    </div>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                        <div class="flex items-center text-slate-500">
                            <div class="mr-2 h-2 w-2 rounded-full border border-primary/60 bg-primary/60"></div>
                            Total Revenue
                        </div>
                        <div class="flex items-center text-slate-500">
                            <div class="mr-2 h-2 w-2 rounded-full border border-slate-500/60 bg-slate-500/60"></div>
                            Customer Visits
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    {{-- Recent Carriers --}}
    <div class="w-full mt-8">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">Recent Carriers</div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <x-base.form-select class="rounded-[0.5rem] pl-9 sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </x-base.form-select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="rounded-[0.5rem] pl-9 sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked mt-3.5">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table>
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Carrier Name
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Membership
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-56 border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Status
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-32 truncate border-slate-200/80 bg-slate-50 py-5 text-right font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Created
                            </x-base.table.td>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentCarriers as $carrier)
                            <x-base.table.tr class="[&_td]:last:border-b-0">
                                <x-base.table.td class="...">
                                    <a class="flex items-center text-primary" href="">
                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                        <div class="ml-1.5 whitespace-nowrap">
                                            {{ $carrier['name'] }}
                                        </div>
                                    </a>
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $carrier['membership'] }}
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    <span class="{{ $carrier['status']['class'] }}">
                                        {{ $carrier['status']['label'] }}
                                    </span>
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $carrier['created_at'] }}
                                </x-base.table.td>
                                <!-- ... resto de la tabla ... -->
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>

                </x-base.table>
            </div>
        </div>
    </div>

    {{-- Recent User Carriers --}}
    <div class="w-full mt-8">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">Recent User Carriers</div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <x-base.form-select class="rounded-[0.5rem] pl-9 sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </x-base.form-select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="rounded-[0.5rem] pl-9 sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked mt-3.5">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table>
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Name
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Email
                            </x-base.table.td>
                            <x-base.table.td
                                class="truncate border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Carrier
                            </x-base.table.td>
                            <x-base.table.td
                                class="truncate border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Status
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-32 truncate border-slate-200/80 bg-slate-50 py-5 text-right font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Created
                            </x-base.table.td>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentUserCarriers as $userCarrier)
                            <x-base.table.tr class="[&_td]:last:border-b-0">
                                <x-base.table.td class="...">
                                    <div class="ml-1.5 whitespace-nowrap">
                                        {{ $userCarrier['name'] }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userCarrier['email'] }}
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userCarrier['carrier'] }}
                                </x-base.table.td>
                                <x-base.table.td
                                    class="rounded-l-none rounded-r-none border-x-0 border-t-0 border-dashed py-5 first:rounded-l-[0.6rem] last:rounded-r-[0.6rem]">
                                    <span class="{{ $userCarrier['status']['class'] }}">
                                        {{ $userCarrier['status']['label'] }}
                                    </span>
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userCarrier['created_at'] }}
                                </x-base.table.td>
                                <!-- ... resto de la tabla ... -->
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>

                </x-base.table>
            </div>
        </div>
    </div>

    {{-- User Driver --}}
    <div class="w-full mt-8">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">Recent User Driver</div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <x-base.form-select class="rounded-[0.5rem] pl-9 sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </x-base.form-select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="rounded-[0.5rem] pl-9 sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked mt-3.5">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table>
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Name
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Email
                            </x-base.table.td>
                            <x-base.table.td
                                class="truncate border-slate-200/80 bg-slate-50 py-5 font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Carrier
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-32 truncate border-slate-200/80 bg-slate-50 py-5 text-right font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Created
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-32 border-slate-200/80 bg-slate-50 py-5 text-center font-medium text-slate-500 first:rounded-tl-[0.6rem] last:rounded-tr-[0.6rem]">
                                Action
                            </x-base.table.td>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentUserDrivers as $userDriver)
                            <x-base.table.tr class="[&_td]:last:border-b-0">
                                <x-base.table.td class="...">
                                    <div class="ml-1.5 whitespace-nowrap">
                                        {{ $userDriver['name'] }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userDriver['email'] }}
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userDriver['carrier'] }}
                                </x-base.table.td>
                                <x-base.table.td class="...">
                                    {{ $userDriver['created_at'] }}
                                </x-base.table.td>
                                <!-- ... resto de la tabla ... -->
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>

                </x-base.table>
            </div>
        </div>
    </div>

</div>


