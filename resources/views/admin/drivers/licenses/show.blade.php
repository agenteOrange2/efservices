@extends('../themes/' . $activeTheme)
@section('title', 'License Details')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Licenses', 'url' => route('admin.licenses.index')],
['label' => 'License Details', 'active' => true],
];
@endphp

@section('subcontent')
<div>
    <!-- Flash Messages -->
    @if(session('success'))
        <x-base.alert variant="success" dismissible>
            {{ session('success') }}
        </x-base.alert>
    @endif

    @if(session('error'))
        <x-base.alert variant="danger" dismissible>
            {{ session('error') }}
        </x-base.alert>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
        <div class="text-base font-medium group-[.mode--light]:text-white">
            License Details
        </div>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
            <x-base.button as="a" href="{{ route('admin.licenses.index') }}" variant="outline-secondary">
                <x-base.lucide class="mr-2 h-4 w-4" icon="arrow-left" />
                Back
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.licenses.edit', $license->id) }}" variant="primary">
                <x-base.lucide class="mr-2 h-4 w-4" icon="edit" />
                Edit
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.licenses.docs.show', $license->id) }}" variant="outline-primary">
                <x-base.lucide class="mr-2 h-4 w-4" icon="file-text" />
                Documents ({{ $license->getMedia('license_documents')->count() }})
            </x-base.button>
        </div>
    </div>

    <div class="mt-3.5 grid grid-cols-12 gap-y-10 gap-x-6">
        <!-- Información Básica -->
        <div class="col-span-12 2xl:col-span-8">
            <div class="box box--stacked p-5">
                <div class="flex items-center border-b border-dashed border-slate-300/70 pb-5 mb-5">
                    <x-base.lucide class="mr-2 h-6 w-6 text-primary" icon="id-card" />
                    <h3 class="text-base font-medium">License Information</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">Driver</div>
                        <div class="mt-1 text-base font-medium">
                            {{ $license->driverDetail->user->name ?? 'N/A' }} {{ $license->driverDetail->user->last_name ?? '' }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">Carrier</div>
                        <div class="mt-1 text-base font-medium">
                            {{ $license->driverDetail->carrier->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">License Number</div>
                        <div class="mt-1 text-base font-medium">
                            {{ $license->current_license_number ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">License Class</div>
                        <div class="mt-1">
                            @if($license->license_class)
                                <div class="inline-flex items-center rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary">
                                    {{ $license->license_class }}
                                </div>
                            @else
                                <span class="text-slate-500">N/A</span>
                            @endif
                        </div>
                    </div>                    
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">Expiration Date</div>
                        <div class="mt-1 flex items-center gap-2">
                            @if($license->expiration_date)
                                <span class="text-base font-medium">
                                    {{ \Carbon\Carbon::parse($license->expiration_date)->format('M d, Y') }}
                                </span>
                                @php
                                    $expirationDate = \Carbon\Carbon::parse($license->expiration_date);
                                    $now = \Carbon\Carbon::now();
                                    $daysUntilExpiration = $now->diffInDays($expirationDate, false);
                                @endphp
                                @if($daysUntilExpiration < 0)
                                    <div class="inline-flex items-center rounded-full bg-danger/10 px-2 py-1 text-xs font-medium text-danger">
                                        Expired
                                    </div>
                                @elseif($daysUntilExpiration <= 30)
                                    <div class="inline-flex items-center rounded-full bg-warning/10 px-2 py-1 text-xs font-medium text-warning">
                                        Expires Soon
                                    </div>
                                @else
                                    <div class="inline-flex items-center rounded-full bg-success/10 px-2 py-1 text-xs font-medium text-success">
                                        Valid
                                    </div>
                                @endif
                            @else
                                <span class="text-slate-500">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs uppercase tracking-widest text-slate-500">Restrictions</div>
                        <div class="mt-1 text-base font-medium">
                            {{ $license->restrictions ?? 'None' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endorsements -->
        <div class="col-span-12 2xl:col-span-4">
            <div class="box box--stacked p-5">
                <div class="flex items-center border-b border-dashed border-slate-300/70 pb-5 mb-5">
                    <x-base.lucide class="mr-2 h-6 w-6 text-primary" icon="award" />
                    <h3 class="text-base font-medium">Endorsements</h3>
                </div>
                @if($license->is_cdl)
                    <div class="mb-4">
                        <div class="inline-flex items-center rounded-full bg-info/10 px-3 py-1 text-sm font-medium text-info">
                            <x-base.lucide class="mr-1 h-4 w-4" icon="truck" />
                            CDL License
                        </div>
                    </div>
                    <div class="space-y-3">
                        @php
                            $endorsementLabels = [
                                'N' => 'Tank Vehicles',
                                'H' => 'Hazmat',
                                'X' => 'Hazmat & Tank',
                                'T' => 'Double/Triple',
                                'P' => 'Passenger',
                                'S' => 'School Bus'
                            ];
                            $currentEndorsements = $license->endorsements->pluck('code')->toArray();
                        @endphp
                        @foreach($endorsementLabels as $code => $label)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    @if(in_array($code, $currentEndorsements))
                                        <x-base.lucide class="mr-2 h-4 w-4 text-success" icon="check-circle" />
                                        <span class="text-sm font-medium">{{ $code }} - {{ $label }}</span>
                                    @else
                                        <x-base.lucide class="mr-2 h-4 w-4 text-slate-400" icon="circle" />
                                        <span class="text-sm text-slate-500">{{ $code }} - {{ $label }}</span>
                                    @endif
                                </div>
                                @if(in_array($code, $currentEndorsements))
                                    <div class="inline-flex items-center rounded-full bg-success/10 px-2 py-1 text-xs font-medium text-success">
                                        Active
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-center py-8">
                        <div class="text-center">
                            <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="info" />
                            <p class="mt-2 text-sm text-slate-500">No CDL endorsements available for this license type.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- License Images -->
    <div class="col-span-12 mt-5">
        <div class="box box--stacked p-5">
            <div class="flex items-center justify-between border-b border-dashed border-slate-300/70 pb-5 mb-5">
                <div class="flex items-center">
                    <x-base.lucide class="mr-2 h-6 w-6 text-primary" icon="image" />
                    <h3 class="text-base font-medium">License Images</h3>
                </div>
                <x-base.button as="a" href="{{ route('admin.licenses.edit', $license->id) }}" variant="outline-primary" size="sm">
                    <x-base.lucide class="mr-1 h-4 w-4" icon="edit" />
                    Edit Images
                </x-base.button>
            </div>
            
            @php
                $frontImage = $license->getFirstMediaUrl('license_front');
                $backImage = $license->getFirstMediaUrl('license_back');
            @endphp
            
            @if($frontImage || $backImage)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Front Image -->
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <x-base.lucide class="mr-2 h-5 w-5 text-slate-600" icon="credit-card" />
                            <h4 class="font-medium text-slate-700">License Front</h4>
                        </div>
                        @if($frontImage)
                            <div class="relative group">
                                <div class="aspect-[3/2] overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                    <img 
                                        src="{{ $frontImage }}" 
                                        alt="License Front" 
                                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    >
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg">
                                    <x-base.button
                                        as="a"
                                        href="{{ $frontImage }}"
                                        target="_blank"
                                        variant="primary"
                                        size="sm"
                                    >
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="eye" />
                                        View Full Size
                                    </x-base.button>
                                </div>
                            </div>
                        @else
                            <div class="aspect-[3/2] flex items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                                <div class="text-center">
                                    <x-base.lucide class="mx-auto h-8 w-8 text-slate-400" icon="image-off" />
                                    <p class="mt-2 text-sm text-slate-500">No front image uploaded</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Back Image -->
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <x-base.lucide class="mr-2 h-5 w-5 text-slate-600" icon="credit-card" />
                            <h4 class="font-medium text-slate-700">License Back</h4>
                        </div>
                        @if($backImage)
                            <div class="relative group">
                                <div class="aspect-[3/2] overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                    <img 
                                        src="{{ $backImage }}" 
                                        alt="License Back" 
                                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                                    >
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg">
                                    <x-base.button
                                        as="a"
                                        href="{{ $backImage }}"
                                        target="_blank"
                                        variant="primary"
                                        size="sm"
                                    >
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="eye" />
                                        View Full Size
                                    </x-base.button>
                                </div>
                            </div>
                        @else
                            <div class="aspect-[3/2] flex items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                                <div class="text-center">
                                    <x-base.lucide class="mx-auto h-8 w-8 text-slate-400" icon="image-off" />
                                    <p class="mt-2 text-sm text-slate-500">No back image uploaded</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <x-base.lucide class="mx-auto h-16 w-16 text-slate-400" icon="image-off" />
                        <h3 class="mt-4 text-lg font-medium text-slate-900">No license images uploaded</h3>
                        <p class="mt-2 text-sm text-slate-500 mb-4">Upload front and back images of the license for verification.</p>
                        <x-base.button as="a" href="{{ route('admin.licenses.edit', $license->id) }}" variant="primary">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="upload" />
                            Upload Images
                        </x-base.button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Additional Documents -->
    <div class="col-span-12 mt-5">
        <div class="box box--stacked p-5">
            <div class="flex items-center justify-between border-b border-dashed border-slate-300/70 pb-5 mb-5">
                <div class="flex items-center">
                    <x-base.lucide class="mr-2 h-6 w-6 text-primary" icon="paperclip" />
                    <h3 class="text-base font-medium">Additional Documents</h3>
                </div>
                <div class="flex items-center gap-2">
                    <x-base.button type="button" onclick="document.getElementById('uploadForm').style.display = document.getElementById('uploadForm').style.display === 'none' ? 'block' : 'none'" variant="primary" size="sm">
                        <x-base.lucide class="mr-1 h-4 w-4" icon="upload" />
                        Upload Documents
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.licenses.docs.show', $license->id) }}" variant="outline-primary" size="sm">
                        View All Documents
                    </x-base.button>
                </div>
            </div>
            
            <!-- Upload Form -->
            <div id="uploadForm" style="display: none;" class="mb-5 p-4 bg-slate-50 rounded-lg border border-slate-200">
                <form action="{{ route('admin.licenses.upload.documents', $license->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Documents</label>
                        <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-primary/90">
                        <p class="mt-1 text-xs text-slate-500">Supported formats: PDF, JPG, PNG, DOC, DOCX. Max size: 10MB per file.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-base.button type="submit" variant="primary" size="sm">
                            <x-base.lucide class="mr-1 h-4 w-4" icon="upload" />
                            Upload
                        </x-base.button>
                        <x-base.button type="button" onclick="document.getElementById('uploadForm').style.display = 'none'" variant="outline-secondary" size="sm">
                            Cancel
                        </x-base.button>
                    </div>
                </form>
            </div>
            @if($license->getMedia('license_documents')->count() > 0)
                <div class="overflow-auto xl:overflow-visible">
                    <x-base.table class="border-spacing-y-[10px] border-separate -mt-2">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="border-b-0 whitespace-nowrap">
                                    Document Name
                                </x-base.table.th>
                                <x-base.table.th class="border-b-0 whitespace-nowrap">
                                    Type
                                </x-base.table.th>
                                <x-base.table.th class="border-b-0 whitespace-nowrap">
                                    Size
                                </x-base.table.th>
                                <x-base.table.th class="border-b-0 whitespace-nowrap">
                                    Upload Date
                                </x-base.table.th>
                                <x-base.table.th class="border-b-0 whitespace-nowrap">
                                    Actions
                                </x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach($license->getMedia('license_documents')->take(5) as $document)
                                <x-base.table.tr class="intro-x">
                                    <x-base.table.td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                        <div class="flex items-center">
                                            <x-base.lucide class="mr-2 h-4 w-4 text-slate-500" icon="file-text" />
                                            <span class="font-medium">{{ $document->name }}</span>
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                        <div class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                            {{ strtoupper($document->mime_type) }}
                                        </div>
                                    </x-base.table.td>
                                    <x-base.table.td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                        <span class="text-slate-500">{{ $document->human_readable_size }}</span>
                                    </x-base.table.td>
                                    <x-base.table.td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                        <span class="text-slate-500">{{ $document->created_at->format('M d, Y H:i') }}</span>
                                    </x-base.table.td>
                                    <x-base.table.td class="first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                        <div class="flex items-center gap-2">
                                            <x-base.button
                                                as="a"
                                                href="{{ route('admin.licenses.doc.preview', $document->id) }}"
                                                target="_blank"
                                                variant="outline-primary"
                                                size="sm"
                                                title="View document"
                                            >
                                                <x-base.lucide class="h-4 w-4" icon="eye" />
                                            </x-base.button>
                                            <x-base.button
                                                type="button"
                                                onclick="confirmDelete({{ $document->id }})"
                                                variant="outline-danger"
                                                size="sm"
                                                title="Delete document"
                                            >
                                                <x-base.lucide class="h-4 w-4" icon="trash-2" />
                                            </x-base.button>
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                @if($license->getMedia('license_documents')->count() > 5)
                    <div class="text-center mt-5">
                        <p class="text-slate-500 mb-3">Showing 5 of {{ $license->getMedia('license_documents')->count() }} documents</p>
                        <x-base.button as="a" href="{{ route('admin.licenses.docs.show', $license->id) }}" variant="outline-primary">
                            View All Documents
                        </x-base.button>
                    </div>
                @endif
            @else
                <div class="flex items-center justify-center py-16">
                    <div class="text-center">
                        <x-base.lucide class="mx-auto h-16 w-16 text-slate-400" icon="folder-open" />
                        <h3 class="mt-4 text-lg font-medium text-slate-900">No additional documents</h3>
                        <p class="mt-2 text-sm text-slate-500 mb-4">No additional documents have been uploaded for this license.</p>
                        <x-base.button as="a" onclick="document.getElementById('uploadForm').style.display = document.getElementById('uploadForm').style.display === 'none' ? 'block' : 'none'" variant="primary">
                            <x-base.lucide class="mr-1 h-4 w-4" icon="upload" />
                            Upload Documents
                        </x-base.button>
                        
                    
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<x-base.dialog id="deleteModal">
    <x-base.dialog.panel>
        <div class="p-5 text-center">
            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
            <div class="mt-5 text-3xl">Are you sure?</div>
            <div class="mt-2 text-slate-500">
                Do you really want to delete this document? <br>
                This process cannot be undone.
            </div>
        </div>
        <div class="px-5 pb-8 text-center">
            <x-base.button
                class="mr-1 w-24"
                data-tw-dismiss="modal"
                type="button"
                variant="outline-secondary"
            >
                Cancel
            </x-base.button>
            <form id="deleteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <x-base.button class="w-24" type="submit" variant="danger">
                    Delete
                </x-base.button>
            </form>
        </div>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@section('script')
<script>
function confirmDelete(documentId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/licenses/document') }}/${documentId}`;
    
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#deleteModal'));
    modal.show();
}
</script>
@endsection