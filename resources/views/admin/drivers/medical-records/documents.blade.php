@extends('../themes/' . $activeTheme)
@section('title', 'Medical Record Documents')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Medical Records', 'url' => route('admin.medical-records.index')],
['label' => 'Medical Record Documents', 'active' => true],
];
@endphp

@section('subcontent')
    <!-- Flash Messages -->
    @if(session('success'))
        <x-base.alert class="mb-6" variant="success" dismissible>
            {{ session('success') }}
        </x-base.alert>
    @endif

    @if(session('error'))
        <x-base.alert class="mb-6" variant="danger" dismissible>
            {{ session('error') }}
        </x-base.alert>
    @endif

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-800">Medical Record Documents</h2>
                <p class="text-slate-600 mt-1">Manage documents for {{ $medicalRecord->driver->first_name }} {{ $medicalRecord->driver->last_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.medical-records.show', $medicalRecord->id) }}" 
                   class="flex items-center px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="ArrowLeft" />
                    Back to Medical Record
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-3 gap-5 mb-6">
        <!-- Total Documents -->
        <a href="{{ route('admin.medical-records.docs.show', ['medicalRecord' => $medicalRecord->id, 'collection' => 'all'] + request()->except('collection', 'page')) }}"
           class="box col-span-3 rounded-[0.6rem] border border-dashed {{ $currentCollection == 'all' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
            <div class="text-base {{ $currentCollection == 'all' ? 'text-primary' : 'text-slate-500' }}">Total Documents</div>
            <div class="mt-1.5 text-2xl font-medium">{{ $totalDocuments }}</div>
            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                <div class="flex items-center rounded-full border border-success/10 bg-success/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-success">
                    <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="Files" />
                    All
                </div>
            </div>
        </a>            
        
        <!-- Medical Card -->
        <a href="{{ route('admin.medical-records.docs.show', ['medicalRecord' => $medicalRecord->id, 'collection' => 'medical_card'] + request()->except('collection', 'page')) }}"
           class="box col-span-3 rounded-[0.6rem] border border-dashed {{ $currentCollection == 'medical_card' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
            <div class="text-base {{ $currentCollection == 'medical_card' ? 'text-primary' : 'text-slate-500' }}">Medical Card</div>
            <div class="mt-1.5 text-2xl font-medium">{{ $medicalCardDocuments }}</div>
            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                <div class="flex items-center rounded-full border border-warning/10 bg-warning/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-warning">
                    <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="CreditCard" />
                    Card
                </div>
            </div>
        </a>
        
        <!-- Medical Documents -->
        <a href="{{ route('admin.medical-records.docs.show', ['medicalRecord' => $medicalRecord->id, 'collection' => 'medical_documents'] + request()->except('collection', 'page')) }}"
           class="box col-span-3 rounded-[0.6rem] border border-dashed {{ $currentCollection == 'medical_documents' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
            <div class="text-base {{ $currentCollection == 'medical_documents' ? 'text-primary' : 'text-slate-500' }}">Medical Documents</div>
            <div class="mt-1.5 text-2xl font-medium">{{ $additionalDocuments }}</div>
            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                <div class="flex items-center rounded-full border border-secondary/10 bg-secondary/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-secondary">
                    <x-base.lucide class="ml-px h-4 w-4 stroke-[1.5] mr-1" icon="FileText" />
                    Docs
                </div>
            </div>
        </a>
    </div>

    <!-- Simplified Filters -->
    <div class="box p-5 mb-6">
        <form method="GET" action="{{ route('admin.medical-records.docs.show', $medicalRecord->id) }}" class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <!-- Preserve current tab -->
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            
            <div class="flex flex-col gap-4 sm:flex-row lg:flex-row">
                <!-- Date Range Filter -->
                <div class="relative">
                    <x-base.form-label for="date-range-filter" class="text-sm font-medium text-slate-700">
                        <x-base.lucide class="w-4 h-4 mr-1 inline" icon="Calendar" />
                        Date Range
                    </x-base.form-label>
                    <x-base.litepicker
                        class="w-full sm:w-64"
                        id="date-range-filter"
                        name="date_range"
                        value="{{ request('date_range') }}"
                        data-single-mode="false"
                        placeholder="Select date range"
                    />
                </div>

                <!-- Document Type Filter -->
                <div class="relative">
                    <div class="flex items-center gap-3">
                    <x-base.form-label for="document-type-filter" class="text-sm font-medium text-slate-700 m-0">
                        <x-base.lucide class="w-4 h-4 mr-1 inline" icon="FileType" />
                        Document Type
                    </x-base.form-label>
                    <x-base.tom-select
                        class="w-full sm:w-48"
                        id="document-type-filter"
                        name="document_type"
                        data-placeholder="All Types"
                    >
                        <option value="">All Types</option>                        
                        <option value="medical_card" {{ request('document_type') == 'medical_card' ? 'selected' : '' }}>Medical Card</option>
                        <option value="medical_documents" {{ request('document_type') == 'medical_documents' ? 'selected' : '' }}>Medical Documents</option>
                    </x-base.tom-select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 lg:ml-auto">
                <x-base.button
                    class="flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors"
                    type="submit"
                >
                    <x-base.lucide class="w-4 h-4 mr-2" icon="Search" />
                    Apply Filters
                </x-base.button>
                <x-base.button
                    class="flex items-center px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
                    variant="outline-secondary"
                    type="button"
                    onclick="window.location.href='{{ route('admin.medical-records.docs.show', $medicalRecord->id) }}'"
                >
                    <x-base.lucide class="w-4 h-4 mr-2" icon="RotateCcw" />
                    Clear
                </x-base.button>
            </div>
        </form>
    </div>

    <!-- Documents Table -->
    <div class="box">
        <div class="overflow-auto">
            <x-base.table class="border-b border-dashed border-slate-200/80">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500">
                            Document
                        </x-base.table.td>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500">
                            Collection
                        </x-base.table.td>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500">
                            Type
                        </x-base.table.td>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500">
                            Size
                        </x-base.table.td>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500">
                            Upload Date
                        </x-base.table.td>
                        <x-base.table.td class="border-b-0 whitespace-nowrap bg-slate-50 py-4 font-medium text-slate-500 text-center">
                            Actions
                        </x-base.table.td>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    @forelse($documents as $document)
                        <x-base.table.tr class="hover:bg-slate-50/50">
                            <x-base.table.td class="border-dashed py-4">
                                <div class="flex items-center">
                                    <div class="image-fit zoom-in h-10 w-10 overflow-hidden rounded-lg border border-slate-200/70">
                                        @if(in_array(strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <img alt="Document" class="tooltip cursor-pointer rounded-lg" src="{{ route('admin.medical-records.doc.preview', $document->id) }}" title="{{ $document->file_name }}">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-slate-100">
                                                <x-base.lucide class="h-5 w-5 text-slate-500" icon="FileText" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-slate-800">{{ Str::limit($document->file_name, 30) }}</div>
                                        <div class="mt-0.5 text-xs text-slate-500">{{ $document->mime_type }}</div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $document->collection_name)) }}
                                </span>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                @php
                                    $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                    $isPdf = $extension === 'pdf';
                                @endphp
                                
                                @if($isImage)
                                    <span class="inline-flex items-center rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-medium text-success">
                                        <x-base.lucide class="mr-1 h-3 w-3" icon="Image" />
                                        Image
                                    </span>
                                @elseif($isPdf)
                                    <span class="inline-flex items-center rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-medium text-danger">
                                        <x-base.lucide class="mr-1 h-3 w-3" icon="FileText" />
                                        PDF
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-warning/10 px-2.5 py-0.5 text-xs font-medium text-warning">
                                        <x-base.lucide class="mr-1 h-3 w-3" icon="File" />
                                        Document
                                    </span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="text-slate-600">
                                    {{ $document->human_readable_size }}
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="text-slate-600">
                                    {{ $document->created_at->format('M d, Y') }}
                                    <div class="text-xs text-slate-400">{{ $document->created_at->format('h:i A') }}</div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.medical-records.doc.preview', $document->id) }}" target="_blank" 
                                       class="flex items-center justify-center w-8 h-8 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" 
                                       title="View Document">
                                        <x-base.lucide class="h-4 w-4" icon="Eye" />
                                    </a>
                                    <a href="{{ route('admin.medical-records.doc.preview', $document->id) }}" download 
                                       class="flex items-center justify-center w-8 h-8 text-slate-500 hover:text-success hover:bg-success/10 rounded-lg transition-colors" 
                                       title="Download Document">
                                        <x-base.lucide class="h-4 w-4" icon="Download" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.medical-records.doc.delete', $document->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="flex items-center justify-center w-8 h-8 text-slate-500 hover:text-danger hover:bg-danger/10 rounded-lg transition-colors" 
                                                title="Delete Document">
                                            <x-base.lucide class="h-4 w-4" icon="Trash2" />
                                        </button>
                                    </form>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @empty
                        <x-base.table.tr>
                            <x-base.table.td colspan="6" class="border-dashed py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <x-base.lucide class="h-16 w-16 text-slate-300 mb-4" icon="FileX" />
                                    <h3 class="text-lg font-medium text-slate-500 mb-2">No Documents Found</h3>
                                    <p class="text-slate-400">There are no documents matching your current filters.</p>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforelse
                </x-base.table.tbody>
            </x-base.table>
        </div>
        
        <!-- Document Count -->
        @if($documents->count() > 0)
            <div class="py-5 border-t border-dashed border-slate-300/70 text-center">
                <p class="text-sm text-slate-500">Showing {{ $documents->count() }} document(s)</p>
            </div>
        @endif
    </div>
    
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tom-select for selectors
                if (document.querySelector('#document-type-filter')) {
                    new TomSelect('#document-type-filter');
                }
            });
        </script>
    @endpush

@endsection