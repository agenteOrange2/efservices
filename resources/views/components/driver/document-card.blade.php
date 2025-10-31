@props(['document'])

@php
    $fileExtension = pathinfo($document->file_name ?? '', PATHINFO_EXTENSION);
    $fileSize = $document->size ? number_format($document->size / 1024, 1) . ' KB' : 'Unknown size';
    
    $iconMap = [
        'pdf' => 'file-text',
        'doc' => 'file-text',
        'docx' => 'file-text',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'xls' => 'file-spreadsheet',
        'xlsx' => 'file-spreadsheet',
        'zip' => 'file-archive',
        'rar' => 'file-archive',
    ];
    
    $icon = $iconMap[strtolower($fileExtension)] ?? 'file';
    
    $colorMap = [
        'pdf' => 'text-red-600 bg-red-100',
        'doc' => 'text-blue-600 bg-blue-100',
        'docx' => 'text-blue-600 bg-blue-100',
        'jpg' => 'text-green-600 bg-green-100',
        'jpeg' => 'text-green-600 bg-green-100',
        'png' => 'text-green-600 bg-green-100',
        'gif' => 'text-green-600 bg-green-100',
        'xls' => 'text-emerald-600 bg-emerald-100',
        'xlsx' => 'text-emerald-600 bg-emerald-100',
        'zip' => 'text-purple-600 bg-purple-100',
        'rar' => 'text-purple-600 bg-purple-100',
    ];
    
    $colors = $colorMap[strtolower($fileExtension)] ?? 'text-gray-600 bg-gray-100';
@endphp

<div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
    <div class="p-4">
        <!-- File Icon and Type -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-lg {{ $colors }} flex items-center justify-center">
                    <x-base.lucide icon="{{ $icon }}" class="w-5 h-5" />
                </div>
            </div>
            <span class="text-xs font-medium text-gray-500 uppercase">{{ strtoupper($fileExtension) }}</span>
        </div>

        <!-- File Name -->
        <h4 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2" title="{{ $document->name ?? $document->file_name }}">
            {{ $document->name ?? $document->file_name ?? 'Untitled Document' }}
        </h4>

        <!-- File Details -->
        <div class="space-y-1 mb-4">
            <div class="flex items-center text-xs text-gray-500">
                <x-base.lucide icon="calendar" class="w-3 h-3 mr-1" />
                <span>{{ $document->created_at ? $document->created_at->format('M d, Y') : 'Unknown date' }}</span>
            </div>
            <div class="flex items-center text-xs text-gray-500">
                <x-base.lucide icon="hard-drive" class="w-3 h-3 mr-1" />
                <span>{{ $fileSize }}</span>
            </div>
            @if($document->collection_name)
                <div class="flex items-center text-xs text-gray-500">
                    <x-base.lucide icon="folder" class="w-3 h-3 mr-1" />
                    <span class="capitalize">{{ str_replace('_', ' ', $document->collection_name) }}</span>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex items-center space-x-2">
            @if($document->getUrl())
                <a href="{{ $document->getUrl() }}" 
                   target="_blank"
                   class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <x-base.lucide icon="eye" class="w-3 h-3 mr-1" />
                    View
                </a>
                <a href="{{ $document->getUrl() }}" 
                   download
                   class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <x-base.lucide icon="download" class="w-3 h-3" />
                </a>
            @else
                <div class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">
                    <x-base.lucide icon="x-circle" class="w-3 h-3 mr-1" />
                    Unavailable
                </div>
            @endif
        </div>
    </div>
</div>