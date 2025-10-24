@extends('../themes/' . $activeTheme)
@section('title', 'Companies')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Companies', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
            <h2 class="text-lg font-medium">
                Companies
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.companies.create') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add New Company
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Filter Companies</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.companies.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-base.form-label for="search">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Company name, address, contact..." />
                    </div>
                    <div>
                        <x-base.form-label for="state">State</x-base.form-label>
                        <x-base.form-select name="state" id="state">
                            <option value="">All States</option>
                            @foreach($allStates as $state)
                                <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    <div>
                        <x-base.form-label for="city">City</x-base.form-label>
                        <x-base.form-select name="city" id="city">
                            <option value="">All Cities</option>
                            @foreach($allCities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </x-base.form-select>
                    </div>
                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                            Apply Filters
                        </x-base.button>
                    </div>
                </form>
            </div>
            <div class="w-full">
                
            </div>
        </div>

        <!-- Lista de companies -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Companies ({{ $companies->count() }})</h3>
                </div>
            </div>

            @if($companies->count() > 0)
                <div class="box-body p-0">
                    <div class="overflow-x-auto">
                        <x-base.table class="border-separate border-spacing-y-[10px]">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Company Name
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Contact Person
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Location
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Phone
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Email
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Drivers
                                    </x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">
                                        Actions
                                    </x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>                                                                   
                                @forelse ($companies as $company)
                                    <x-base.table.tr>
                                        <x-base.table.td class="px-6 py-4">
                                            <div class="font-medium">{{ $company->company_name }}</div>
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4">
                                            {{ $company->contact ?? '---' }}
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4">
                                            <div class="text-sm">
                                                @if($company->city || $company->state)
                                                    {{ $company->city }}{{ $company->city && $company->state ? ', ' : '' }}{{ $company->state }}
                                                @else
                                                    ---
                                                @endif
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4">
                                            {{ $company->phone ?? '---' }}
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4">
                                            {{ $company->email ?? '---' }}
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-2 text-slate-500" icon="users" />
                                                <span class="font-medium">{{ $company->driver_employment_companies_count ?? 0 }}</span>
                                            </div>
                                        </x-base.table.td>
                                        <x-base.table.td>
                                            <x-base.menu class="h-5">
                                                <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                    <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                        icon="MoreVertical" />
                                                </x-base.menu.button>

                                                <x-base.menu.items class="w-40">
                                                    <div class="flex flex-col gap-3">
                                                        <a href="{{ route('admin.companies.show', $company->id) }}" 
                                                           class="flex mr-1 text-primary" title="View Company">
                                                           <x-base.lucide class="w-4 h-4 mr-3" icon="eye" />
                                                           View                                                      
                                                        </a>
                                                        <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-sm btn-primary flex">
                                                            <x-base.lucide class="w-4 h-4 mr-3" icon="edit" />
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this company?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger flex w-full">
                                                                <x-base.lucide class="w-4 h-4 mr-3" icon="trash-2" />
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </x-base.menu.items>
                                            </x-base.menu>
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @empty
                                    <x-base.table.tr>
                                        <x-base.table.td colspan="7" class="text-center">
                                            <div class="flex flex-col items-center justify-center py-16">
                                                <x-base.lucide class="h-8 w-8 text-slate-400" icon="Building" />
                                                No Companies found
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @endforelse
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                </div>
            @else
                <div class="box-body">
                    <div class="flex flex-col items-center justify-center py-16">
                        <x-base.lucide class="h-8 w-8 text-slate-400" icon="Building" />
                        <div class="mt-5 text-slate-500">
                            No companies found
                        </div>
                        <x-base.button as="a" href="{{ route('admin.companies.create') }}" class="mt-5">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                            Add Company
                        </x-base.button>
                    </div>
                </div>
            @endif
            
            {{-- Pagination --}}
            @if($companies->hasPages())
                <div class="mt-5">
                    {{ $companies->appends(request()->query())->links('custom.pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
