# Especificaciones Técnicas - Rediseño de Documentos del Carrier

## 1. Arquitectura Técnica

### 1.1 Stack Tecnológico

**Frontend:**
- Laravel Blade Components
- Tailwind CSS 3.x
- Alpine.js para interactividad
- Livewire para componentes reactivos
- Lucide Icons para iconografía

**Backend:**
- Laravel 10.x
- Spatie Media Library para gestión de archivos
- Laravel Validation para validación de archivos
- Laravel Queues para procesamiento asíncrono

### 1.2 Estructura de Componentes

```php
// Componente principal
<x-carrier.document-center 
    :carrier="$carrier" 
    :documents="$documents" 
    :progress="$progress" 
/>

// Subcomponentes
<x-carrier.document-header />
<x-carrier.document-grid />
<x-carrier.document-card />
<x-carrier.upload-modal />
<x-carrier.progress-indicator />
```

## 2. Definición de Componentes Blade

### 2.1 DocumentCenter Component

**Archivo:** `resources/views/components/carrier/document-center.blade.php`

```php
@props([
    'carrier',
    'documents',
    'progress' => [],
    'filters' => [],
    'bulkActions' => true
])

<div class="document-center min-h-screen bg-slate-50" x-data="documentCenter()">
    <!-- Header Section -->
    <x-carrier.document-header 
        :carrier="$carrier" 
        :progress="$progress" 
        :filters="$filters"
        :bulk-actions="$bulkActions" 
    />
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-carrier.document-grid :documents="$documents" />
    </div>
    
    <!-- Upload Modal -->
    <x-carrier.upload-modal />
    
    <!-- Notification System -->
    <x-carrier.notification-system />
</div>
```

### 2.2 DocumentHeader Component

**Archivo:** `resources/views/components/carrier/document-header.blade.php`

```php
@props([
    'carrier',
    'progress',
    'filters' => [],
    'bulkActions' => true
])

<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Progress Section -->
        <div class="py-4 border-b border-slate-100">
            <x-carrier.progress-indicator :progress="$progress" />
        </div>
        
        <!-- Title and Carrier Info -->
        <div class="py-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-slate-900 flex items-center">
                        <x-base.lucide icon="FileText" class="w-6 h-6 mr-3 text-blue-600" />
                        Document Center
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Manage and upload required documents for 
                        <span class="font-semibold text-slate-900">{{ $carrier->name }}</span>
                    </p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <x-base.button variant="outline" size="sm" x-on:click="openHelp()">
                        <x-base.lucide icon="HelpCircle" class="w-4 h-4 mr-2" />
                        Help
                    </x-base.button>
                    
                    <x-base.button variant="primary" size="sm" x-on:click="openBulkUpload()">
                        <x-base.lucide icon="Upload" class="w-4 h-4 mr-2" />
                        Bulk Upload
                    </x-base.button>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="pb-6">
            <x-carrier.document-filters :filters="$filters" />
        </div>
    </div>
</header>
```

### 2.3 DocumentCard Component

**Archivo:** `resources/views/components/carrier/document-card.blade.php`

```php
@props([
    'document',
    'carrier',
    'compact' => false
])

@php
$statusConfig = [
    'not_uploaded' => ['color' => 'slate', 'icon' => 'Upload', 'label' => 'Not Uploaded'],
    'uploading' => ['color' => 'blue', 'icon' => 'Loader2', 'label' => 'Uploading'],
    'pending' => ['color' => 'amber', 'icon' => 'Clock', 'label' => 'Pending Review'],
    'approved' => ['color' => 'emerald', 'icon' => 'CheckCircle', 'label' => 'Approved'],
    'rejected' => ['color' => 'red', 'icon' => 'XCircle', 'label' => 'Rejected'],
    'expired' => ['color' => 'orange', 'icon' => 'AlertTriangle', 'label' => 'Expired']
];

$status = $statusConfig[$document['status']] ?? $statusConfig['not_uploaded'];
@endphp

<div class="document-card bg-white rounded-xl shadow-sm border border-slate-200 hover:shadow-md hover:border-blue-300 transition-all duration-200"
     x-data="documentCard({{ json_encode($document) }})"
     :class="{ 'ring-2 ring-blue-500 ring-opacity-50': selected }">
     
    <!-- Card Header -->
    <div class="p-4 border-b border-slate-100">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    @if($document['type']->requirement)
                        <x-base.badge variant="red" size="sm" class="mr-2">Required</x-base.badge>
                    @endif
                    
                    <h3 class="text-lg font-semibold text-slate-900 truncate">
                        {{ $document['type']->name }}
                    </h3>
                </div>
                
                <p class="mt-1 text-sm text-slate-600 line-clamp-2">
                    {{ $document['type']->description ?? 'Please upload this document to complete your registration.' }}
                </p>
            </div>
            
            <!-- Status Badge -->
            <x-base.badge 
                :variant="$status['color']" 
                class="ml-3 flex items-center"
            >
                <x-base.lucide :icon="$status['icon']" class="w-3 h-3 mr-1" />
                {{ $status['label'] }}
            </x-base.badge>
        </div>
    </div>
    
    <!-- Card Body -->
    <div class="p-4">
        @if($document['file_url'])
            <!-- Document Uploaded -->
            <x-carrier.document-preview :document="$document" />
        @elseif($document['type']->getFirstMediaUrl('default_documents'))
            <!-- Default Document Available -->
            <x-carrier.default-document-section :document="$document" :carrier="$carrier" />
        @else
            <!-- Upload Zone -->
            <x-carrier.upload-zone :document="$document" :carrier="$carrier" />
        @endif
    </div>
    
    <!-- Card Footer -->
    <div class="px-4 pb-4">
        <x-carrier.document-actions :document="$document" :carrier="$carrier" />
    </div>
</div>
```

## 3. JavaScript Architecture

### 3.1 Alpine.js Data Components

**DocumentCenter Controller:**

```javascript
// resources/js/carrier/document-center.js
function documentCenter() {
    return {
        documents: [],
        selectedDocuments: [],
        filters: {
            status: 'all',
            type: 'all',
            search: ''
        },
        bulkActions: {
            visible: false,
            loading: false
        },
        
        init() {
            this.loadDocuments();
            this.setupEventListeners();
        },
        
        loadDocuments() {
            // Load documents via API
        },
        
        selectDocument(documentId) {
            if (this.selectedDocuments.includes(documentId)) {
                this.selectedDocuments = this.selectedDocuments.filter(id => id !== documentId);
            } else {
                this.selectedDocuments.push(documentId);
            }
            this.updateBulkActionsVisibility();
        },
        
        selectAllDocuments() {
            this.selectedDocuments = this.documents.map(doc => doc.id);
            this.updateBulkActionsVisibility();
        },
        
        clearSelection() {
            this.selectedDocuments = [];
            this.updateBulkActionsVisibility();
        },
        
        updateBulkActionsVisibility() {
            this.bulkActions.visible = this.selectedDocuments.length > 0;
        },
        
        async bulkUpload() {
            this.bulkActions.loading = true;
            // Implement bulk upload logic
            this.bulkActions.loading = false;
        },
        
        filterDocuments() {
            // Implement filtering logic
        },
        
        openHelp() {
            // Open help modal
        }
    }
}
```

**DocumentCard Controller:**

```javascript
// resources/js/carrier/document-card.js
function documentCard(document) {
    return {
        document: document,
        selected: false,
        uploading: false,
        uploadProgress: 0,
        
        init() {
            this.$watch('selected', (value) => {
                this.$dispatch('document-selection-changed', {
                    documentId: this.document.id,
                    selected: value
                });
            });
        },
        
        toggleSelection() {
            this.selected = !this.selected;
        },
        
        async uploadDocument(file) {
            this.uploading = true;
            this.uploadProgress = 0;
            
            try {
                const formData = new FormData();
                formData.append('document', file);
                formData.append('document_type_id', this.document.type.id);
                
                const response = await fetch(`/carrier/${this.document.carrier.slug}/documents/upload`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    onUploadProgress: (progressEvent) => {
                        this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    this.document = result.document;
                    this.$dispatch('document-uploaded', { document: this.document });
                    this.showNotification('Document uploaded successfully', 'success');
                } else {
                    throw new Error('Upload failed');
                }
            } catch (error) {
                this.showNotification('Upload failed: ' + error.message, 'error');
            } finally {
                this.uploading = false;
                this.uploadProgress = 0;
            }
        },
        
        async toggleDefaultDocument() {
            try {
                const response = await fetch(`/carrier/${this.document.carrier.slug}/documents/toggle-default/${this.document.type.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    this.document = result.document;
                    this.$dispatch('document-updated', { document: this.document });
                }
            } catch (error) {
                this.showNotification('Action failed: ' + error.message, 'error');
            }
        },
        
        showNotification(message, type) {
            this.$dispatch('show-notification', { message, type });
        }
    }
}
```

## 4. CSS Architecture

### 4.1 Component Styles

**Document Center Styles:**

```css
/* resources/css/carrier/document-center.css */
.document-center {
    @apply min-h-screen bg-slate-50;
}

.document-card {
    @apply bg-white rounded-xl shadow-sm border border-slate-200;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.document-card:hover {
    @apply shadow-md border-blue-300;
    transform: translateY(-1px);
}

.document-card.selected {
    @apply ring-2 ring-blue-500 ring-opacity-50 border-blue-500;
}

.upload-zone {
    @apply border-2 border-dashed border-slate-300 rounded-lg p-6;
    background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%23cbd5e1' stroke-width='2' stroke-dasharray='8%2c 8' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
    transition: all 0.2s ease;
}

.upload-zone:hover,
.upload-zone.dragover {
    @apply border-blue-500 bg-blue-50;
    background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' stroke='%233b82f6' stroke-width='2' stroke-dasharray='8%2c 8' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
}

.progress-indicator {
    @apply w-full bg-slate-200 rounded-full h-2 overflow-hidden;
}

.progress-value {
    @apply h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full;
    transition: width 0.3s ease;
}

/* Status indicators */
.status-not-uploaded { @apply text-slate-500 bg-slate-100; }
.status-uploading { @apply text-blue-600 bg-blue-100; }
.status-pending { @apply text-amber-600 bg-amber-100; }
.status-approved { @apply text-emerald-600 bg-emerald-100; }
.status-rejected { @apply text-red-600 bg-red-100; }
.status-expired { @apply text-orange-600 bg-orange-100; }
```

### 4.2 Responsive Design

```css
/* Mobile First Approach */
.document-grid {
    @apply grid gap-4;
    grid-template-columns: 1fr;
}

@media (min-width: 640px) {
    .document-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .document-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .document-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Mobile optimizations */
@media (max-width: 639px) {
    .document-card {
        @apply rounded-lg;
    }
    
    .document-header {
        @apply px-4 py-3;
    }
    
    .bulk-actions {
        @apply fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 p-4;
    }
}
```

## 5. API Endpoints

### 5.1 Document Management APIs

```php
// routes/carrier.php
Route::prefix('carrier/{carrier:slug}/documents')->name('carrier.documents.')->group(function () {
    Route::get('/', [CarrierDocumentController::class, 'index'])->name('index');
    Route::post('/upload', [CarrierDocumentController::class, 'upload'])->name('upload');
    Route::post('/bulk-upload', [CarrierDocumentController::class, 'bulkUpload'])->name('bulk-upload');
    Route::post('/toggle-default/{documentType}', [CarrierDocumentController::class, 'toggleDefault'])->name('toggle-default');
    Route::delete('/{document}', [CarrierDocumentController::class, 'delete'])->name('delete');
    Route::get('/progress', [CarrierDocumentController::class, 'progress'])->name('progress');
    Route::post('/complete', [CarrierDocumentController::class, 'complete'])->name('complete');
    Route::post('/skip', [CarrierDocumentController::class, 'skip'])->name('skip');
});
```

### 5.2 Controller Methods

```php
// app/Http/Controllers/Auth/CarrierDocumentController.php
class CarrierDocumentController extends Controller
{
    public function index($carrierSlug)
    {
        $carrier = $this->findCarrierBySlug($carrierSlug);
        $documents = $this->carrierDocumentService->getMappedDocuments($carrier);
        $progress = $this->carrierDocumentService->calculateProgress($carrier);
        
        return view('carrier.documents.index', compact('carrier', 'documents', 'progress'));
    }
    
    public function upload(Request $request, $carrierSlug)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_type_id' => 'required|exists:document_types,id'
        ]);
        
        $carrier = $this->findCarrierBySlug($carrierSlug);
        $documentType = DocumentType::findOrFail($request->document_type_id);
        
        $result = $this->carrierDocumentService->uploadDocument(
            $carrier,
            $documentType,
            $request->file('document')
        );
        
        return response()->json([
            'success' => true,
            'document' => $result,
            'progress' => $this->carrierDocumentService->calculateProgress($carrier)
        ]);
    }
    
    public function bulkUpload(Request $request, $carrierSlug)
    {
        $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);
        
        $carrier = $this->findCarrierBySlug($carrierSlug);
        
        $results = [];
        foreach ($request->file('documents') as $file) {
            // Process each file
            $results[] = $this->carrierDocumentService->processUploadedFile($carrier, $file);
        }
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'progress' => $this->carrierDocumentService->calculateProgress($carrier)
        ]);
    }
}
```

## 6. Database Schema

### 6.1 Existing Tables Enhancement

```sql
-- Add new columns to carrier_documents table
ALTER TABLE carrier_documents ADD COLUMN upload_progress TINYINT DEFAULT 0;
ALTER TABLE carrier_documents ADD COLUMN file_size BIGINT DEFAULT NULL;
ALTER TABLE carrier_documents ADD COLUMN mime_type VARCHAR(100) DEFAULT NULL;
ALTER TABLE carrier_documents ADD COLUMN uploaded_at TIMESTAMP NULL;
ALTER TABLE carrier_documents ADD COLUMN expires_at TIMESTAMP NULL;

-- Add indexes for performance
CREATE INDEX idx_carrier_documents_status ON carrier_documents(status);
CREATE INDEX idx_carrier_documents_carrier_status ON carrier_documents(carrier_id, status);
CREATE INDEX idx_carrier_documents_expires_at ON carrier_documents(expires_at);
```

### 6.2 New Tables

```sql
-- Document upload sessions for tracking bulk uploads
CREATE TABLE document_upload_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carrier_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    total_files INT DEFAULT 0,
    processed_files INT DEFAULT 0,
    failed_files INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (carrier_id) REFERENCES carriers(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_carrier_status (carrier_id, status)
);

-- Document validation rules
CREATE TABLE document_validation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_type_id BIGINT UNSIGNED NOT NULL,
    rule_type ENUM('file_size', 'mime_type', 'dimensions', 'custom') NOT NULL,
    rule_value JSON NOT NULL,
    error_message TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (document_type_id) REFERENCES document_types(id) ON DELETE CASCADE,
    INDEX idx_document_type (document_type_id)
);
```

## 7. Performance Optimizations

### 7.1 Caching Strategy

```php
// app/Services/CarrierDocumentService.php
class CarrierDocumentService
{
    public function getMappedDocuments(Carrier $carrier)
    {
        return Cache::remember(
            "carrier_documents_{$carrier->id}",
            now()->addMinutes(30),
            function () use ($carrier) {
                return $this->buildDocumentMapping($carrier);
            }
        );
    }
    
    public function calculateProgress(Carrier $carrier)
    {
        return Cache::remember(
            "carrier_progress_{$carrier->id}",
            now()->addMinutes(15),
            function () use ($carrier) {
                return $this->buildProgressData($carrier);
            }
        );
    }
}
```

### 7.2 File Upload Optimization

```php
// app/Jobs/ProcessDocumentUpload.php
class ProcessDocumentUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        // Process file upload in background
        // Generate thumbnails
        // Run OCR if needed
        // Update document status
        // Send notifications
    }
}
```

## 8. Testing Strategy

### 8.1 Unit Tests

```php
// tests/Unit/CarrierDocumentServiceTest.php
class CarrierDocumentServiceTest extends TestCase
{
    public function test_can_upload_document()
    {
        $carrier = Carrier::factory()->create();
        $documentType = DocumentType::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 1024);
        
        $result = $this->carrierDocumentService->uploadDocument($carrier, $documentType, $file);
        
        $this->assertInstanceOf(CarrierDocument::class, $result);
        $this->assertEquals('pending', $result->status);
    }
    
    public function test_calculates_progress_correctly()
    {
        $carrier = Carrier::factory()->create();
        // Create test documents...
        
        $progress = $this->carrierDocumentService->calculateProgress($carrier);
        
        $this->assertArrayHasKey('percentage', $progress);
        $this->assertArrayHasKey('completed', $progress);
        $this->assertArrayHasKey('total', $progress);
    }
}
```

### 8.2 Feature Tests

```php
// tests/Feature/CarrierDocumentUploadTest.php
class CarrierDocumentUploadTest extends TestCase
{
    public function test_user_can_upload_document()
    {
        $user = User::factory()->create();
        $carrier = Carrier::factory()->create();
        $documentType = DocumentType::factory()->create();
        
        $response = $this->actingAs($user)
            ->post("/carrier/{$carrier->slug}/documents/upload", [
                'document' => UploadedFile::fake()->create('test.pdf', 1024),
                'document_type_id' => $documentType->id
            ]);
            
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
```

## 9. Security Considerations

### 9.1 File Upload Security

```php
// app/Rules/SecureFileUpload.php
class SecureFileUpload implements Rule
{
    public function passes($attribute, $value)
    {
        // Validate file type
        if (!in_array($value->getMimeType(), $this->allowedMimeTypes())) {
            return false;
        }
        
        // Check file size
        if ($value->getSize() > $this->maxFileSize()) {
            return false;
        }
        
        // Scan for malware (if antivirus service available)
        if ($this->containsMalware($value)) {
            return false;
        }
        
        return true;
    }
}
```

### 9.2 Access Control

```php
// app/Policies/CarrierDocumentPolicy.php
class CarrierDocumentPolicy
{
    public function upload(User $user, Carrier $carrier)
    {
        return $user->carrierDetails && 
               $user->carrierDetails->carrier_id === $carrier->id &&
               $carrier->status === 'active';
    }
    
    public function view(User $user, Carrier $carrier)
    {
        return $this->upload($user, $carrier);
    }
}
```

## 10. Deployment Checklist

### 10.1 Pre-deployment

- [ ] Run all tests (unit, feature, browser)
- [ ] Check database migrations
- [ ] Verify file upload limits
- [ ] Test on staging environment
- [ ] Performance testing
- [ ] Security audit
- [ ] Accessibility testing

### 10.2 Post-deployment

- [ ] Monitor error logs
- [ ] Check file upload functionality
- [ ] Verify caching is working
- [ ] Monitor performance metrics
- [ ] User acceptance testing
- [ ] Gather feedback
- [ ] Plan next iteration

Esta especificación técnica proporciona una guía completa para implementar el rediseño de la página de documentos del carrier con componentes profesionales y funcionalidad mejorada.