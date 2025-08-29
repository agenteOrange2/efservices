<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use App\Models\DriverDocumentStatus;
use App\Models\DocumentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DriverDocumentApiController extends Controller
{
    /**
     * Filter documents by category, search term, date range, and status
     */
    public function filterDocuments(Request $request, $driverId): JsonResponse
    {
        try {
            $driver = UserDriverDetail::findOrFail($driverId);
            
            $query = DriverDocumentStatus::where('driver_id', $driverId)
                ->with(['media', 'categoryInfo']);

            // Filter by category
            if ($request->filled('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by search term (search in media filename or notes)
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('media', function($mediaQuery) use ($searchTerm) {
                        $mediaQuery->where('name', 'like', "%{$searchTerm}%")
                                  ->orWhere('file_name', 'like', "%{$searchTerm}%");
                    })->orWhere('notes', 'like', "%{$searchTerm}%");
                });
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $documents = $query->orderBy('created_at', 'desc')->get();

            // Transform the data for frontend
            $transformedDocuments = $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'category' => $doc->category,
                    'status' => $doc->status,
                    'expiry_date' => $doc->expiry_date?->format('Y-m-d'),
                    'notes' => $doc->notes,
                    'created_at' => $doc->created_at->format('Y-m-d H:i:s'),
                    'media' => $doc->media ? [
                        'id' => $doc->media->id,
                        'name' => $doc->media->name,
                        'file_name' => $doc->media->file_name,
                        'mime_type' => $doc->media->mime_type,
                        'size' => $doc->media->size,
                        'url' => $doc->media->getUrl(),
                        'preview_url' => $doc->media->hasGeneratedConversion('preview') ? $doc->media->getUrl('preview') : null,
                    ] : null,
                    'category_info' => $doc->categoryInfo ? [
                        'display_name' => $doc->categoryInfo->display_name,
                        'icon' => $doc->categoryInfo->icon,
                        'color' => $doc->categoryInfo->color,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedDocuments,
                'total' => $transformedDocuments->count(),
                'filters_applied' => [
                    'category' => $request->category,
                    'status' => $request->status,
                    'search' => $request->search,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error filtering documents', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error filtering documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk download documents
     */
    public function bulkDownload(Request $request, $driverId): JsonResponse
    {
        try {
            $driver = UserDriverDetail::findOrFail($driverId);
            
            $query = DriverDocumentStatus::where('driver_id', $driverId)
                ->with(['media', 'categoryInfo']);

            // Filter by categories if specified
            if ($request->filled('categories') && is_array($request->categories)) {
                $query->whereIn('category', $request->categories);
            }

            // Filter by status if specified
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $documents = $query->get();

            if ($documents->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No documents found for download'
                ], 404);
            }

            // Create temporary directory for zip
            $tempDir = storage_path('app/temp/bulk_downloads');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zipFileName = 'driver_' . $driverId . '_documents_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = $tempDir . '/' . $zipFileName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create zip file'
                ], 500);
            }

            $addedFiles = 0;
            $metadata = [];

            foreach ($documents as $doc) {
                if ($doc->media && file_exists($doc->media->getPath())) {
                    $categoryFolder = $doc->categoryInfo ? $doc->categoryInfo->display_name : ucfirst($doc->category);
                    $fileName = $categoryFolder . '/' . $doc->media->file_name;
                    
                    $zip->addFile($doc->media->getPath(), $fileName);
                    $addedFiles++;

                    // Add metadata if requested
                    if ($request->boolean('include_metadata', false)) {
                        $metadata[] = [
                            'file_name' => $doc->media->file_name,
                            'category' => $doc->category,
                            'status' => $doc->status,
                            'expiry_date' => $doc->expiry_date?->format('Y-m-d'),
                            'upload_date' => $doc->created_at->format('Y-m-d H:i:s'),
                            'notes' => $doc->notes,
                            'file_size' => $doc->media->size,
                            'mime_type' => $doc->media->mime_type,
                        ];
                    }
                }
            }

            // Add metadata file if requested
            if ($request->boolean('include_metadata', false) && !empty($metadata)) {
                $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT);
                $zip->addFromString('metadata.json', $metadataJson);
            }

            $zip->close();

            if ($addedFiles === 0) {
                unlink($zipPath);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid files found for download'
                ], 404);
            }

            // Generate download URL (you might want to use a signed URL for security)
            $downloadUrl = route('admin.drivers.download-bulk', [
                'driver' => $driverId,
                'file' => basename($zipPath)
            ]);

            return response()->json([
                'success' => true,
                'download_url' => $downloadUrl,
                'file_name' => $zipFileName,
                'files_count' => $addedFiles,
                'file_size' => filesize($zipPath),
                'expires_at' => now()->addHours(1)->toISOString(), // Link expires in 1 hour
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating bulk download', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating bulk download: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document categories with counts
     */
    public function getCategories($driverId): JsonResponse
    {
        try {
            $categories = DocumentCategory::active()
                ->ordered()
                ->withCount(['documentStatuses' => function($query) use ($driverId) {
                    $query->where('driver_id', $driverId);
                }])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories->map(function($category) {
                    return [
                        'name' => $category->name,
                        'display_name' => $category->display_name,
                        'icon' => $category->icon,
                        'color' => $category->color,
                        'document_count' => $category->document_statuses_count,
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting categories', [
                'driver_id' => $driverId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error getting categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update document status
     */
    public function updateDocumentStatus(Request $request, $driverId, $documentId): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:active,expired,pending,rejected',
                'expiry_date' => 'nullable|date',
                'notes' => 'nullable|string|max:1000'
            ]);

            $document = DriverDocumentStatus::where('driver_id', $driverId)
                ->where('id', $documentId)
                ->firstOrFail();

            $document->update([
                'status' => $request->status,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully',
                'data' => $document->load(['media', 'categoryInfo'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating document status', [
                'driver_id' => $driverId,
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating document status: ' . $e->getMessage()
            ], 500);
        }
    }
}
