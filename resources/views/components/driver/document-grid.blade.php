@props(['documents' => [], 'category' => 'all'])

@php
    $documentsByCategory = collect($documents)->groupBy('collection_name');
    $categories = [
        'all' => 'All Documents',
        'licenses' => 'Licenses',
        'medical' => 'Medical Records',
        'employment' => 'Employment',
        'training' => 'Training & Certificates',
        'testing' => 'Drug & Alcohol Testing',
        'inspections' => 'Vehicle Inspections',
        'personal' => 'Personal Documents',
        'other' => 'Other Documents'
    ];
@endphp

<div class="space-y-6">
    <!-- Category Tabs -->
    @if($documentsByCategory->count() > 1)
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Document Categories">
                <button type="button"
                        onclick="showDocumentCategory('all')"
                        class="document-category-tab whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $category === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        data-category="all">
                    All Documents ({{ collect($documents)->count() }})
                </button>
                @foreach($documentsByCategory as $categoryName => $categoryDocs)
                    <button type="button"
                            onclick="showDocumentCategory('{{ $categoryName }}')"
                            class="document-category-tab whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $category === $categoryName ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                            data-category="{{ $categoryName }}">
                        {{ $categories[$categoryName] ?? ucfirst($categoryName) }} ({{ $categoryDocs->count() }})
                    </button>
                @endforeach
            </nav>
        </div>
    @endif

    <!-- Documents Grid -->
    <div class="document-categories">
        <!-- All Documents -->
        <div id="all-documents" class="document-category-content {{ $category === 'all' ? '' : 'hidden' }}">
            @if(collect($documents)->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($documents as $document)
                        <x-driver.document-card :document="$document" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                        <x-base.lucide icon="file-x" class="w-6 h-6 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No Documents</h3>
                    <p class="text-sm text-gray-500">No documents have been uploaded yet.</p>
                </div>
            @endif
        </div>

        <!-- Category-specific Documents -->
        @foreach($documentsByCategory as $categoryName => $categoryDocs)
            <div id="{{ $categoryName }}-documents" class="document-category-content {{ $category === $categoryName ? '' : 'hidden' }}">
                @if($categoryDocs->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($categoryDocs as $document)
                            <x-driver.document-card :document="$document" />
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                            <x-base.lucide icon="file-x" class="w-6 h-6 text-gray-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No {{ $categories[$categoryName] ?? ucfirst($categoryName) }}</h3>
                        <p class="text-sm text-gray-500">No documents in this category yet.</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<script>
function showDocumentCategory(category) {
    // Hide all category content
    document.querySelectorAll('.document-category-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected category content
    const selectedContent = document.getElementById(category + '-documents');
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Update tab styles
    document.querySelectorAll('.document-category-tab').forEach(tab => {
        const tabCategory = tab.getAttribute('data-category');
        if (tabCategory === category) {
            tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            tab.classList.add('border-blue-500', 'text-blue-600');
        } else {
            tab.classList.remove('border-blue-500', 'text-blue-600');
            tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        }
    });
}
</script>