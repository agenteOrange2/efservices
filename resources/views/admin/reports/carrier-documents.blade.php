@extends('../themes/' . $activeTheme)
@section('title', 'Carrier Documents Report')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Carrier Documents Report', 'active' => true],
    ];
    // Definir el tab actual
    $currentTab = request('tab', 'all');
@endphp

@section('subcontent')
<div class="gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Carrier Documents Report
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.reports.index') }}" class="w-full sm:w-auto" variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Reports
                </x-base.button>
                <x-base.button onclick="exportToPDF()" class="w-full sm:w-auto" variant="primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                    Export PDF
                </x-base.button>
                <x-base.menu>
                    <x-base.menu.button class="w-full px-2 sm:w-auto" as="x-base.button">
                        <x-base.lucide class="h-4 w-4" icon="Filter" />
                    </x-base.menu.button>
                    <x-base.menu.items class="w-80">
                        <form id="filterForm" method="GET" action="{{ route('admin.reports.carrier-documents') }}" class="p-4">
                            <h3 class="font-medium text-base mb-4">Filters</h3>
                            
                            <!-- Search -->
                            <div class="mb-4">
                                <x-base.form-label>Search</x-base.form-label>
                                <x-base.form-input
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Name, DOT, MC, EIN..."
                                />
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="mb-4">
                                <x-base.form-label>Status</x-base.form-label>
                                <x-base.form-select name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ $statusFilter == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ $statusFilter == '2' ? 'selected' : '' }}>Pending</option>
                                    <option value="0" {{ $statusFilter == '0' ? 'selected' : '' }}>Inactive</option>
                                </x-base.form-select>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="mb-4">
                                <x-base.form-label>Date Range</x-base.form-label>
                                <div class="grid grid-cols-2 gap-2">
                                    <x-base.form-input
                                        type="date"
                                        name="date_from"
                                        value="{{ $dateFrom }}"
                                        placeholder="From"
                                    />
                                    <x-base.form-input
                                        type="date"
                                        name="date_to"
                                        value="{{ $dateTo }}"
                                        placeholder="To"
                                    />
                                </div>
                            </div>
                            
                            <!-- Per Page -->
                            <div class="mb-4">
                                <x-base.form-label>Per Page</x-base.form-label>
                                <x-base.form-select name="per_page">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </x-base.form-select>
                            </div>
                            
                            <div class="flex justify-between">
                                <x-base.button type="button" onclick="clearFilters()" variant="outline-secondary">Clear</x-base.button>
                                <x-base.button type="submit" variant="primary">Apply</x-base.button>
                            </div>
                        </form>
                    </x-base.menu.items>
                </x-base.menu>
        </div>
    </div>
</div>

<!-- Enhanced Statistics Cards -->
<div class="mt-3.5 flex flex-col gap-8">
    <!-- Quick Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Carriers</p>
                    <p class="text-2xl font-bold">{{ $totalCarriers }}</p>
                    <p class="text-blue-200 text-xs mt-1">{{ $totalDocuments }} documents</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <x-base.lucide icon="Truck" class="h-6 w-6" />
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Active</p>
                    <p class="text-2xl font-bold">{{ $activeCarriers }}</p>
                    <p class="text-green-200 text-xs mt-1">{{ $totalCarriers > 0 ? round(($activeCarriers / $totalCarriers) * 100, 1) : 0 }}% of total</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <x-base.lucide icon="CheckCircle" class="h-6 w-6" />
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Pending</p>
                    <p class="text-2xl font-bold">{{ $pendingCarriers }}</p>
                    <p class="text-yellow-200 text-xs mt-1">{{ $totalCarriers > 0 ? round(($pendingCarriers / $totalCarriers) * 100, 1) : 0 }}% of total</p>
                </div>
                <div class="bg-yellow-400 bg-opacity-30 rounded-full p-3">
                    <x-base.lucide icon="Clock" class="h-6 w-6" />
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Documents</p>
                    <p class="text-2xl font-bold">{{ $totalDocuments }}</p>
                    <p class="text-purple-200 text-xs mt-1">{{ $approvedDocuments }} approved</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <x-base.lucide icon="FileText" class="h-6 w-6" />
                </div>
            </div>
        </div>
    </div>

<!-- Document Statistics -->
<div class="mt-5 box box--stacked flex flex-col">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Document Status Counts -->
        <div class="col-span-1">
            <div class="box p-5 rounded-[0.6rem]">
                <h3 class="text-lg font-medium mb-3">Document Status</h3>
                <div class="flex flex-col">
                    <div class="flex items-center mb-2">
                        <div class="w-2 h-2 bg-success rounded-full mr-3"></div>
                        <div class="flex-1 text-sm">Approved</div>
                        <div class="text-sm font-medium">{{ $approvedDocuments }}</div>
                    </div>
                    <div class="flex items-center mb-2">
                        <div class="w-2 h-2 bg-warning rounded-full mr-3"></div>
                        <div class="flex-1 text-sm">Pending</div>
                        <div class="text-sm font-medium">{{ $pendingDocuments }}</div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-danger rounded-full mr-3"></div>
                        <div class="flex-1 text-sm">Rejected</div>
                        <div class="text-sm font-medium">{{ $rejectedDocuments }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Document Types -->
        <div class="col-span-1 md:col-span-2">
            <div class="box p-5 rounded-[0.6rem]">
                <h3 class="text-lg font-medium mb-3">Document Types</h3>
                <div class="overflow-auto max-h-[300px] pr-2">
                    @foreach($documentTypes as $docType)
                    <div class="flex items-center mb-3">
                        <div class="flex-1 text-sm">{{ $docType->name }}</div>
                        <div class="text-sm font-medium">{{ $docType->carrier_documents_count }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="mt-5">
    <form method="GET" action="{{ route('admin.reports.carrier-documents') }}" class="w-full">
        <div class="flex flex-col gap-4 xl:flex-row">
            <div class="flex flex-1 items-center">
                <x-base.form-input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search carriers..."
                    class="mr-3 w-full" />
                <x-base.button type="submit" variant="primary">
                    <x-base.lucide class="h-4 w-4" icon="Search" />
                </x-base.button>
            </div>
            @if($search || $statusFilter || $dateFrom || $dateTo)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            @endif
        </div>
    </form>
</div>

<!-- Carriers Table -->
<div class="mt-5 box box--stacked">
    <div class="flex flex-col p-5 sm:flex-row sm:items-center">
        <div>
            <div class="text-base font-medium">Carriers List</div>
            <div class="mt-1 text-slate-500">Total: {{ $carriers->total() }} carriers</div>
        </div>
    </div>
    
    <div>
        @if($carriers->count() > 0)
            <div class="overflow-x-auto">
                <x-base.table striped>
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">DOT</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">MC</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Status</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Documents</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Progress</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Actions</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach($carriers as $carrier)
                            <x-base.table.tr>
                                <x-base.table.td>
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 image-fit">
                                            @if($carrier->getFirstMediaUrl('logo_carrier'))
                                                <img alt="{{ $carrier->name }}" class="rounded-full" src="{{ $carrier->getFirstMediaUrl('logo_carrier') }}" title="{{ $carrier->name }}">
                                            @else
                                                <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center">
                                                    <x-base.lucide class="h-4 w-4 text-slate-600" icon="Truck" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <a href="{{ route('admin.carrier.show', $carrier->slug) }}" class="font-medium whitespace-nowrap hover:text-primary">
                                                {{ $carrier->name }}
                                            </a>
                                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                                EIN: {{ $carrier->ein_number }}
                                            </div>
                                        </div>
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    <span class="text-slate-500">{{ $carrier->dot_number ?: 'N/A' }}</span>
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    <span class="text-slate-500">{{ $carrier->mc_number ?: 'N/A' }}</span>
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    @if($carrier->status == 1)
                                        <div class="flex items-center justify-center text-success">
                                            <x-base.lucide class="h-4 w-4 mr-1" icon="CheckCircle" /> Active
                                        </div>
                                    @elseif($carrier->status == 2)
                                        <div class="flex items-center justify-center text-warning">
                                            <x-base.lucide class="h-4 w-4 mr-1" icon="Clock" /> Pending
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center text-danger">
                                            <x-base.lucide class="h-4 w-4 mr-1" icon="XCircle" /> Inactive
                                        </div>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-lg font-medium">{{ $carrier->documents_count }}</span>
                                        <div class="text-xs text-slate-500 mt-1">
                                            <span class="text-success">{{ $carrier->approved_documents_count }} approved</span> |
                                            <span class="text-warning">{{ $carrier->pending_documents_count }} pending</span>
                                            @if($carrier->rejected_documents_count > 0)
                                                | <span class="text-danger">{{ $carrier->rejected_documents_count }} rejected</span>
                                            @endif
                                        </div>
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    @php
                                        $totalDocumentTypes = $documentTypes->count();
                                        $progress = $totalDocumentTypes > 0 ? ($carrier->approved_documents_count / $totalDocumentTypes) * 100 : 0;
                                    @endphp
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-2 bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 mt-1">{{ number_format($progress, 1) }}%</span>
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="text-center">
                                    <div class="flex justify-center items-center">
                                        <!-- View Documents -->
                                        <a href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}" class="flex items-center mr-3" title="View Documents">
                                            <x-base.lucide class="h-4 w-4 mr-1" icon="Eye" /> View
                                        </a>
                                        
                                        <!-- Download All Documents -->
                                        @if($carrier->documents_count > 0)
                                            <a href="{{ route('admin.reports.download-carrier-documents', $carrier) }}" class="flex items-center text-primary" title="Download All Documents">
                                                <x-base.lucide class="h-4 w-4 mr-1" icon="Download" /> Download
                                            </a>
                                        @else
                                            <span class="flex items-center text-slate-400" title="No documents available">
                                                <x-base.lucide class="h-4 w-4 mr-1" icon="Download" /> Download
                                            </span>
                                        @endif
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
            
            <!-- Pagination -->
            <div class="p-5 border-t border-slate-200/60">
                {{ $carriers->appends(request()->query())->links() }}
            </div>
        @else
            <div class="p-10 text-center">
                <div class="text-slate-500">
                    <x-base.lucide class="h-16 w-16 mx-auto mb-4 text-slate-300" icon="Inbox" />
                    <p class="text-lg font-medium">No carriers found</p>
                    <p class="mt-2">Try adjusting your search criteria or filters.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    function exportToPDF() {
        // Get current filter parameters
        const params = new URLSearchParams(window.location.search);
        const pdfUrl = '{{ route("admin.reports.carrier-documents.pdf") }}?' + params.toString();
        window.open(pdfUrl, '_blank');
    }
    
    function clearFilters() {
        window.location.href = '{{ route("admin.reports.carrier-documents") }}';
    }
    
    // Auto-submit form when filters change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            const selects = filterForm.querySelectorAll('select');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    filterForm.submit();
                });
            });
        }
    });
</script>
@endpush
