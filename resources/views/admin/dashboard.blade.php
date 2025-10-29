@extends('../themes/' . $activeTheme)

@section('title', 'Dashboard')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Dashboard', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                EF Services Report
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] group-[.mode--light]:!text-slate-200"
                        icon="CalendarCheck2" />
                    <x-base.form-select
                        class="rounded-[0.5rem] pl-9 group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:bg-chevron-white group-[.mode--light]:!text-slate-200 sm:w-44">
                        <option value="custom-date">Custom Date</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </x-base.form-select>
                </div>
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] group-[.mode--light]:!text-slate-200"
                        icon="Calendar" />
                    <x-base.litepicker
                        id="dashboard-date-picker"
                        class="rounded-[0.5rem] pl-9 group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200 sm:w-64" />
                </div>
            </div>
        </div>
        <div class="mt-3.5 grid grid-cols-12 gap-5">
            <!-- Carriers Card -->
            <div class="col-span-12 p-1 box box--stacked md:col-span-6 2xl:col-span-3">
                <div
                    class="-mx-1 h-[244px] overflow-hidden [&_.tns-nav]:bottom-auto [&_.tns-nav]:ml-5 [&_.tns-nav]:mt-5 [&_.tns-nav]:w-auto [&_.tns-nav_button.tns-nav-active]:w-5 [&_.tns-nav_button.tns-nav-active]:bg-white/70 [&_.tns-nav_button]:mx-0.5 [&_.tns-nav_button]:h-2 [&_.tns-nav_button]:w-2 [&_.tns-nav_button]:bg-white/40">
                    <x-base.tiny-slider config="fade">
                        <div class="px-1">
                            <div
                                class="relative flex h-full w-full flex-col overflow-hidden rounded-[0.5rem] bg-gradient-to-b from-theme-2/90 to-theme-1/[0.85] p-5">
                                <x-base.lucide
                                    class="absolute right-0 top-0 -mr-5 -mt-5 h-36 w-36 rotate-[-10deg] transform fill-white/[0.03] stroke-[0.3] text-white/20"
                                    icon="Truck" />
                                <div class="mt-12 mb-9">
                                    <div class="text-2xl font-medium leading-snug text-white">
                                        {{ number_format($statistics['carriers']['total'] ?? 0) }}
                                        <br>
                                        Carriers
                                    </div>
                                    <div class="mt-1.5 text-lg text-white/70">
                                        Total registered carriers
                                    </div>
                                </div>
                                <a class="flex items-center font-medium text-white" href="{{ route('admin.carrier.index') }}">
                                    View details
                                    <x-base.lucide class="ml-1.5 h-4 w-4" icon="MoveRight" />
                                </a>
                            </div>
                        </div>
                        <div class="px-1">
                            <div
                                class="relative flex h-full w-full flex-col overflow-hidden rounded-[0.5rem] bg-gradient-to-b from-theme-2/90 to-theme-1/90 p-5">
                                <x-base.lucide
                                    class="absolute right-0 top-0 -mr-5 -mt-5 h-36 w-36 rotate-[-10deg] transform fill-white/[0.03] stroke-[0.3] text-white/20"
                                    icon="Users" />
                                <div class="mt-12 mb-9">
                                    <div class="text-2xl font-medium leading-snug text-white">
                                        {{ number_format($statistics['drivers']['total'] ?? 0) }}
                                        <br>
                                        Drivers
                                    </div>
                                    <div class="mt-1.5 text-lg text-white/70">
                                        Active drivers in system
                                    </div>
                                </div>
                                <a class="flex items-center font-medium text-white" href="{{ route('admin.drivers.index') }}">
                                    View drivers
                                    <x-base.lucide class="ml-1.5 h-4 w-4" icon="ArrowRight" />
                                </a>
                            </div>
                        </div>
                        <div class="px-1">
                            <div
                                class="relative flex h-full w-full flex-col overflow-hidden rounded-[0.5rem] bg-gradient-to-b from-theme-2/90 to-theme-1/90 p-5">
                                <x-base.lucide
                                    class="absolute right-0 top-0 -mr-5 -mt-5 h-36 w-36 rotate-[-10deg] transform fill-white/[0.03] stroke-[0.3] text-white/20"
                                    icon="Car" />
                                <div class="mt-12 mb-9">
                                    <div class="text-2xl font-medium leading-snug text-white">
                                        {{ number_format($statistics['vehicles']['total'] ?? 0) }}
                                        <br>
                                        Vehicles
                                    </div>
                                    <div class="mt-1.5 text-lg text-white/70">
                                        Fleet vehicles registered
                                    </div>
                                </div>
                                <a class="flex items-center font-medium text-white" href="{{ route('admin.vehicles.index') }}">
                                    View fleet
                                    <x-base.lucide class="ml-1.5 h-4 w-4" icon="ArrowRight" />
                                </a>
                            </div>
                        </div>
                    </x-base.tiny-slider>
                </div>
            </div>

            <!-- Carriers Statistics -->
            <div class="flex flex-col col-span-12 p-5 box box--stacked md:col-span-6 2xl:col-span-3">
                <x-base.menu class="absolute top-0 right-0 mt-5 mr-5">
                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                        <x-base.lucide class="w-6 h-6 fill-slate-400/70 stroke-slate-400/70" icon="MoreVertical" />
                    </x-base.menu.button>
                    <x-base.menu.items class="w-40">
                        <x-base.menu.item as="a" href="{{ route('admin.carrier.index') }}">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" /> View Carrier
                        </x-base.menu.item>
                    </x-base.menu.items>
                </x-base.menu>
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-12 h-12 border rounded-full border-primary/10 bg-primary/10">
                        <x-base.lucide class="w-6 h-6 fill-primary/10 text-primary" icon="Truck" />
                    </div>
                    <div class="ml-4">
                        <div class="text-base font-medium">{{ number_format($statistics['carriers']['total'] ?? 0) }} Carriers Registered</div>
                        <div class="mt-0.5 text-slate-500">
                            {{ number_format($statistics['carriers']['active'] ?? 0) }} active,
                            {{ number_format($statistics['carriers']['inactive'] ?? 0) }} inactive
                        </div>
                    </div>
                </div>
                <div class="relative mt-5 mb-6 overflow-hidden">
                    <div
                        class="absolute inset-0 my-auto h-px whitespace-nowrap text-xs leading-[0] tracking-widest text-slate-400/60">
                        .......................................................................
                    </div>
                    <canvas id="carriersChart" class="relative z-10 -ml-1.5" height="100"></canvas>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-primary/70"></div>
                        <div class="ml-2.5">Active Carriers</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                        <div class="ml-2.5">Inactive</div>
                    </div>
                </div>
            </div>

            <!-- Drivers Statistics -->
            <div class="flex flex-col col-span-12 p-5 box box--stacked md:col-span-6 2xl:col-span-3">
                <x-base.menu class="absolute top-0 right-0 mt-5 mr-5">
                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                        <x-base.lucide class="w-6 h-6 fill-slate-400/70 stroke-slate-400/70" icon="MoreVertical" />
                    </x-base.menu.button>
                    <x-base.menu.items class="w-40">
                        <x-base.menu.item as="a" href="{{ route('admin.drivers.index') }}">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" /> View Drivers
                        </x-base.menu.item>
                    </x-base.menu.items>
                </x-base.menu>
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-12 h-12 border rounded-full border-success/10 bg-success/10">
                        <x-base.lucide class="w-6 h-6 fill-success/10 text-success" icon="Users" />
                    </div>
                    <div class="ml-4">
                        <div class="text-base font-medium">
                            {{ number_format($statistics['drivers']['total'] ?? 0) }} Total Drivers
                        </div>
                        <div class="mt-0.5 text-slate-500">
                            {{ number_format($statistics['drivers']['active'] ?? 0) }} active,
                            {{ number_format($statistics['drivers']['inactive'] ?? 0) }} inactive
                        </div>
                    </div>
                </div>
                <div class="relative mt-5 mb-6 overflow-hidden">
                    <div
                        class="absolute inset-0 my-auto h-px whitespace-nowrap text-xs leading-[0] tracking-widest text-slate-400/60">
                    </div>
                    <canvas id="driversChart" class="relative z-10 -ml-1.5" height="100"></canvas>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-success/70"></div>
                        <div class="ml-2.5">Active Drivers</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                        <div class="ml-2.5">Inactive</div>
                    </div>
                </div>
            </div>

            <!-- Vehicles Statistics -->
            <div class="flex flex-col col-span-12 p-5 box box--stacked md:col-span-6 2xl:col-span-3">
                <x-base.menu class="absolute top-0 right-0 mt-5 mr-5">
                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                        <x-base.lucide class="w-6 h-6 fill-slate-400/70 stroke-slate-400/70" icon="MoreVertical" />
                    </x-base.menu.button>
                    <x-base.menu.items class="w-40">
                        <x-base.menu.item as="a" href="{{ route('admin.vehicles.index') }}">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" /> View Vehicles
                        </x-base.menu.item>
                    </x-base.menu.items>
                </x-base.menu>
                <div class="flex items-center">
                    <div
                        class="flex items-center justify-center w-12 h-12 border rounded-full border-info/10 bg-info/10">
                        <x-base.lucide class="w-6 h-6 fill-info/10 text-info" icon="Car" />
                    </div>
                    <div class="ml-4">
                        <div class="text-base font-medium">
                            {{ number_format($statistics['vehicles']['total'] ?? 0) }} Total Vehicles
                        </div>
                        <div class="mt-0.5 text-slate-500">
                            {{ number_format($statistics['vehicles']['active'] ?? 0) }} active,
                            {{ number_format($statistics['vehicles']['inactive'] ?? 0) }} inactive
                        </div>
                    </div>
                </div>
                <div class="relative mt-5 mb-6">
                    <canvas id="vehiclesChart" class="relative z-10" height="100"></canvas>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-success/70"></div>
                        <div class="ml-2.5">Active Vehicles</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                        <div class="ml-2.5">Inactive</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Insights Section -->
    <!-- <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">Performance Insights</div>                
                <div class="flex gap-x-3 gap-y-2 md:ml-auto">
                    <x-base.button class="rounded-[0.5rem] bg-white text-slate-600" data-carousel="important-notes"
                        data-target="prev">
                        <span class="flex h-5 w-3.5 items-center justify-center">
                            <x-base.lucide class="w-4 h-4" icon="ChevronLeft" />
                        </span>
                    </x-base.button>
                    <x-base.button class="rounded-[0.5rem] bg-white text-slate-600" data-carousel="important-notes"
                        data-target="next">
                        <span class="flex h-5 w-3.5 items-center justify-center">
                            <x-base.lucide class="w-4 h-4" icon="ChevronRight" />
                        </span>
                    </x-base.button>
                </div>
            </div>
            <div class="-mx-2.5 mt-3.5">
                <x-base.tiny-slider config="performance-insight-slider-config">
                    @foreach ($ecommerce as $fakerKey => $faker)
                        <div class="px-2.5 pb-3">
                            <div class="relative p-5 box box--stacked">
                                <div class="flex items-center">                                    
                                    <div @class([
                                        'group flex items-center justify-center w-10 h-10 border rounded-full',
                                        '[&.primary]:border-primary/10 [&.primary]:bg-primary/10',
                                        '[&.success]:border-success/10 [&.success]:bg-success/10',
                                        ['primary', 'success'][mt_rand(0, 1)],
                                    ])>
                                        <x-base.lucide :icon="$faker['icon']" @class([
                                            'w-5 h-5',
                                            'group-[.primary]:text-primary group-[.primary]:fill-primary/10',
                                            'group-[.success]:text-success group-[.success]:fill-success/10',
                                        ]) />
                                    </div>
                                    <div class="flex ml-auto">
                                        <div class="w-8 h-8 image-fit zoom-in">
                                            <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center">
                                                <x-base.lucide class="w-4 h-4 text-slate-500" icon="User" />
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 -ml-3 image-fit zoom-in">                                            
                                            <div class="w-8 h-8 bg-slate-300 rounded-full flex items-center justify-center">
                                                <x-base.lucide class="w-4 h-4 text-slate-600" icon="User" />
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 -ml-3 image-fit zoom-in">                                            
                                            <div class="w-8 h-8 bg-slate-400 rounded-full flex items-center justify-center">
                                                <x-base.lucide class="w-4 h-4 text-slate-700" icon="User" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-11">
                                    <div class="text-base font-medium">{{ $faker['title'] }}</div>
                                    <div class="mt-0.5 text-slate-500">
                                        {{ $faker['subtitle'] }}
                                    </div>
                                </div>
                                <a class="flex items-center pt-4 mt-4 font-medium border-t border-dashed text-primary"
                                    href="">
                                    {{ $faker['link'] }}
                                    <x-base.lucide class="ml-1.5 h-4 w-4" icon="ArrowRight" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </x-base.tiny-slider>
            </div>
        </div> -->

    <!-- Recent Activity Section -->
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">Recent Activity</div>
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
        <div class="mt-2 overflow-auto lg:overflow-visible">
            <x-base.table class="border-separate border-spacing-y-[10px]">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th class="box rounded-l-[0.6rem] rounded-r-none border-r-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Make & Model
                        </x-base.table.th>
                        <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Carrier
                        </x-base.table.th>
                        <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Assignment Type
                        </x-base.table.th>
                        <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Status
                        </x-base.table.th>
                        <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Registration Date
                        </x-base.table.th>
                        <x-base.table.th class="box rounded-l-none rounded-r-[0.6rem] border-l-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                            Actions
                        </x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @if(isset($recentRecords['vehicles']) && count($recentRecords['vehicles']) > 0)
                    @foreach ($recentRecords['vehicles'] as $vehicle)
                    <x-base.table.tr>
                        <x-base.table.td
                            class="box rounded-l-[0.6rem] rounded-r-none border-r-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="flex items-center">
                                <x-base.lucide class="h-6 w-6 fill-primary/10 stroke-[0.8] text-theme-1"
                                    icon="Car" />
                                <div class="ml-3.5">
                                    <div class="font-medium whitespace-nowrap">
                                        {{ $vehicle['make_model'] }}
                                    </div>
                                    <div class="mt-1 text-xs whitespace-nowrap text-slate-500">
                                        Vehicle Registration
                                    </div>
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-60 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Carrier Name
                            </div>
                            <div class="flex items-center text-primary">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="Building2" />
                                <div class="ml-1.5 whitespace-nowrap">
                                    {{ $vehicle['carrier_name'] }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1.5 whitespace-nowrap text-xs text-slate-500">
                                Driver Assignment
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 bg-primary/10 rounded-full flex items-center justify-center">
                                    <x-base.lucide class="w-3 h-3 text-primary"
                                        icon="Package" />
                                </div>
                                <div class="ml-2 text-sm capitalize">
                                    {{ str_replace('_', ' ', $vehicle['assignment_type']) }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Vehicle Status
                            </div>
                            <div class="flex items-center {{ strtolower($vehicle['status']) === 'active' ? 'text-success' : (strtolower($vehicle['status']) === 'pending' ? 'text-warning' : 'text-danger') }}">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                    icon="{{ strtolower($vehicle['status']) === 'active' ? 'CheckCircle' : (strtolower($vehicle['status']) === 'pending' ? 'Clock' : 'XCircle') }}" />
                                <div class="ml-1.5 whitespace-nowrap">
                                    {{ $vehicle['status'] }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Registered On
                            </div>
                            <div class="whitespace-nowrap">{{ $vehicle['registration_date'] }}</div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box relative w-20 rounded-l-none rounded-r-[0.6rem] border-l-0 py-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="flex items-center justify-center">
                                <x-base.menu class="h-5">
                                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                                        <x-base.lucide class="w-5 h-5 fill-slate-400/70 stroke-slate-400/70"
                                            icon="MoreVertical" />
                                    </x-base.menu.button>
                                    <x-base.menu.items class="w-40">
                                        <x-base.menu.item as="a" href="{{ route('admin.vehicles.show', $vehicle['id']) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" />
                                            View Details
                                        </x-base.menu.item>
                                        <x-base.menu.item as="a" href="{{ route('admin.vehicles.edit', $vehicle['id']) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                                            Edit Vehicle
                                        </x-base.menu.item>
                                    </x-base.menu.items>
                                </x-base.menu>
                            </div>
                        </x-base.table.td>
                    </x-base.table.tr>
                    @endforeach
                    @else
                    <x-base.table.tr>
                        <x-base.table.td colspan="6" class="box rounded-[0.6rem] shadow-[5px_3px_5px_#00000005] text-center py-8">
                            <div class="flex flex-col items-center">
                                <x-base.lucide class="h-12 w-12 text-slate-400 mb-3" icon="Car" />
                                <div class="text-slate-500">No recent vehicle registrations found</div>
                            </div>
                        </x-base.table.td>
                    </x-base.table.tr>
                    @endif
                </x-base.table.tbody>
            </x-base.table>
        </div>
    </div>
</div>

<!-- Recent Carriers Section -->
<div class="col-span-12 mt-8">
    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
        <div class="text-base font-medium">Recent Carries</div>
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
            <div class="relative">
                <x-base.button as="a" href="" variant="outline-primary" size="sm">
                    View All Carriers
                </x-base.button>
            </div>
        </div>
    </div>
    <div class="mt-2 overflow-auto lg:overflow-visible">
        <x-base.table class="border-separate border-spacing-y-[10px]">
            <x-base.table.thead>
                <x-base.table.tr>
                    <x-base.table.th class="box rounded-l-[0.6rem] rounded-r-none border-r-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        Carrier Name
                    </x-base.table.th>
                    <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        State
                    </x-base.table.th>
                    <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        Plan
                    </x-base.table.th>
                    <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        Status
                    </x-base.table.th>
                    <x-base.table.th class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        Registration Date
                    </x-base.table.th>
                    <x-base.table.th class="box rounded-l-none rounded-r-[0.6rem] border-l-0 shadow-[5px_3px_5px_#00000005] whitespace-nowrap">
                        Actions
                    </x-base.table.th>
                </x-base.table.tr>
            </x-base.table.thead>
            <x-base.table.tbody>
                @if(isset($recentRecords['carriers']) && count($recentRecords['carriers']) > 0)
                @foreach($recentRecords['carriers'] as $carrier)
                <x-base.table.tr>
                    <x-base.table.td
                        class="box rounded-l-[0.6rem] rounded-r-none border-r-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="flex items-center">
                            <x-base.lucide class="h-6 w-6 fill-primary/10 stroke-[0.8] text-theme-1"
                                icon="User" />
                            <div class="ml-3.5">
                                <div class="font-medium whitespace-nowrap">
                                    {{ $carrier['name'] }}
                                </div>
                            </div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td
                        class="box w-60 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            State
                        </div>
                        <div class="flex items-center text-primary">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="MapPin" />
                            <div class="ml-1.5 whitespace-nowrap">
                                {{ $carrier['state'] }}
                            </div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td
                        class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="mb-1.5 whitespace-nowrap text-xs text-slate-500">
                            Membership Plan
                        </div>
                        <div class="flex items-center">
                            <div class="w-5 h-5 bg-primary/10 rounded-full flex items-center justify-center">
                                <x-base.lucide class="w-3 h-3 text-primary"
                                    icon="Package" />
                            </div>
                            <div class="ml-2 text-sm capitalize">
                                {{ str_replace('_', ' ', $carrier['plan']) }}
                            </div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td
                        class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            Carrier Status
                        </div>
                        <div class="flex items-center {{ strtolower($carrier['status']) === 'active' ? 'text-success' : (strtolower($carrier['status']) === 'pending' ? 'text-warning' : 'text-danger') }}">
                            <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                icon="{{ strtolower($carrier['status']) === 'active' ? 'CheckCircle' : (strtolower($carrier['status']) === 'pending' ? 'Clock' : 'XCircle') }}" />
                            <div class="ml-1.5 whitespace-nowrap">
                                {{ $carrier['status'] }}
                            </div>
                        </div>
                    </x-base.table.td>
                    <x-base.table.td
                        class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                            Registered On
                        </div>
                        <div class="whitespace-nowrap">{{ $carrier['registration_date'] }}</div>
                    </x-base.table.td>
                    <x-base.table.td
                        class="box relative w-20 rounded-l-none rounded-r-[0.6rem] border-l-0 py-0 shadow-[5px_3px_5px_#00000005]">
                        <div class="flex items-center justify-center">
                            <x-base.menu class="h-5">
                                <x-base.menu.button class="w-5 h-5 text-slate-500">
                                    <x-base.lucide class="w-5 h-5 fill-slate-400/70 stroke-slate-400/70"
                                        icon="MoreVertical" />
                                </x-base.menu.button>
                                <x-base.menu.items class="w-40">
                                    <x-base.menu.item as="a" href="{{ route('admin.carrier.show', $carrier['slug']) }}">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" />
                                        View Details
                                    </x-base.menu.item>
                                    <x-base.menu.item as="a" href="{{ route('admin.carrier.edit', $carrier['slug']) }}">
                                        <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                                        Edit Vehicle
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu>
                        </div>
                    </x-base.table.td>
                </x-base.table.tr>
                @endforeach
                @else
                <x-base.table.tr>
                    <x-base.table.td colspan="6" class="box rounded-[0.6rem] shadow-[5px_3px_5px_#00000005] text-center py-8">
                        <div class="flex flex-col items-center">
                            <x-base.lucide class="h-12 w-12 text-slate-400 mb-3" icon="Car" />
                            <div class="text-slate-500">No recent carrier registrations found</div>
                        </div>
                    </x-base.table.td>
                </x-base.table.tr>
                @endif
            </x-base.table.tbody>
        </x-base.table>
    </div>
</div>

<!-- Recent Drivers Section -->
<div class="col-span-12 mt-8">
    <div class="flex items-center h-10">
        <div class="text-lg font-medium truncate mr-5">
            Recent Drivers
        </div>
        <a class="ml-auto flex items-center text-primary" href="{{ route('admin.drivers.index') }}">
            <x-base.lucide class="w-4 h-4 mr-3" icon="RefreshCcw" />
            Reload Data
        </a>
    </div>
    <div class="mt-3">
        <div class="mt-2 overflow-auto lg:overflow-visible">
            <x-base.table class="border-spacing-y-[10px] border-separate -mt-2">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Full Name
                            </div>
                        </x-base.table.th>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Carrier
                            </div>
                        </x-base.table.th>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Email
                            </div>
                        </x-base.table.th>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Status
                            </div>
                        </x-base.table.th>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Application Status
                            </div>
                        </x-base.table.th>
                        <x-base.table.th
                            class="border-b-0 whitespace-nowrap px-0 py-0 font-medium first:rounded-l-md first:px-4 last:rounded-r-md last:px-4">
                            <div
                                class="flex items-center justify-center rounded-[0.6rem] bg-slate-50 px-3.5 py-2.5 text-slate-600">
                                Actions
                            </div>
                        </x-base.table.th>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @if(isset($recentRecords['drivers']) && count($recentRecords['drivers']) > 0)
                    @foreach($recentRecords['drivers'] as $driver)
                    <x-base.table.tr class="intro-x">
                        <x-base.table.td
                            class="box w-40 rounded-l-[0.6rem] rounded-r-none border-r-0 py-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l-0">
                            <div class="flex items-center">
                                <div class="w-9 h-9 image-fit zoom-in">
                                    <x-base.tippy class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                        src="{{ $driver['profile_photo_url'] ?? asset('build/default_profile.png') }}"
                                        as="img"
                                        alt="{{ $driver['full_name'] }}"
                                        content="{{ $driver['full_name'] }}" />
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium whitespace-nowrap">
                                        {{ $driver['full_name'] }}
                                    </div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                        ID: {{ $driver['id'] }}
                                    </div>
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Carrier
                            </div>
                            <div class="flex items-center">
                                <div class="w-5 h-5 bg-primary/10 rounded-full flex items-center justify-center">
                                    <x-base.lucide class="w-3 h-3 text-primary"
                                        icon="Truck" />
                                </div>
                                <div class="ml-2 text-sm">
                                    {{ $driver['carrier_name'] }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Email
                            </div>
                            <div class="whitespace-nowrap">{{ $driver['email'] }}</div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Driver Status
                            </div>
                            <div class="flex items-center {{ strtolower($driver['status']) === 'active' ? 'text-success' : (strtolower($driver['status']) === 'inactive' ? 'text-warning' : 'text-danger') }}">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                    icon="{{ strtolower($driver['status']) === 'active' ? 'CheckCircle' : (strtolower($driver['status']) === 'inactive' ? 'Clock' : 'XCircle') }}" />
                                <div class="ml-1.5 whitespace-nowrap">
                                    {{ $driver['status'] }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                Application Status
                            </div>
                            <div class="flex items-center {{ strtolower($driver['application_completed']) === 'completed' ? 'text-success' : 'text-warning' }}">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                    icon="{{ strtolower($driver['application_completed']) === 'completed' ? 'CheckCircle' : 'Clock' }}" />
                                <div class="ml-1.5 whitespace-nowrap">
                                    {{ $driver['application_completed'] }}
                                </div>
                            </div>
                        </x-base.table.td>
                        <x-base.table.td
                            class="box relative w-20 rounded-l-none rounded-r-[0.6rem] border-l-0 py-0 shadow-[5px_3px_5px_#00000005]">
                            <div class="flex items-center justify-center">
                                <x-base.menu class="h-5">
                                    <x-base.menu.button class="w-5 h-5 text-slate-500">
                                        <x-base.lucide class="w-5 h-5 fill-slate-400/70 stroke-slate-400/70"
                                            icon="MoreVertical" />
                                    </x-base.menu.button>
                                    <x-base.menu.items class="w-40">
                                        <x-base.menu.item as="a" href="{{ route('admin.driver-recruitment.show', $driver['id']) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Eye" />
                                            View Details
                                        </x-base.menu.item>
                                        @if($driver['carrier_slug'])
                                        <x-base.menu.item as="a" href="http://efservices.la/admin/carrier/{{ $driver['carrier_slug'] }}/drivers/{{ $driver['id'] }}/edit">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                                            Edit Driver
                                        </x-base.menu.item>
                                        @endif
                                    </x-base.menu.items>
                                </x-base.menu>
                            </div>
                        </x-base.table.td>
                    </x-base.table.tr>
                    @endforeach
                    @else
                    <x-base.table.tr>
                        <x-base.table.td colspan="6" class="box rounded-[0.6rem] shadow-[5px_3px_5px_#00000005] text-center py-8">
                            <div class="flex flex-col items-center">
                                <x-base.lucide class="h-12 w-12 text-slate-400 mb-3" icon="Users" />
                                <div class="text-slate-500">No recent driver registrations found</div>
                            </div>
                        </x-base.table.td>
                    </x-base.table.tr>
                    @endif
                </x-base.table.tbody>
            </x-base.table>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Safe JSON parsing function
        function safeParseChartData(data, fallback) {
            try {
                return data && typeof data === 'object' ? data : fallback;
            } catch (e) {
                console.error('Error parsing chart data:', e);
                return fallback;
            }
        }

        // Default chart data structure
        const defaultChartData = {
            labels: ['No Data'],
            datasets: [{
                data: [1],
                backgroundColor: ['rgba(156, 163, 175, 0.8)']
            }]
        };

        // Carriers Chart (Doughnut)
        const carriersCtx = document.getElementById('carriersChart');
        if (carriersCtx) {
            const carriersData = safeParseChartData(@json($chartData['carriers'] ?? null), defaultChartData);
            new Chart(carriersCtx, {
                type: 'doughnut',
                data: carriersData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Drivers Chart (Doughnut)
        const driversCtx = document.getElementById('driversChart');
        if (driversCtx) {
            const driversData = safeParseChartData(@json($chartData['drivers'] ?? null), defaultChartData);
            new Chart(driversCtx, {
                type: 'doughnut',
                data: driversData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Vehicles Chart (Doughnut)
        const vehiclesCtx = document.getElementById('vehiclesChart');
        if (vehiclesCtx) {
            const vehiclesData = safeParseChartData(@json($chartData['vehicles'] ?? null), defaultChartData);
            new Chart(vehiclesCtx, {
                type: 'doughnut',
                data: vehiclesData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endsection