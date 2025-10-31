# Carrier Document Management - Implementation Guide

## 1. Current Issues Analysis

### Problems Identified:

1. **Blade Syntax Errors**: `@push('scripts')` usage in components causing "Cannot end a section without first starting one"
2. **Mixed Concerns**: Business logic mixed with presentation in Blade templates
3. **Inline Styles**: Large CSS blocks embedded in Blade files
4. **Custom Modal Code**: Manual modal implementation instead of using x-base.dialog
5. **Component Architecture**: Improper component structure causing parsing errors

### Root Causes:

* Components using `@push` directives (forbidden in Blade components)

* Complex PHP logic in view files instead of controller/service layer

* Inconsistent prop naming between components

* Missing separation of concerns

## 2. Proposed Solution Architecture

### 2.1 Clean Component Structure

```
resources/views/carrier/documents/
├── index.blade.php (main view - clean, minimal logic)
├── components/
│   ├── document-card.blade.php (individual document display)
│   ├── document-filters.blade.php (sidebar filters)
│   ├── document-stats.blade.php (progress overview)
│   └── upload-form.blade.php (upload functionality)
```

### 2.2 Controller Enhancement

Move all business logic to `CarrierDocumentController`:

* Document status calculation

* Progress statistics

* Filter data preparation

* File validation logic

### 2.3 Service Layer Integration

Utilize existing `CarrierDocumentService` for:

* Document upload processing

* Status management

* File operations

* Progress calculations

## 3. Implementation Steps

### Step 1: Clean the Main View (index.blade.php)

```blade
@extends('../themes/' . $activeTheme)
@section('title', 'Documents Overview')

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <!-- Header -->
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Document Center
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="mt-3.5 grid grid-cols-12 gap-x-6 gap-y-10">
            <!-- Sidebar Filters -->
            <div class="relative col-span-12 xl:col-span-3">
                @include('carrier.documents.components.document-filters', [
                    'documentStats' => $documentStats
                ])
            </div>
            
            <!-- Main Content -->
            <div class="col-span-12 flex flex-col gap-y-7 xl:col-span-9">
                <!-- Progress Overview -->
                @include('carrier.documents.components.document-stats', [
                    'progress' => $progress,
                    'carrier' => $carrier
                ])
                
                <!-- Document Grid -->
                <div class="box box--stacked flex flex-col p-5">
                    <div class="mb-6 border-b border-dashed border-slate-300/70 pb-5 text-[0.94rem] font-medium">
                        Document Management
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="documents-grid">
                        @foreach($mappedDocuments as $document)
                            @include('carrier.documents.components.document-card', [
                                'document' => $document,
                                'carrier' => $carrier
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal using x-base.dialog -->
<x-base.dialog id="upload-modal" size="lg">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="mr-auto text-base font-medium">Upload Document</h2>
        </x-base.dialog.title>
        <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
            @include('carrier.documents.components.upload-form')
        </x-base.dialog.description>
        <x-base.dialog.footer>
            <x-base.button type="button" variant="outline-secondary" onclick="closeUploadModal()" class="w-20 mr-1">
                Cancel
            </x-base.button>
            <x-base.button type="button" variant="primary" onclick="submitUpload()" class="w-20">
                Upload
            </x-base.button>
        </x-base.dialog.footer>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@push('scripts')
<script>
// Global variables from controller
window.carrierSlug = '{{ $carrier->slug }}';
window.csrfToken = '{{ csrf_token() }}';
window.uploadRoute = '{{ route("carrier.documents.upload", $carrier->slug) }}';

// Document management functions
function openUploadModal(documentTypeId, documentTypeName) {
    document.getElementById('document_type_id').value = documentTypeId;
    document.getElementById('document-type-name').textContent = documentTypeName;
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#upload-modal"));
    modal.show();
}

function closeUploadModal() {
    const modal = tailwind.Modal.getInstance(document.querySelector("#upload-modal"));
    modal.hide();
}

// Additional JavaScript functions...
</script>
@endpush
```

### Step 2: Create Clean Components

#### Document Card Component (document-card.blade.php)

```blade
@php
    $documentType = $document['type'];
    $carrierDocument = $document['document'];
    $status = $document['status'];
    $hasFile = $document['has_file'];
    $hasDefault = $document['has_default'];
    
    $statusConfig = [
        'uploaded' => ['class' => 'uploaded', 'badge' => 'status-uploaded', 'icon' => 'CheckCircle', 'text' => 'Uploaded'],
        'pending' => ['class' => 'pending', 'badge' => 'status-pending', 'icon' => 'Clock', 'text' => 'Pending'],
        'missing' => ['class' => 'missing', 'badge' => 'status-missing', 'icon' => 'AlertCircle', 'text' => 'Missing'],
        'default-available' => ['class' => 'default-available', 'badge' => 'status-default-available', 'icon' => 'Download', 'text' => 'Default Available']
    ];
    
    $config = $statusConfig[$status] ?? $statusConfig['missing'];
@endphp

<div class="document-card {{ $config['class'] }} rounded-[0.6rem] border border-slate-200/80 p-5 document-item" 
     data-status="{{ $status }}" 
     data-requirement="{{ $documentType->requirement ? 'mandatory' : 'optional' }}">
    
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
            <h3 class="font-medium text-slate-700 mb-2">
                {{ $documentType->name }}
                @if($documentType->requirement)
                    <span class="ml-1 text-danger font-bold">*</span>
                @endif
            </h3>
            <span class="status-badge {{ $config['badge'] }}">{{ $config['text'] }}</span>
        </div>
        <div class="ml-4">
            <x-base.lucide class="w-6 h-6" icon="{{ $config['icon'] }}" />
        </div>
    </div>

    <div class="mb-4">
        <p class="text-xs text-slate-500 mb-2">
            {{ $documentType->description ?? 'Please upload this document to complete your registration.' }}
        </p>
        <span class="requirement-badge {{ $documentType->requirement ? 'mandatory' : 'optional' }}">
            {{ $documentType->requirement ? 'OBLIGATORY' : 'OPTIONAL' }}
        </span>
    </div>

    <div class="flex gap-2 mt-4">
        @if($hasFile)
            <x-base.button variant="outline-primary" size="sm" onclick="viewDocument({{ $carrierDocument->id }})">
                <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" />
                View
            </x-base.button>
            <x-base.button variant="outline-secondary" size="sm" onclick="replaceDocument({{ $documentType->id }}, '{{ $documentType->name }}')">
                <x-base.lucide class="w-4 h-4 mr-1" icon="Upload" />
                Replace
            </x-base.button>
        @elseif($hasDefault)
            <x-base.button variant="primary" size="sm" onclick="acceptDefaultDocument({{ $documentType->id }})">
                <x-base.lucide class="w-4 h-4 mr-1" icon="Download" />
                Use Default
            </x-base.button>
            <x-base.button variant="outline-primary" size="sm" onclick="openUploadModal({{ $documentType->id }}, '{{ $documentType->name }}')">
                <x-base.lucide class="w-4 h-4 mr-1" icon="Upload" />
                Upload Own
            </x-base.button>
        @else
            <x-base.button variant="primary" size="sm" onclick="openUploadModal({{ $documentType->id }}, '{{ $documentType->name }}')">
                <x-base.lucide class="w-4 h-4 mr-1" icon="Upload" />
                Upload
            </x-base.button>
        @endif
    </div>
</div>
```

#### Upload Form Component (upload-form.blade.php)

```blade
<div class="col-span-12">
    <form id="upload-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="document_type_id" name="document_type_id">
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Document Type: <span id="document-type-name" class="font-semibold"></span>
            </label>
        </div>
        
        <div class="mb-4">
            <div class="upload-area" id="upload-area">
                <div class="text-center">
                    <x-base.lucide class="mx-auto h-12 w-12 text-slate-400 mb-4" icon="Upload" />
                    <div class="text-sm text-slate-600">
                        <label for="file-upload" class="cursor-pointer font-medium text-primary hover:text-primary/80">
                            Click to upload
                        </label>
                        or drag and drop
                    </div>
                    <p class="text-xs text-slate-500 mt-1">PDF, PNG, JPG up to 10MB</p>
                </div>
                <input id="file-upload" name="file" type="file" class="sr-only" accept=".pdf,.png,.jpg,.jpeg">
            </div>
        </div>
        
        <div id="file-preview" class="hidden mb-4">
            <div class="flex items-center p-3 bg-slate-50 rounded-lg">
                <x-base.lucide class="h-8 w-8 text-slate-400 mr-3" icon="FileText" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-900" id="file-name"></p>
                    <p class="text-xs text-slate-500" id="file-size"></p>
                </div>
                <button type="button" onclick="removeFile()" class="text-slate-400 hover:text-slate-600">
                    <x-base.lucide class="h-5 w-5" icon="X" />
                </button>
            </div>
        </div>
        
        <div id="upload-progress" class="hidden mb-4">
            <div class="bg-slate-200 rounded-full h-2">
                <div id="progress-bar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-1" id="progress-text">Uploading...</p>
        </div>
    </form>
</div>
```

### Step 3: Enhanced Controller Logic

```php
// CarrierDocumentController@index method enhancement
public function index($carrierSlug)
{
    $carrier = $this->findCarrierBySlug($carrierSlug);
    
    if (!$this->canAccessCarrier($carrier)) {
        return redirect()->route('login')
            ->withErrors(['access' => 'You do not have permission to access this carrier.']);
    }

    // Get mapped documents with enhanced status information
    $mappedDocuments = $this->carrierDocumentService->getMappedDocuments($carrier);
    
    // Calculate progress and statistics
    $progress = $this->carrierDocumentService->getDocumentProgress($carrier);
    
    // Prepare document statistics for filters
    $documentStats = $this->calculateDocumentStats($mappedDocuments);
    
    return view('carrier.documents.index', compact(
        'carrier', 
        'mappedDocuments', 
        'progress', 
        'documentStats'
    ));
}

private function calculateDocumentStats($mappedDocuments)
{
    $stats = [
        'all' => count($mappedDocuments),
        'uploaded' => 0,
        'pending' => 0,
        'missing' => 0,
        'default-available' => 0
    ];
    
    foreach ($mappedDocuments as $document) {
        $stats[$document['status']]++;
    }
    
    return $stats;
}
```

## 4. Key Implementation Rules

### 4.1 Component Guidelines

1. **No @push in components**: Use regular `<script>` tags or move to main view
2. **Props consistency**: Use camelCase for component props
3. **Single responsibility**: Each component handles one specific UI concern
4. **Data flow**: Pass data down, emit events up

### 4.2 JavaScript Organization

1. **Global variables**: Define in main view @push('scripts')
2. **Event handlers**: Attach to window object for component access
3. **AJAX calls**: Centralized error handling and loading states
4. **Modal management**: Use x-base.dialog API consistently

### 4.3 Styling Strategy

1. **Tailwind classes**: Primary styling method
2. **Component-specific styles**: In separate CSS files if needed
3. **No inline styles**: Move to external stylesheets
4. **Consistent spacing**: Use Tailwind spacing scale

## 5. Testing Checklist

* [ ] Document upload functionality works

* [ ] Modal opens and closes properly using x-base.dialog

* [ ] Filtering updates document display correctly

* [ ] Progress statistics calculate accurately

* [ ] File validation prevents invalid uploads

* [ ] Error messages display appropriately

* [ ] Responsive design works on mobile devices

* [ ] No Blade syntax errors in browser console

* [ ] All AJAX endpoints return proper responses

* [ ] Document status updates reflect immediately

## 6. Migration Strategy

1. **Backup current files**: Create copies of existing components
2. **Implement incrementally**: Start with main view, then components
3. **Test each component**: Verify functionality before moving to next
4. **Update controller**: Enhance data preparation logic
5. **Remove old components**: Clean up unused component files
6. **Performance testing**: Ensure no regression in load times

