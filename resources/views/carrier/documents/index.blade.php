@extends('themes.base')

@section('title', 'Document Center - ' . $carrier->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 mb-2">Document Center</h1>
                <p class="text-slate-600">Manage your carrier documents for <strong>{{ $carrier->name }}</strong></p>
            </div>
            
            <!-- Skip Documents Button -->
            <div class="flex flex-col items-end">
                <a href="{{ route('carrier.documents.skip', $carrier->slug) }}" 
                   id="skip-documents-btn"
                   class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 transition-colors duration-200 shadow-sm">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="ArrowRight" />
                    Skip for Now
                </a>
                <p class="text-xs text-slate-500 mt-1 text-right">You can upload documents later from your dashboard</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Sidebar with Stats and Filters -->
        <div class="col-span-12 lg:col-span-3 space-y-6">
            <!-- Document Stats -->
            <x-carrier.document-stats :progress="$progress" />
            
            <!-- Document Filters -->
            <x-carrier.document-filters :documentStats="$documentStats" />
        </div>

        <!-- Main Content Area -->
        <div class="col-span-12 lg:col-span-9">
            <!-- Documents Grid -->
            <div id="documents-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($mappedDocuments as $document)
                    <x-carrier.document-card :document="$document" />
                @endforeach
            </div>

            <!-- Empty State -->
            @if(empty($mappedDocuments))
            <div class="text-center py-12">
                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400 mb-4" icon="FileText" />
                <h3 class="text-lg font-medium text-slate-900 mb-2">No documents found</h3>
                <p class="text-slate-500">Start by uploading your first document.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload Modal using x-base.dialog -->
<x-base.dialog id="upload-modal" size="lg">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h3 class="text-lg font-medium">Upload Document</h3>
        </x-base.dialog.title>
        
        <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
            <x-carrier.upload-form />
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

<script>
// Global variables
window.carrierSlug = '{{ $carrier->slug }}';
window.csrfToken = '{{ csrf_token() }}';
// Note: toggleDefaultUrl will be built dynamically since it requires documentType parameter

// Upload Modal Management
function openUploadModal(documentTypeId, documentTypeName) {
    document.getElementById('document_type_id').value = documentTypeId;
    document.getElementById('document-type-name').textContent = documentTypeName;
    
    // Reset form
    resetUploadForm();
    
    // Show modal
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#upload-modal"));
    modal.show();
}

function closeUploadModal() {
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#upload-modal"));
    modal.hide();
    resetUploadForm();
}

function resetUploadForm() {
    document.getElementById('upload-form').reset();
    document.getElementById('file-preview').classList.add('hidden');
    document.getElementById('upload-progress').classList.add('hidden');
    document.getElementById('upload-error').classList.add('hidden');
}

// File Upload Functions
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        showFilePreview(file);
    }
}

function handleDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const uploadArea = document.getElementById('upload-area');
    uploadArea.classList.remove('border-primary');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file-upload').files = files;
        showFilePreview(files[0]);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    document.getElementById('upload-area').classList.add('border-primary');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    document.getElementById('upload-area').classList.remove('border-primary');
}

function showFilePreview(file) {
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatFileSize(file.size);
    document.getElementById('file-preview').classList.remove('hidden');
}

function removeFile() {
    document.getElementById('file-upload').value = '';
    document.getElementById('file-preview').classList.add('hidden');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Submit Upload
async function submitUpload() {
    const form = document.getElementById('upload-form');
    const formData = new FormData(form);
    const fileInput = document.getElementById('file-upload');
    const documentTypeId = document.getElementById('document_type_id').value;
    
    if (!fileInput.files[0]) {
        showError('Please select a file to upload.');
        return;
    }
    
    if (!documentTypeId) {
        showError('Document type is required.');
        return;
    }
    
    try {
        showProgress();
        
        // Build the upload URL dynamically with the documentType parameter
        const uploadUrl = `{{ url('/carrier') }}/${window.carrierSlug}/documents/upload/${documentTypeId}`;
        
        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            showSuccess('Document uploaded successfully!');
            setTimeout(() => {
                closeUploadModal();
                location.reload();
            }, 1500);
        } else {
            showError(result.message || 'Upload failed. Please try again.');
        }
    } catch (error) {
        showError('Network error. Please try again.');
    } finally {
        hideProgress();
    }
}

function showProgress() {
    document.getElementById('upload-progress').classList.remove('hidden');
    document.getElementById('progress-bar').style.width = '100%';
}

function hideProgress() {
    document.getElementById('upload-progress').classList.add('hidden');
    document.getElementById('progress-bar').style.width = '0%';
}

function showError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('upload-error').classList.remove('hidden');
}

function showSuccess(message) {
    // You can implement a success notification here
    console.log(message);
}

// Document Actions
function viewDocument(documentId) {
    // Use Laravel route helper to generate the correct URL
    const baseUrl = '{{ route("carrier.documents.index", $carrier->slug) }}';
    const viewUrl = baseUrl.replace('/documents', `/documents/${documentId}/view`);
    window.open(viewUrl, '_blank');
}

function replaceDocument(documentTypeId, documentTypeName) {
    openUploadModal(documentTypeId, documentTypeName);
}

async function acceptDefaultDocument(documentTypeId) {
    try {
        // Build the toggle default URL dynamically with the documentType parameter
        const toggleDefaultUrl = `{{ url('/carrier') }}/${window.carrierSlug}/documents/toggle-default/${documentTypeId}`;
        
        const response = await fetch(toggleDefaultUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                document_type_id: documentTypeId,
                use_default: true
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            location.reload();
        } else {
            alert(result.message || 'Action failed. Please try again.');
        }
    } catch (error) {
        alert('Network error. Please try again.');
    }
}

// Filter Functions
function clearFilters() {
    // Reset all filter inputs
    document.querySelectorAll('input[name="status_filter"]').forEach(input => {
        input.checked = input.value === 'all';
    });
    
    document.querySelectorAll('input[name="requirement_filter"]').forEach(input => {
        input.checked = true;
    });
    
    // Show all documents
    filterDocuments();
}

function filterDocuments() {
    const statusFilter = document.querySelector('input[name="status_filter"]:checked')?.value || 'all';
    const requirementFilters = Array.from(document.querySelectorAll('input[name="requirement_filter"]:checked')).map(input => input.value);
    
    const documentCards = document.querySelectorAll('.document-card');
    
    documentCards.forEach(card => {
        const status = card.dataset.status;
        const requirement = card.dataset.requirement;
        
        const statusMatch = statusFilter === 'all' || status === statusFilter;
        const requirementMatch = requirementFilters.includes(requirement);
        
        if (statusMatch && requirementMatch) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Quick Actions Functions
function showMissingDocuments() {
    // Set the status filter to 'missing'
    const missingRadio = document.querySelector('input[name="status_filter"][value="missing"]');
    if (missingRadio) {
        // Uncheck all status filters first
        document.querySelectorAll('input[name="status_filter"]').forEach(input => {
            input.checked = false;
        });
        
        // Check the missing filter
        missingRadio.checked = true;
        
        // Apply the filter
        filterDocuments();
        
        // Scroll to the documents grid
        const documentsGrid = document.getElementById('documents-grid');
        if (documentsGrid) {
            documentsGrid.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Show a notification
        showNotification('Showing only missing documents', 'info');
    }
}

function refreshProgress() {
    // Show loading indicator
    showNotification('Refreshing progress...', 'info');
    
    // Reload the page to refresh all data
    setTimeout(() => {
        location.reload();
    }, 500);
}

function showNotification(message, type = 'info') {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg text-white transition-all duration-300 ${
        type === 'info' ? 'bg-blue-500' : 
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-gray-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Filter event listeners
    document.querySelectorAll('input[name="status_filter"], input[name="requirement_filter"]').forEach(input => {
        input.addEventListener('change', filterDocuments);
    });
});
</script>

<style>
/* Status badge styles for rejected documents */
.status-rejected {
    @apply bg-red-100 text-red-800 border border-red-200;
}

.document-card.rejected {
    @apply border-red-200 bg-red-50/30;
}

.document-card.rejected .status-badge {
    @apply bg-red-100 text-red-800;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Skip Documents button click
    const skipBtn = document.getElementById('skip-documents-btn');
    if (skipBtn) {
        skipBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-slate-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Redirecting to Dashboard...';
            this.classList.add('opacity-75', 'cursor-not-allowed');
            
            // Navigate to the skip route
            window.location.href = this.getAttribute('href');
        });
    }
});
</script>
@endsection