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
                <a class="flex items-center text-slate-500" href="#" @click.prevent="exportPdf()">
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
                            <div class="text-base font-medium" x-text="stats.totalSuperAdmins"></div>
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
                            <div class="text-base font-medium" x-text="stats.totalCarriers"></div>
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
                            <div class="text-base font-medium" x-text="stats.totalUserDrivers"></div>
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
                            <div class="text-base font-medium" x-text="stats.totalDocuments"></div>
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
                            <div class="text-base font-medium" x-text="stats.totalVehicles || 'N/A'"></div>
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
                            <div class="text-base font-medium" x-text="stats.totalMaintenance || 'N/A'"></div>
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
                <div class="w-full h-full flex items-center justify-center bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    <div class="text-slate-400">Status trend visualization</div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Sección de tablas de datos recientes --}}
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
                    <template x-for="carrier in stats.recentCarriers" :key="carrier.id">
                        <x-base.table.tr>
                            <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                <a class="flex items-center text-primary" href="#">
                                    <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                    <div class="ml-1.5 whitespace-nowrap" x-text="carrier.name">
                                    </div>
                                </a>
                            </x-base.table.td>
                            <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4" x-text="carrier.membership">
                            </x-base.table.td>
                            <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4">
                                <span :class="carrier.status.class" x-text="carrier.status.label">
                                </span>
                            </x-base.table.td>
                            <x-base.table.td class="first:rounded-l-lg last:rounded-r-lg bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] px-5 py-4 text-right" x-text="carrier.created_at">
                            </x-base.table.td>
                        </x-base.table.tr>
                    </template>
                </x-base.table.tbody>
            </x-base.table>
        </div>
    </div>
</div>