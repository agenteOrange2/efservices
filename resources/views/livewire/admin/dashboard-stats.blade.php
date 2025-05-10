<div>
    {{-- Barra de filtros y exportación --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-4">
        <div class="flex flex-col md:flex-row items-center gap-3">
            <h2 class="text-lg font-medium">Dashboard Overview</h2>
            <div class="relative">
                <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]" icon="Filter" />
                <select wire:model.live="dateRange" class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="custom">Custom Date</option>
                </select>
            </div>
            
            @if($showCustomDateFilter)
            <div class="flex items-center gap-2">
                <input 
                    type="date" 
                    wire:model="customDateStart" 
                    class="border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <span>to</span>
                <input 
                    type="date" 
                    wire:model="customDateEnd" 
                    class="border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <button 
                    wire:click="applyCustomDateFilter"
                    class="bg-primary text-white rounded-lg py-2 px-4 hover:bg-primary-focus transition duration-300"
                >
                    Apply
                </button>
            </div>
            @endif
        </div>
        
        <div class="flex items-center gap-2">
            <button
                wire:click="exportPdf"
                class="flex items-center gap-2 bg-danger text-white rounded-lg py-2 px-4 hover:bg-danger/80 transition duration-300"
            >
                <x-base.lucide icon="FileText" class="h-4 w-4" />
                Export PDF
            </button>
        </div>
    </div>

    {{-- Layout principal --}}
    <div class="grid grid-cols-12 gap-6">
        {{-- Primera columna - Stats y gráfico de usuarios --}}
        <div class="col-span-12 xl:col-span-5 2xl:col-span-4">
            {{-- Estadísticas en cajas --}}
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-2 gap-4 mb-6">
                <!-- Vehículos -->
                <div class="col-span-1 box p-5 bg-gradient-to-tr from-primary/20 via-primary/10 to-white">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary">
                            <x-base.lucide icon="Truck" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-slate-500">Total Vehicles</div>
                            <div class="text-2xl font-medium mt-1">{{ number_format($totalVehicles) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-5 text-xs sm:text-sm">
                        <div class="flex items-center gap-1">
                            <div class="bg-success/20 text-success px-2 py-0.5 rounded-full">{{ number_format($activeVehicles) }}</div>
                            <span class="text-slate-500">Active</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full">{{ number_format($suspendedVehicles) }}</div>
                            <span class="text-slate-500">Susp.</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full">{{ number_format($outOfServiceVehicles) }}</div>
                            <span class="text-slate-500">OOS</span>
                        </div>
                    </div>
                </div>

                <!-- Mantenimiento -->
                <div class="col-span-1 box p-5 bg-gradient-to-tr from-success/20 via-success/10 to-white">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-success/10 text-success">
                            <x-base.lucide icon="Wrench" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-slate-500">Maintenance</div>
                            <div class="text-2xl font-medium mt-1">{{ number_format($totalMaintenance) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-5 text-xs sm:text-sm">
                        <div class="flex items-center gap-1">
                            <div class="bg-success/20 text-success px-2 py-0.5 rounded-full">{{ number_format($completedMaintenance) }}</div>
                            <span class="text-slate-500">Done</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full">{{ number_format($upcomingMaintenance) }}</div>
                            <span class="text-slate-500">Soon</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full">{{ number_format($overdueMaintenance) }}</div>
                            <span class="text-slate-500">Late</span>
                        </div>
                    </div>
                </div>

                <!-- Carriers -->
                <div class="col-span-1 box p-5 bg-gradient-to-tr from-warning/20 via-warning/10 to-white">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-warning/10 text-warning">
                            <x-base.lucide icon="Building" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-slate-500">Total Carriers</div>
                            <div class="text-2xl font-medium mt-1">{{ number_format($totalCarriers) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-5 text-xs sm:text-sm">
                        <div class="flex items-center gap-1">
                            <div class="bg-success/20 text-success px-2 py-0.5 rounded-full">{{ number_format($activeUserCarriers) }}</div>
                            <span class="text-slate-500">Active</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full">{{ number_format($pendingUserCarriers) }}</div>
                            <span class="text-slate-500">Pending</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full">{{ number_format($inactiveUserCarriers) }}</div>
                            <span class="text-slate-500">Inactive</span>
                        </div>
                    </div>
                </div>

                <!-- Drivers -->
                <div class="col-span-1 box p-5 bg-gradient-to-tr from-primary/20 via-primary/10 to-white">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary">
                            <x-base.lucide icon="Users" class="w-6 h-6" />
                        </div>
                        <div>
                            <div class="text-slate-500">Total Drivers</div>
                            <div class="text-2xl font-medium mt-1">{{ number_format($totalUserDrivers) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-5 text-xs sm:text-sm">
                        <div class="flex items-center gap-1">
                            <div class="bg-success/20 text-success px-2 py-0.5 rounded-full">{{ number_format($activeUserDrivers) }}</div>
                            <span class="text-slate-500">Active</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full">{{ number_format($pendingUserDrivers) }}</div>
                            <span class="text-slate-500">Pending</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full">{{ number_format($inactiveUserDrivers) }}</div>
                            <span class="text-slate-500">Inactive</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gráfica donut --}}
            <div class="box box--stacked p-5">
                <x-base.tab.group>
                    <x-base.tab.list class="mx-auto w-3/4 rounded-lg border-slate-200 bg-white shadow-sm"
                        variant="boxed-tabs">
                        <x-base.tab
                            class="bg-slate-50 first:rounded-l-lg last:rounded-r-lg [&[aria-selected='true']_button]:text-current"
                            id="example-1-tab" selected>
                            <x-base.tab.button class="w-full whitespace-nowrap rounded-lg text-slate-500"
                                as="button">
                                Daily
                            </x-base.tab.button>
                        </x-base.tab>
                        <x-base.tab
                            class="bg-slate-50 first:rounded-l-lg last:rounded-r-lg [&[aria-selected='true']_button]:text-current"
                            id="example-2-tab">
                            <x-base.tab.button class="w-full whitespace-nowrap rounded-lg text-slate-500"
                                as="button">
                                Weekly
                            </x-base.tab.button>
                        </x-base.tab>
                        <x-base.tab
                            class="bg-slate-50 first:rounded-l-lg last:rounded-r-lg [&[aria-selected='true']_button]:text-current"
                            id="example-3-tab">
                            <x-base.tab.button class="w-full whitespace-nowrap rounded-lg text-slate-500"
                                as="button">
                                Monthly
                            </x-base.tab.button>
                        </x-base.tab>
                    </x-base.tab.list>
                    <x-base.tab.panels class="mt-6">
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
                            <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
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
                            <button class="mt-6 w-full border border-dashed border-slate-300 py-2 rounded-lg hover:bg-slate-50 flex items-center justify-center">
                                <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ExternalLink" />
                                Export Report
                            </button>
                        </x-base.tab.panel>
                        <x-base.tab.panel id="example-2">
                            {{-- Contenido para Weekly --}}
                            <div class="text-center py-10 text-slate-500">
                                Weekly data visualization coming soon
                            </div>
                        </x-base.tab.panel>
                        <x-base.tab.panel id="example-3">
                            {{-- Contenido para Monthly --}}
                            <div class="text-center py-10 text-slate-500">
                                Monthly data visualization coming soon
                            </div>
                        </x-base.tab.panel>
                    </x-base.tab.panels>
                </x-base.tab.group>
            </div>
        </div>
        
        {{-- Segunda columna - Métricas principales --}}
        <div class="col-span-12 xl:col-span-7 2xl:col-span-8">
            <div class="box box--stacked p-5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative">
                            <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                icon="CalendarCheck2" />
                            <select class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary sm:w-44">
                                <option value="custom-date">Custom Date</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="relative">
                            <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                                icon="Calendar" />
                            <x-base.litepicker class="py-2 px-3 pl-9 border border-slate-300 rounded-lg sm:w-64" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3.5 lg:ml-auto">
                        <a class="flex items-center text-slate-500" href="#">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="Printer" />
                            <div
                                class="ml-1.5 whitespace-nowrap underline decoration-slate-300 decoration-dotted underline-offset-[3px]">
                                Export to PDF
                            </div>
                        </a>
                        <a class="flex items-center text-primary" href="#">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                            <div
                                class="ml-1.5 whitespace-nowrap underline decoration-primary/30 decoration-dotted underline-offset-[3px]">
                                Show full report
                            </div>
                        </a>
                    </div>
                </div>
                <div class="mt-5 rounded-lg border border-dashed border-slate-300/70 py-5">
                    <div class="flex flex-col md:flex-row">
                        <div
                            class="flex flex-1 items-center justify-center py-3 md:border-r border-dashed border-slate-300/70">
                            <div class="group flex items-center justify-center w-10 h-10 border border-primary/10 bg-primary/10 rounded-full mr-5">
                                <x-base.lucide icon="KanbanSquare" class="w-5 h-5 text-primary fill-primary/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Total Super Admin</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalSuperAdmins }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex flex-1 items-center justify-center border-t md:border-t-0 md:border-r border-dashed border-slate-300/70 py-3">
                            <div class="group flex items-center justify-center w-10 h-10 border border-success/10 bg-success/10 rounded-full mr-5">
                                <x-base.lucide icon="PersonStanding" class="w-5 h-5 text-success fill-success/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Total Carriers</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalCarriers }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex flex-1 items-center justify-center border-t md:border-t-0 border-dashed border-slate-300/70 py-3">
                            <div class="group flex items-center justify-center w-10 h-10 border border-primary/10 bg-primary/10 rounded-full mr-5">
                                <x-base.lucide icon="Banknote" class="w-5 h-5 text-primary fill-primary/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Total Drivers</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalUserDrivers }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mx-5 my-5 h-px border-t border-dashed border-slate-300/70"></div>
                    <div class="flex flex-col md:flex-row">
                        <div
                            class="flex flex-1 items-center justify-center md:border-r border-dashed border-slate-300/70 py-3">
                            <div class="group flex items-center justify-center w-10 h-10 border border-success/10 bg-success/10 rounded-full mr-5">
                                <x-base.lucide icon="Coffee" class="w-5 h-5 text-success fill-success/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Documents Uploads</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalDocuments }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex flex-1 items-center justify-center border-t md:border-t-0 md:border-r border-dashed border-slate-300/70 py-3">
                            <div class="group flex items-center justify-center w-10 h-10 border border-primary/10 bg-primary/10 rounded-full mr-5">
                                <x-base.lucide icon="CreditCard" class="w-5 h-5 text-primary fill-primary/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Total Vehicles</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalVehicles ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex flex-1 items-center justify-center border-t md:border-t-0 border-dashed border-slate-300/70 py-3">
                            <div class="group flex items-center justify-center w-10 h-10 border border-success/10 bg-success/10 rounded-full mr-5">
                                <x-base.lucide icon="PackageSearch" class="w-5 h-5 text-success fill-success/10" />
                            </div>
                            <div class="flex flex-col">
                                <div class="text-slate-500">Total Maintenance</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base font-medium">{{ $totalMaintenance ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Área para gráfico de barras o líneas opcional --}}
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-medium">Vehicle Status Trend</h3>
                        <div class="flex items-center gap-2">
                            <span class="flex items-center text-xs text-slate-500">
                                <span class="inline-block w-3 h-3 bg-primary rounded-full mr-1"></span> Active
                            </span>
                            <span class="flex items-center text-xs text-slate-500">
                                <span class="inline-block w-3 h-3 bg-danger rounded-full mr-1"></span> Out of Service
                            </span>
                        </div>
                    </div>
                    <div class="h-[220px] w-full">
                        {{-- Aquí iría el componente de gráfico --}}
                        <div class="w-full h-full flex items-center justify-center bg-slate-50 rounded-lg border border-dashed border-slate-200">
                            <div class="text-slate-400">Status trend visualization</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Carriers --}}
    <div class="w-full mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="text-base font-medium">Recent Carriers</div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <select class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="py-2 px-3 pl-9 border border-slate-300 rounded-lg sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table class="border-spacing-y-[10px] border-separate -mt-2">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Carrier Name
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Membership
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Status
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 text-right font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Created
                            </x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentCarriers as $carrier)
                            <x-base.table.tr>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    <a class="flex items-center text-primary" href="#">
                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                        <div class="ml-1.5 whitespace-nowrap">
                                            {{ $carrier['name'] }}
                                        </div>
                                    </a>
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    {{ $carrier['membership'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    <span class="{{ $carrier['status']['class'] }}">
                                        {{ $carrier['status']['label'] }}
                                    </span>
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4 text-right">
                                    {{ $carrier['created_at'] }}
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
    </div>

    {{-- Recent User Carriers --}}
    <div class="w-full mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">            
            <div class="text-base font-medium">Recent User Carriers</div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <select class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="py-2 px-3 pl-9 border border-slate-300 rounded-lg sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table class="border-spacing-y-[10px] border-separate -mt-2">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Name
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Email
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Carrier
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Status
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 text-right font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Created
                            </x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentUserCarriers as $userCarrier)
                            <x-base.table.tr>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    <div class="whitespace-nowrap font-medium">
                                        {{ $userCarrier['name'] }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    {{ $userCarrier['email'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    {{ $userCarrier['carrier'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    <span class="{{ $userCarrier['status']['class'] }}">
                                        {{ $userCarrier['status']['label'] }}
                                    </span>
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4 text-right">
                                    {{ $userCarrier['created_at'] }}
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
    </div>

    {{-- Recent User Drivers --}}
    <div class="w-full mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <div class="text-base font-medium">Recent User Drivers</div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="CalendarCheck2" />
                    <select class="pl-9 pr-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]"
                        icon="Calendar" />
                    <x-base.litepicker class="py-2 px-3 pl-9 border border-slate-300 rounded-lg sm:w-64" />
                </div>
            </div>
        </div>
        <div class="box box--stacked">
            <div class="overflow-auto xl:overflow-visible">
                <x-base.table class="border-spacing-y-[10px] border-separate -mt-2">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Name
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Email
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Carrier
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 text-right font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Created
                            </x-base.table.th>
                            <x-base.table.th class="border-b-0 whitespace-nowrap px-5 py-4 text-center font-medium text-slate-500 first:rounded-l-lg last:rounded-r-lg bg-slate-50">
                                Action
                            </x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($recentUserDrivers as $userDriver)
                            <x-base.table.tr>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    <div class="whitespace-nowrap font-medium">
                                        {{ $userDriver['name'] }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    {{ $userDriver['email'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                    {{ $userDriver['carrier'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4 text-right">
                                    {{ $userDriver['created_at'] }}
                                </x-base.table.td>
                                <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button class="btn btn-sm btn-primary flex items-center">
                                            <x-base.lucide icon="Eye" class="h-4 w-4" />
                                        </button>
                                        <button class="btn btn-sm btn-success flex items-center">
                                            <x-base.lucide icon="Edit" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
        </div>
    </div>

</div>