@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers Overview')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers Overview', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">
                    Approved Drivers
                </div>
                {{-- <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <a href="{{ route('admin.drivers.export') }}" class="btn btn-primary">
                        <i data-lucide="Download" class="mr-2 h-4 w-4 stroke-[1.3]"></i>
                        Export Drivers
                    </a>
                </div> --}}
            </div>
            <div class="mt-3.5 flex flex-col gap-8">
                <div class="box box--stacked flex flex-col p-5">
                    <div class="grid grid-cols-4 gap-5">
                        <!-- Clickable tab cards -->
                        <a href="{{ route('admin.drivers.index', ['tab' => 'all'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'all' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'all' ? 'text-primary' : 'text-slate-500' }}">Total Approved Drivers</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $totalDriversCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-success/10 bg-success/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-success">
                                    <i data-lucide="Users" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    All
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.drivers.index', ['tab' => 'active'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'active' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'active' ? 'text-primary' : 'text-slate-500' }}">Active Drivers</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $activeDriversCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-success/10 bg-success/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-success">
                                    <i data-lucide="UserCheck" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    Active
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.drivers.index', ['tab' => 'inactive'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'inactive' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'inactive' ? 'text-primary' : 'text-slate-500' }}">Inactive Drivers</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $inactiveDriversCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-danger/10 bg-danger/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-danger">
                                    <i data-lucide="UserMinus" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    Inactive
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.drivers.index', ['tab' => 'new'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'new' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'new' ? 'text-primary' : 'text-slate-500' }}">New Drivers (Last 30 Days)</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $newDriversCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-info/10 bg-info/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-info">
                                    <i data-lucide="UserPlus" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    New
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="box box--stacked flex flex-col">
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2">
                            <form method="GET" action="{{ route('admin.drivers.index') }}" class="relative">
                                @if(request('tab'))
                                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                                @endif
                                @if(request('carrier'))
                                    <input type="hidden" name="carrier" value="{{ request('carrier') }}">
                                @endif
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input 
                                    class="rounded-[0.5rem] pl-9 sm:w-64" 
                                    type="text" 
                                    name="search"
                                    value="{{ $search }}" 
                                    placeholder="Search drivers..." 
                                    onkeyup="if(event.key === 'Enter') this.form.submit()" />
                            </form>
                            
                            @if($search || (request('tab') && request('tab') !== 'all') || request('carrier'))
                                <a href="{{ route('admin.drivers.index') }}" 
                                   class="flex items-center justify-center w-10 h-10 rounded-[0.5rem] border border-slate-300 bg-white hover:bg-slate-50 transition-colors"
                                   title="Limpiar filtros">
                                    <x-base.lucide class="h-4 w-4 stroke-[1.3] text-slate-500" icon="X" />
                                </a>
                            @endif
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            {{-- <x-base.menu>
                                <x-base.menu.button class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
                                    Export
                                    <x-base.lucide class="ml-2 h-4 w-4 stroke-[1.3]" icon="ChevronDown" />
                                </x-base.menu.button>
                                <x-base.menu.items class="w-40">
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        <a href="{{ route('admin.drivers.export', ['format' => 'pdf']) }}">PDF</a>
                                    </x-base.menu.item>
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        <a href="{{ route('admin.drivers.export', ['format' => 'csv']) }}">CSV</a>
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu> --}}
                            {{-- <x-base.popover class="inline-block">
                                <x-base.popover.button class="w-full sm:w-auto" as="x-base.button"
                                    variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowDownWideNarrow" />
                                    Filter
                                    <span
                                        class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                                        {{ !empty($carrierFilter) ? '1' : '0' }}
                                    </span>
                                </x-base.popover.button>
                                <x-base.popover.panel>
                                    <div class="p-2">
                                        <form method="GET" action="{{ route('admin.drivers.index') }}">
                                            @if (!empty($search))
                                                <input type="hidden" name="search" value="{{ $search }}">
                                            @endif
                                            <div>
                                                <div class="text-left text-slate-500">
                                                    Carrier
                                                </div>
                                                <x-base.form-select name="carrier" class="mt-2 flex-1">
                                                    <option value="">All Carriers</option>
                                                    @foreach ($carriers as $carrier)
                                                        <option value="{{ $carrier->id }}" {{ $carrierFilter == $carrier->id ? 'selected' : '' }}>
                                                            {{ $carrier->name }}
                                                        </option>
                                                    @endforeach
                                                </x-base.form-select>
                                            </div>
                                            <div class="mt-4 flex items-center">
                                                <x-base.button class="ml-auto w-32" variant="secondary" as="a" href="{{ route('admin.drivers.index') }}">
                                                    Reset
                                                </x-base.button>
                                                <x-base.button class="ml-2 w-32" variant="primary" type="submit">
                                                    Apply
                                                </x-base.button>
                                            </div>
                                        </form>
                                    </div>
                                </x-base.popover.panel>
                            </x-base.popover> --}}
                        </div>
                    </div>
                    <div class="overflow-auto xl:overflow-visible">
                        <x-base.table class="border-b border-slate-200/60">
                            <x-base.table.thead>
                                <x-base.table.tr>                                    
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Name
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Carrier
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="w-52 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Profile Completeness
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Status
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                        Joined Date
                                    </x-base.table.td>
                                    <x-base.table.td
                                        class="w-20 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                        Action
                                    </x-base.table.td>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @forelse($drivers as $driver)
                                    <x-base.table.tr class="[&_td]:last:border-b-0">                                        
                                        <x-base.table.td class="w-80 border-dashed py-4">
                                             <div class="flex items-center">
                                                <div class="image-fit zoom-in h-9 w-9">
                                                    @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                                                    <x-base.tippy
                                                        class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                                        src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}"
                                                        alt="{{ $driver->user->name ?? 'Unknown' }}" as="img"
                                                        content="{{ $driver->user->name ?? 'Unknown' }} {{ $driver->last_name }}" />
                                                    @else
                                                    <div class="w-10 h-10 rounded-full overflow-hidden mr-3 bg-slate-100 flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            data-lucide="user"
                                                            class="lucide lucide-user stroke-[1] h-5 w-5 text-slate-500">
                                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="ml-3.5">
                                                    <a class="whitespace-nowrap font-medium"
                                                        href="{{ route('admin.drivers.show', $driver->id) }}">
                                                        {{ $driver->user->name ?? 'Unknown' }} {{ $driver->last_name }}
                                                    </a>
                                                    <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                                        {{ $driver->user->email ?? 'No email' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <a class="whitespace-nowrap font-medium" href="#">
                                                {{ $driver->carrier->name ?? 'No carrier' }}
                                            </a>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="w-40">
                                                <div class="text-xs text-slate-500">
                                                    {{ $driver->completion_percentage }}%
                                                </div>
                                                <div class="mt-1.5 flex h-1 rounded-sm border bg-slate-50">
                                                    <div class="first:rounded-l-sm last:rounded-r-sm border border-primary/20 -m-px bg-primary/40"
                                                        style="width: {{ $driver->completion_percentage }}%"></div>
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div
                                                class="flex items-center justify-center {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'text-success' : 'text-danger' }}">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="Database" />
                                                <div class="ml-1.5 whitespace-nowrap">
                                                    {{ $driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE ? 'Active' : 'Inactive' }}
                                                </div>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="border-dashed py-4">
                                            <div class="whitespace-nowrap">
                                                {{ $driver->created_at->format('M d, Y') }}
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="relative border-dashed py-4">
                                            <div class="flex items-center justify-center">
                                                <x-base.menu class="h-5">
                                                    <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                        <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                            icon="MoreVertical" />
                                                    </x-base.menu.button>
                                                    <x-base.menu.items class="w-40">
                                                        <a href="{{ route('admin.drivers.show', $driver->id) }}" class="flex items-center p-2 hover:bg-slate-200">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="Eye" />
                                                            View
                                                        </a>                                                                                                                                                                    
                                                        <a href="{{ route('admin.drivers.documents.download', $driver->id) }}" class="flex items-center p-2 hover:bg-slate-200">
                                                            <x-base.lucide class="mr-2 h-4 w-4" icon="Download" />
                                                            Documents
                                                        </a>                                                    
                                                        @if ($driver->status == App\Models\UserDriverDetail::STATUS_ACTIVE)
                                                            <form
                                                                action="{{ route('admin.drivers.deactivate', $driver->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <x-base.menu.item class="text-danger">
                                                                    <x-base.lucide class="mr-2 h-4 w-4"
                                                                        icon="UserMinus" />
                                                                    <button type="submit"
                                                                        class="bg-transparent border-0 p-0 text-inherit">Deactivate</button>
                                                                </x-base.menu.item>
                                                            </form>
                                                        @else
                                                            <form
                                                                action="{{ route('admin.drivers.activate', $driver->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <x-base.menu.item class="text-success">
                                                                    <x-base.lucide class="mr-2 h-4 w-4"
                                                                        icon="UserCheck" />
                                                                    <button type="submit"
                                                                        class="bg-transparent border-0 p-0 text-inherit">Activate</button>
                                                                </x-base.menu.item>
                                                            </form>
                                                        @endif
                                                    </x-base.menu.items>
                                                </x-base.menu>
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @empty
                                    <x-base.table.tr>
                                        <x-base.table.td colspan="7" class="text-center py-4">
                                            No approved drivers found
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @endforelse
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    <div class="w-full">
                        
                        {{ $drivers->links('custom.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
