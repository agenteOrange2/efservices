@extends('../themes/' . $activeTheme)
@section('title', 'Prospect Drivers Report')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Prospect Drivers Report', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Prospect Drivers Report
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.reports.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Reports
                </x-base.button>
            </div>
        </div>

        <div class="mt-3.5 flex flex-col gap-8">
            <div class="box box--stacked flex flex-col p-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div>
                        <div class="relative">
                            <form action="{{ route('admin.reports.driver-prospects') }}" method="GET" id="search-form">
                                @if(!empty(request('carrier_id')))
                                    <input type="hidden" name="carrier_id" value="{{ request('carrier_id') }}">
                                @endif
                                @if(!empty(request('status')))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                @endif
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text" name="search"
                                    value="{{ request('search') }}" placeholder="Search for prospects..." />
                            </form>
                        </div>
                    </div>
                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <x-base.popover class="inline-block">
                            <x-base.popover.button class="w-full sm:w-auto" as="x-base.button"
                                variant="outline-secondary">
                                <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowDownWideNarrow" />
                                Filter Options
                                <span
                                    class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                                    {{ !empty(request('carrier_id')) || !empty(request('status')) || !empty(request('date_from')) || !empty(request('date_to')) ? count(array_filter([request('carrier_id'), request('status'), request('date_from'), request('date_to')])) : '0' }}
                                </span>
                            </x-base.popover.button>
                            <x-base.popover.panel>
                                <div class="p-2">
                                    <form method="GET" action="{{ route('admin.reports.driver-prospects') }}">
                                        <div>
                                            <div class="text-left text-slate-500">
                                                Carrier
                                            </div>
                                            <x-base.form-select name="carrier_id" class="mt-2 flex-1">
                                                <option value="">All Carriers</option>
                                                @foreach($carriers as $carrier)
                                                    <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                        {{ $carrier->name }}
                                                    </option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>

                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Status
                                            </div>
                                            <x-base.form-select name="status" class="mt-2 flex-1">
                                                <option value="">All Statuses</option>
                                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </x-base.form-select>
                                        </div>

                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Date Range
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                <div>
                                                    <label class="form-label">From</label>
                                                    <x-base.form-input type="date" name="date_from"
                                                        value="{{ request('date_from') }}" class="form-control" />
                                                </div>
                                                <div>
                                                    <label class="form-label">To</label>
                                                    <x-base.form-input type="date" name="date_to"
                                                        value="{{ request('date_to') }}" class="form-control" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center">
                                            <x-base.button class="ml-auto w-32" variant="secondary" as="a"
                                                href="{{ route('admin.reports.driver-prospects') }}">
                                                Clear
                                            </x-base.button>
                                            <x-base.button class="ml-2 w-32" variant="primary" type="submit">
                                                Apply
                                            </x-base.button>
                                        </div>
                                    </form>
                                </div>
                            </x-base.popover.panel>
                        </x-base.popover>

                        <x-base.button id="export-pdf-inline" variant="outline-secondary">
                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
                            Export PDF
                        </x-base.button>
                    </div>
                </div>
            </div>

            <div class="box box--stacked flex flex-col">
                @if($prospects->total() > 0)
                <div class="flex flex-col gap-y-2 px-5 pt-5">
                    <div class="text-base font-medium">Results <span class="text-slate-500">({{ $prospects->total() }} prospect{{ $prospects->total() !== 1 ? 's' : '' }})</span></div>
                </div>
                <div class="flex flex-col gap-y-2">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Carrier
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registration Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prospects as $prospect)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             <div class="flex items-center">
                                                <div
                                                class="w-10 h-10 rounded-full overflow-hidden mr-3 bg-slate-100 flex items-center justify-center">
                                                @if ($prospect->userDriverDetail && $prospect->userDriverDetail->getFirstMediaUrl('profile_photo_driver'))
                                                    <img src="{{ $prospect->userDriverDetail->getFirstMediaUrl('profile_photo_driver') }}"
                                                        alt="Foto de perfil" class="w-full h-full object-cover">
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        data-lucide="user"
                                                        class="lucide lucide-user stroke-[1] h-5 w-5 text-slate-500">
                                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-medium whitespace-nowrap">
                                                    {{ $prospect->user->name ?? 'N/A' }}
                                                </div>
                                                <div class="text-slate-500 whitespace-nowrap">
                                                        {{ $prospect->userDriverDetail->phone ?? ($prospect->user->phone ?? 'N/A') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="whitespace-nowrap">{{ $prospect->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="whitespace-nowrap">
                                                {{ $prospect->userDriverDetail->carrier->name ?? 'No carrier' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($prospect->isOwnerOperator())
                                                <div class="flex items-center whitespace-nowrap text-warning">
                                                    <x-base.lucide icon="Truck" class="mr-1 h-4 w-4" />Owner Operator
                                                </div>
                                            @elseif($prospect->isThirdPartyDriver())
                                                <div class="flex items-center whitespace-nowrap text-pending">
                                                    <x-base.lucide icon="Users" class="mr-1 h-4 w-4" />Third Party
                                                </div>
                                            @else
                                                <div class="flex items-center whitespace-nowrap text-primary">
                                                    <x-base.lucide icon="User" class="mr-1 h-4 w-4" />Company Driver
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($prospect->status)
                                                @case('draft')
                                                    <div class="flex items-center whitespace-nowrap text-pending">
                                                        <x-base.lucide icon="AlertCircle" class="mr-1 h-4 w-4" />Draft
                                                    </div>
                                                    @break
                                                @case('pending')
                                                    <div class="flex items-center whitespace-nowrap text-primary">
                                                        <x-base.lucide icon="Clock" class="mr-1 h-4 w-4" />Pending
                                                    </div>
                                                    @break
                                                @case('rejected')
                                                    <div class="flex items-center whitespace-nowrap text-danger">
                                                        <x-base.lucide icon="XCircle" class="mr-1 h-4 w-4" />Rejected
                                                    </div>
                                                    @break
                                                @default
                                                    <div class="flex items-center whitespace-nowrap text-slate-500">
                                                        <x-base.lucide icon="HelpCircle" class="mr-1 h-4 w-4" />{{ $prospect->status }}
                                                    </div>
                                            @endswitch
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $prospect->created_at->format('m/d/Y') }}
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center">
                                                <a href="{{ route('admin.driver-recruitment.show', $prospect->id) }}" class="flex items-center text-primary mr-2" title="View Details">
                                                    <x-base.lucide icon="Eye" class="h-4 w-4" />
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col-reverse items-center gap-y-2 p-5 sm:flex-row">
                    <div class="flex items-center">
                        <p class="text-sm text-slate-600">
                            Showing {{ $prospects->firstItem() ?? 0 }} to {{ $prospects->lastItem() ?? 0 }} of {{ $prospects->total() }} entries
                        </p>
                    </div>
                    <div class="sm:ml-auto">
                        {{ $prospects->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-10 sm:p-20">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border text-warning">
                        <x-base.lucide icon="Users" class="h-5 w-5" />
                    </div>
                    <div class="mt-2 text-center">
                        <div class="text-base font-medium">No Driver Prospects Found</div>
                        <div class="mt-1 text-slate-500">No prospects were found with the applied filters.</div>
                    </div>
                    <div class="mt-3">
                        <x-base.button variant="outline-primary" as="a" href="{{ route('admin.reports.driver-prospects') }}">
                            <x-base.lucide icon="RotateCcw" class="mr-2 h-4 w-4" />Clear Filters
                        </x-base.button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportPdfInlineBtn = document.getElementById('export-pdf-inline');
        
        function getExportUrl() {
            // Get all current query parameters from the URL
            const currentParams = new URLSearchParams(window.location.search);
            
            // Create params for export
            const params = new URLSearchParams();
            
            // Copy relevant filters from current URL
            if (currentParams.has('carrier_id')) params.append('carrier_id', currentParams.get('carrier_id'));
            if (currentParams.has('status')) params.append('status', currentParams.get('status'));
            if (currentParams.has('date_from')) params.append('date_from', currentParams.get('date_from'));
            if (currentParams.has('date_to')) params.append('date_to', currentParams.get('date_to'));
            if (currentParams.has('search')) params.append('search', currentParams.get('search'));
            
            // Base URL for PDF export
            let url = '{{ route("admin.reports.driver-prospects.pdf") }}';
            const queryString = params.toString();
            
            return queryString ? `${url}?${queryString}` : url;
        }
        
        function handleExport() {
            const button = this;
            const originalHTML = button.innerHTML;
            
            // Show loading state
            button.innerHTML = `
                <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            `;
            button.disabled = true;
            
            // Open PDF in new tab
            window.open(getExportUrl(), '_blank');
            
            // Reset button after delay
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 2000);
        }
        
        if (exportPdfInlineBtn) {
            exportPdfInlineBtn.addEventListener('click', handleExport);
        }
    });
</script>
@endpush

@endsection
