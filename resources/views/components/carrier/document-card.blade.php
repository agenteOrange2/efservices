@php
    $documentType = $document['type'];
    $carrierDocument = $document['document'];
    $status = $document['status'];
    $hasFile = $document['has_file'];
    $hasDefault = $document['has_default'];
    
    $statusConfig = [
        'uploaded' => ['class' => 'uploaded text-green-50', 'badge' => 'status-uploaded', 'icon' => 'CheckCircle', 'text' => 'Uploaded'],
        'pending' => ['class' => 'pending', 'badge' => 'status-pending', 'icon' => 'Clock', 'text' => 'Pending'],
        'missing' => ['class' => 'missing', 'badge' => 'status-missing', 'icon' => 'AlertCircle', 'text' => 'Missing'],
        'rejected' => ['class' => 'rejected', 'badge' => 'status-rejected', 'icon' => 'XCircle', 'text' => 'Rejected'],
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