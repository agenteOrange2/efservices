<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use ZipArchive;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverDocumentsController extends Controller
{
    /**
     * Display the documents tab for a specific driver
     */
    public function index(UserDriverDetail $driver)
    {
        try {
            $documentsByCategory = $this->getDocumentsByCategory($driver);
            $documentStats = $this->getDocumentStats($documentsByCategory);
            
            return view('admin.drivers.list-driver.tabs.documents', compact(
                'driver',
                'documentsByCategory',
                'documentStats'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading driver documents', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error loading documents: ' . $e->getMessage());
        }
    }

    /**
     * Download all documents for a driver as ZIP
     */
    public function downloadAll(UserDriverDetail $driver)
    {
        try {
            Log::info('Starting downloadAll process', ['driver_id' => $driver->id]);
            
            // Get documents by category with error handling for each category
            $documentsByCategory = [];
            $categoryMethods = [
                'license' => 'getLicenseDocuments',
                'medical' => 'getMedicalDocuments',
                'training_schools' => 'getTrainingDocuments',
                'courses' => 'getCourseDocuments',
                'accidents' => 'getAccidentDocuments',
                'traffic_violations' => 'getTrafficDocuments',
                'inspections' => 'getInspectionDocuments',
                'testing' => 'getTestingDocuments',
                'driving_records' => 'getDrivingRecordDocuments',
                'criminal_records' => 'getCriminalRecordDocuments',
                'medical_records' => 'getMedicalRecordDocuments',
                'clearing_house_records' => 'getClearingHouseRecordDocuments',
                'vehicle_verifications' => 'getVehicleVerificationDocuments',
                'records' => 'getRecordDocuments',
                'application_forms' => 'getApplicationDocuments',
                'individual_application_forms' => 'getIndividualApplicationDocuments',
                'employment_verification' => 'getEmploymentDocuments',
                'other' => 'getOtherDocuments'
            ];
            
            foreach ($categoryMethods as $category => $method) {
                try {
                    Log::info("Processing category: {$category} with method: {$method}");
                    $documentsByCategory[$category] = $this->$method($driver);
                    Log::info("Category {$category} processed successfully", ['count' => count($documentsByCategory[$category])]);
                } catch (\Exception $e) {
                    Log::error("Error processing category {$category}", [
                        'method' => $method,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $documentsByCategory[$category] = []; // Set empty array to continue
                }
            }
            
            Log::info('All categories processed, creating ZIP', ['total_categories' => count($documentsByCategory)]);
            
            $zipPath = $this->createDocumentZip($driver, $documentsByCategory);
            
            if (!$zipPath) {
                Log::warning('No ZIP path returned', ['driver_id' => $driver->id]);
                return back()->with('error', 'No documents found to download.');
            }
            
            $fileName = "driver_{$driver->id}_documents_" . date('Y-m-d_H-i-s') . ".zip";
            
            Log::info('Returning download response', [
                'driver_id' => $driver->id,
                'zip_path' => $zipPath,
                'file_name' => $fileName,
                'file_exists' => file_exists($zipPath),
                'file_size' => file_exists($zipPath) ? filesize($zipPath) : 0
            ]);
            
            return Response::download($zipPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Critical error in downloadAll', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'Error downloading documents: ' . $e->getMessage());
        }
    }

    /**
     * Download selected documents as ZIP
     */
    public function downloadSelected(Request $request)
    {
        try {
            $documentIds = $request->input('documents', []);
            
            if (empty($documentIds)) {
                return back()->with('error', 'No documents selected.');
            }
            
            $zipPath = $this->createSelectedDocumentsZip($documentIds);
            
            if (!$zipPath) {
                return back()->with('error', 'Error creating document archive.');
            }
            
            $fileName = "selected_documents_" . date('Y-m-d_H-i-s') . ".zip";
            
            return Response::download($zipPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error downloading selected documents', [
                'document_ids' => $request->input('documents', []),
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error downloading selected documents: ' . $e->getMessage());
        }
    }

    /**
     * Get documents organized by category
     */
    public function getDocumentsByCategory(UserDriverDetail $driver)
    {
        $categories = [
            'license' => $this->getLicenseDocuments($driver),
            'medical' => $this->getMedicalDocuments($driver),
            'training_schools' => $this->getTrainingDocuments($driver),
            'courses' => $this->getCourseDocuments($driver),
            'accidents' => $this->getAccidentDocuments($driver),
            'traffic_violations' => $this->getTrafficDocuments($driver),
            'inspections' => $this->getInspectionDocuments($driver),
            'testing' => $this->getTestingDocuments($driver),
            'driving_records' => $this->getDrivingRecordDocuments($driver),
            'criminal_records' => $this->getCriminalRecordDocuments($driver),
            'medical_records' => $this->getMedicalRecordDocuments($driver),
            'clearing_house_records' => $this->getClearingHouseRecordDocuments($driver),
            'vehicle_verifications' => $this->getVehicleVerificationDocuments($driver),
            'records' => $this->getRecordDocuments($driver),
            'application_forms' => $this->getApplicationDocuments($driver),
            'individual_application_forms' => $this->getIndividualApplicationDocuments($driver),
            'employment_verification' => $this->getEmploymentDocuments($driver),
            'other' => $this->getOtherDocuments($driver)
        ];
        
        return $categories;
    }

    /**
     * Get license documents
     */
    private function getLicenseDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting license documents', ['driver_id' => $driver->id]);
        
        // Force reload the licenses relation to ensure we get ALL licenses
        $driver->load('licenses');
        
        // Get ALL licenses using multiple approaches to ensure we don't miss any
        $allLicenses = collect();
        
        // Method 1: Use the licenses relation
        if ($driver->licenses && $driver->licenses->count() > 0) {
            $allLicenses = $allLicenses->merge($driver->licenses);
            Log::info('Driver licenses relation found', ['count' => $driver->licenses->count()]);
        }
        
        // Method 2: Direct query to ensure we get ALL licenses (in case relation is cached or limited)
        $directLicenses = \App\Models\Admin\Driver\DriverLicense::where('user_driver_detail_id', $driver->id)->get();
        if ($directLicenses->count() > 0) {
            $allLicenses = $allLicenses->merge($directLicenses);
            Log::info('Direct query licenses found', ['count' => $directLicenses->count()]);
        }
        
        // Remove duplicates by ID
        $allLicenses = $allLicenses->unique('id');
        
        Log::info('Total unique licenses found', ['count' => $allLicenses->count()]);
        
        // Process each license
        foreach ($allLicenses as $license) {
            Log::info('Processing license', [
                'license_id' => $license->id, 
                'license_number' => $license->current_license_number ?? $license->license_number ?? 'N/A'
            ]);
            
            // Get ALL media from license (license_front, license_back, etc.)
            $allLicenseMedia = collect();
            
            // Get specific collections
            $licenseCollections = ['license_front', 'license_back', 'license', 'license_documents'];
            foreach ($licenseCollections as $collection) {
                $collectionMedia = $license->getMedia($collection);
                if ($collectionMedia->count() > 0) {
                    $allLicenseMedia = $allLicenseMedia->merge($collectionMedia);
                    Log::info('License media in collection', [
                        'license_id' => $license->id,
                        'collection' => $collection, 
                        'count' => $collectionMedia->count()
                    ]);
                }
            }
            
            // Also get ALL media from this license (in case there are other collections)
            $allMedia = $license->getMedia();
            if ($allMedia->count() > 0) {
                $allLicenseMedia = $allLicenseMedia->merge($allMedia);
                Log::info('All license media found', ['license_id' => $license->id, 'total_count' => $allMedia->count()]);
            }
            
            // Remove duplicates
            $allLicenseMedia = $allLicenseMedia->unique('id');
            
            Log::info('License media found (unique)', ['license_id' => $license->id, 'media_count' => $allLicenseMedia->count()]);
            
            foreach ($allLicenseMedia as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $licenseInfo = $license->current_license_number ?? $license->license_number ?? 'License #' . $license->id;
                
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'license',
                    'collection' => $document->collection_name,
                    'related_info' => $licenseInfo
                ];
            }
        }
        
        // Also check driver's direct media (just in case some are stored there)
        $driverMedia = collect();
        $licenseCollections = ['license_front', 'license_back', 'license'];
        foreach ($licenseCollections as $collection) {
            $collectionMedia = $driver->getMedia($collection);
            if ($collectionMedia->count() > 0) {
                $driverMedia = $driverMedia->merge($collectionMedia);
                Log::info('Driver direct license media in collection', ['collection' => $collection, 'count' => $collectionMedia->count()]);
            }
        }
        
        Log::info('Driver direct license media found', ['count' => $driverMedia->count()]);
        
        foreach ($driverMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'driver_license',
                'collection' => $document->collection_name,
                'related_info' => 'Driver License (Direct)'
            ];
        }
        
        Log::info('Total license documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get medical documents
     */
    private function getMedicalDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting medical documents', ['driver_id' => $driver->id]);
        
        // Check medicalQualification relation - THIS IS WHERE THE MEDIA ACTUALLY IS
        if ($driver->medicalQualification) {
            Log::info('Medical qualification relation found');
            
            // Get ALL medical media from the medical qualification
            $allMedicalMedia = collect();
            $allMedicalMedia = $allMedicalMedia->merge($driver->medicalQualification->getMedia('medical_card'));
            $allMedicalMedia = $allMedicalMedia->merge($driver->medicalQualification->getMedia('medical_records'));
            $allMedicalMedia = $allMedicalMedia->merge($driver->medicalQualification->getMedia('medical'));
            $allMedicalMedia = $allMedicalMedia->merge($driver->medicalQualification->getMedia('medical_certificate'));
            $allMedicalMedia = $allMedicalMedia->merge($driver->medicalQualification->getMedia()); // Get all collections
            
            Log::info('Medical qualification media found', ['count' => $allMedicalMedia->count()]);
            
            foreach ($allMedicalMedia as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'medical',
                    'collection' => $document->collection_name,
                    'related_info' => 'Medical Qualification'
                ];
            }
        } else {
            Log::info('No medical qualification relation found for driver', ['driver_id' => $driver->id]);
        }
        
        // Also check ALL medical qualifications for this driver (in case there are multiple)
        if ($driver->medicalQualifications) {
            Log::info('Medical qualifications relation found', ['count' => $driver->medicalQualifications->count()]);
            foreach ($driver->medicalQualifications as $medicalQual) {
                $allMedicalMedia = collect();
                $allMedicalMedia = $allMedicalMedia->merge($medicalQual->getMedia('medical_card'));
                $allMedicalMedia = $allMedicalMedia->merge($medicalQual->getMedia('medical_records'));
                $allMedicalMedia = $allMedicalMedia->merge($medicalQual->getMedia('medical'));
                $allMedicalMedia = $allMedicalMedia->merge($medicalQual->getMedia()); // Get all collections
                
                Log::info('Medical qualification media found', ['medical_qual_id' => $medicalQual->id, 'count' => $allMedicalMedia->count()]);
                
                foreach ($allMedicalMedia as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'medical',
                        'collection' => $document->collection_name,
                        'related_info' => 'Medical Qualification #' . $medicalQual->id
                    ];
                }
            }
        }
        
        // Also check driver's direct medical media collections (just in case)
        $medicalCollections = ['medical', 'medical_certificate', 'medical_qualification', 'medical_card', 'medical_records'];
        foreach ($medicalCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Medical media in collection (direct)', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'medical',
                    'collection' => $document->collection_name,
                    'related_info' => 'Medical Document (Direct)'
                ];
            }
        }
        
        Log::info('Total medical documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get training school documents
     */
    private function getTrainingDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting training documents', ['driver_id' => $driver->id]);
        
        // Check trainingSchools relation - THIS IS WHERE THE MEDIA ACTUALLY IS
        if ($driver->trainingSchools) {
            Log::info('Training schools relation found', ['count' => $driver->trainingSchools->count()]);
            foreach ($driver->trainingSchools as $school) {
                // Get ALL training media from the school
                $allTrainingMedia = collect();
                $allTrainingMedia = $allTrainingMedia->merge($school->getMedia('school_certificates'));
                $allTrainingMedia = $allTrainingMedia->merge($school->getMedia('training_certificate'));
                $allTrainingMedia = $allTrainingMedia->merge($school->getMedia('training'));
                $allTrainingMedia = $allTrainingMedia->merge($school->getMedia()); // Get all collections
                
                Log::info('Training school media found', ['school_id' => $school->id, 'media_count' => $allTrainingMedia->count()]);
                
                foreach ($allTrainingMedia as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'training',
                        'collection' => $document->collection_name,
                        'related_info' => $school->school_name ?? 'Training School #' . $school->id
                    ];
                }
            }
        } else {
            Log::info('No training schools relation found for driver', ['driver_id' => $driver->id]);
        }
        
        // Also check driver's direct training media collections (just in case)
        $trainingCollections = ['training', 'training_schools', 'training_certificate', 'school_certificates'];
        foreach ($trainingCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Training media in collection (direct)', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'training',
                    'collection' => $document->collection_name,
                    'related_info' => 'Training Document (Direct)'
                ];
            }
        }
        
        Log::info('Total training documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get course documents
     */
    private function getCourseDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting course documents', ['driver_id' => $driver->id]);
        
        // Check courses relation - THIS IS WHERE THE MEDIA ACTUALLY IS
        if ($driver->courses) {
            Log::info('Courses relation found', ['count' => $driver->courses->count()]);
            foreach ($driver->courses as $course) {
                // Get ALL course media from the course
                $allCourseMedia = collect();
                $allCourseMedia = $allCourseMedia->merge($course->getMedia('course_certificates'));
                $allCourseMedia = $allCourseMedia->merge($course->getMedia('course_certificate'));
                $allCourseMedia = $allCourseMedia->merge($course->getMedia('course'));
                $allCourseMedia = $allCourseMedia->merge($course->getMedia()); // Get all collections
                
                Log::info('Course media found', ['course_id' => $course->id, 'media_count' => $allCourseMedia->count()]);
                
                foreach ($allCourseMedia as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'course',
                        'collection' => $document->collection_name,
                        'related_info' => $course->course_name ?? 'Course #' . $course->id
                    ];
                }
            }
        } else {
            Log::info('No courses relation found for driver', ['driver_id' => $driver->id]);
        }
        
        // Also check driver's direct course media collections (just in case)
        $courseCollections = ['courses', 'course', 'course_certificate', 'course_certificates'];
        foreach ($courseCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Course media in collection (direct)', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'course',
                    'collection' => $document->collection_name,
                    'related_info' => 'Course Document (Direct)'
                ];
            }
        }
        
        Log::info('Total course documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get accident documents
     */
    private function getAccidentDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting accident documents', ['driver_id' => $driver->id]);
        
        // Ensure accidents relation is loaded
        $driver->load('accidents');
        
        // Check driver's direct accident media collections
        $accidentCollections = ['accidents', 'accident', 'accident_report', 'accident_documents', 'accident_images'];
        foreach ($accidentCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Accident media in collection', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'accident',
                    'collection' => $document->collection_name,
                    'related_info' => 'Accident Document'
                ];
            }
        }
        
        // Check accidents relation (DriverAccident model)
        Log::info('Checking accidents relation', ['accidents_count' => $driver->accidents->count()]);
        foreach ($driver->accidents as $accident) {
            Log::info('Processing accident record', ['accident_id' => $accident->id, 'nature' => $accident->nature_of_accident]);
            
            // Get all media from all collections for this accident record
            $allAccidentCollections = ['default', 'accident_images', 'accident_documents', 'documents', 'files', 'accidents'];
            foreach ($allAccidentCollections as $collection) {
                $media = $accident->getMedia($collection);
                Log::info('Accident record media in collection', [
                    'accident_id' => $accident->id, 
                    'collection' => $collection, 
                    'media_count' => $media->count()
                ]);
                
                foreach ($media as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'accident',
                        'collection' => $document->collection_name,
                        'related_info' => ($accident->nature_of_accident ?? 'Accident') . ' - ' . ($accident->accident_date ? $accident->accident_date->format('M d, Y') : 'No Date')
                    ];
                }
            }
        }
        
        Log::info('Total accident documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get traffic violation documents
     */
    private function getTrafficDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting traffic documents', ['driver_id' => $driver->id]);
        
        // Ensure trafficConvictions relation is loaded
        $driver->load('trafficConvictions');
        
        // Check driver's direct traffic media collections
        $trafficCollections = ['traffic_violations', 'traffic', 'traffic_conviction', 'traffic_images', 'traffic_documents'];
        foreach ($trafficCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Traffic media in collection', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'traffic',
                    'collection' => $document->collection_name,
                    'related_info' => 'Traffic Document'
                ];
            }
        }
        
        // Check trafficConvictions relation (DriverTrafficConviction model)
        Log::info('Checking traffic convictions relation', ['convictions_count' => $driver->trafficConvictions->count()]);
        foreach ($driver->trafficConvictions as $conviction) {
            Log::info('Processing traffic conviction record', ['conviction_id' => $conviction->id, 'charge' => $conviction->charge]);
            
            // Get all media from all collections for this traffic conviction record
            $allTrafficCollections = ['default', 'traffic_images', 'traffic_documents', 'documents', 'files', 'traffic'];
            foreach ($allTrafficCollections as $collection) {
                $media = $conviction->getMedia($collection);
                Log::info('Traffic conviction media in collection', [
                    'conviction_id' => $conviction->id, 
                    'collection' => $collection, 
                    'media_count' => $media->count()
                ]);
                
                foreach ($media as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'traffic',
                        'collection' => $document->collection_name,
                        'related_info' => ($conviction->charge ?? 'Traffic Violation') . ' - ' . ($conviction->conviction_date ? $conviction->conviction_date->format('M d, Y') : 'No Date')
                    ];
                }
            }
        }
        
        Log::info('Total traffic documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get inspection documents
     */
    private function getInspectionDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting inspection documents', ['driver_id' => $driver->id]);
        
        // Check driver's direct inspection media collections
        $inspectionCollections = ['inspections', 'inspection', 'inspection_report'];
        foreach ($inspectionCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Inspection media in collection', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'inspection',
                    'collection' => $document->collection_name,
                    'related_info' => 'Inspection Document'
                ];
            }
        }
        
        // Check inspections relation
        if ($driver->inspections) {
            Log::info('Inspections relation found', ['count' => $driver->inspections->count()]);
            foreach ($driver->inspections as $inspection) {
                $media = $inspection->getMedia();
                Log::info('Inspection media found', ['inspection_id' => $inspection->id, 'media_count' => $media->count()]);
                foreach ($media as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'inspection',
                        'collection' => $document->collection_name,
                        'related_info' => $inspection->inspection_date ? Carbon::parse($inspection->inspection_date)->format('M d, Y') : 'Inspection'
                    ];
                }
            }
        } else {
            Log::info('No inspections relation found for driver', ['driver_id' => $driver->id]);
        }
        
        Log::info('Total inspection documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get testing documents
     */
    private function getTestingDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting testing documents', ['driver_id' => $driver->id]);
        
        // Ensure testings relation is loaded
        $driver->load('testings');
        
        // Check driver's direct testing media collections
        $testingCollections = ['testing', 'tests', 'test_results', 'drug_test', 'alcohol_test', 'medical_test'];
        foreach ($testingCollections as $collection) {
            $media = $driver->getMedia($collection);
            Log::info('Testing media in collection', ['collection' => $collection, 'count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'testing',
                    'collection' => $document->collection_name,
                    'related_info' => 'Testing Document'
                ];
            }
        }
        
        // Check testings relation (DriverTesting model)
        Log::info('Checking testings relation', ['testings_count' => $driver->testings->count()]);
        foreach ($driver->testings as $testing) {
            Log::info('Processing testing record', ['testing_id' => $testing->id, 'test_type' => $testing->test_type]);
            
            // Get all media from all collections for this testing record
            $allTestingCollections = ['default', 'testing', 'tests', 'test_results', 'documents', 'files'];
            foreach ($allTestingCollections as $collection) {
                $media = $testing->getMedia($collection);
                Log::info('Testing record media in collection', [
                    'testing_id' => $testing->id, 
                    'collection' => $collection, 
                    'media_count' => $media->count()
                ]);
                
                foreach ($media as $document) {
                    $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                    $documents[] = [
                        'id' => $document->id,
                        'name' => $document->name,
                        'original_name' => $originalName,
                        'url' => $document->getUrl(),
                        'size' => $this->formatFileSize($document->size),
                        'date' => $document->created_at->format('M d, Y'),
                        'type' => 'testing',
                        'collection' => $document->collection_name,
                        'related_info' => ($testing->test_type ?? 'Test') . ' - ' . ($testing->test_date ? $testing->test_date->format('M d, Y') : 'No Date')
                    ];
                }
            }
        }
        
        Log::info('Total testing documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get general record documents (excluding specific record types that have their own categories)
     */
    private function getRecordDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting record documents', ['driver_id' => $driver->id]);
        
        // Get documents from various collections that don't fit other specific categories
        $generalMedia = collect();
        $generalMedia = $generalMedia->merge($driver->getMedia('records'));
        $generalMedia = $generalMedia->merge($driver->getMedia('general'));
        $generalMedia = $generalMedia->merge($driver->getMedia('documents'));
        
        Log::info('Record media found', ['count' => $generalMedia->count()]);
        
        // Exclude collections that are handled by specific methods
        $excludedCollections = [
            'driving_records', 
            'criminal_records', 
            'medical_records', 
            'clearing_house_records'
        ];
        
        foreach ($generalMedia as $document) {
            // Skip documents that belong to specific record categories
            if (!in_array($document->collection_name, $excludedCollections)) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'record',
                    'collection' => $document->collection_name,
                    'related_info' => 'General Record'
                ];
            }
        }
        
        Log::info('Total record documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get application form documents
     */
    private function getApplicationDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting application documents', ['driver_id' => $driver->id]);
        
        if ($driver->application) {
            $media = $driver->application->getMedia();
            Log::info('Application media found', ['count' => $media->count()]);
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'application',
                    'collection' => $document->collection_name,
                    'related_info' => 'Application Form'
                ];
            }
        } else {
            Log::info('No application relation found for driver', ['driver_id' => $driver->id]);
        }
        
        // Get signed application documents
        $signedMedia = collect();
        $signedMedia = $signedMedia->merge($driver->getMedia('signed_application'));
        $signedMedia = $signedMedia->merge($driver->getMedia('application_pdf'));
        Log::info('Signed application media found', ['count' => $signedMedia->count()]);
        
        foreach ($signedMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'signed_application',
                'collection' => $document->collection_name,
                'related_info' => 'Signed Application'
            ];
        }
        
        Log::info('Total application documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get employment verification documents
     */
    private function getEmploymentDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting employment documents', ['driver_id' => $driver->id]);
        
        // Manual employment documents
        $manualCount = 0;
        foreach ($driver->employmentCompanies as $company) {
            $media = $company->getMedia();
            $manualCount += $media->count();
            foreach ($media as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'employment_manual',
                    'collection' => $document->collection_name,
                    'company_name' => $company->company_name ?? 'N/A'
                ];
            }
        }
        Log::info('Manual employment documents found', ['count' => $manualCount]);
        
        // Email verified documents (if the model exists)
        $autoCount = 0;
        if (class_exists(EmploymentVerificationToken::class)) {
            $tokens = EmploymentVerificationToken::whereIn('employment_company_id', 
                $driver->employmentCompanies->pluck('id'))
                ->whereNotNull('verified_at')
                ->whereNotNull('document_path')
                ->get();
                
            foreach ($tokens as $token) {
                if (Storage::disk('public')->exists($token->document_path)) {
                    $autoCount++;
                    $documents[] = [
                        'id' => 'token_' . $token->id,
                        'name' => 'Email Verification Document',
                        'original_name' => 'Email Verification Document',
                        'url' => Storage::disk('public')->url($token->document_path),
                        'size' => $this->getFileSize($token->document_path),
                        'date' => Carbon::parse($token->verified_at)->format('M d, Y'),
                        'type' => 'employment_auto',
                        'collection' => 'employment_verification',
                        'company_name' => $token->employmentCompany->company_name ?? 'N/A'
                    ];
                }
            }
        }
        Log::info('Auto employment documents found', ['count' => $autoCount]);
        
        Log::info('Total employment documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get other/miscellaneous documents
     */
    private function getOtherDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting other documents', ['driver_id' => $driver->id]);
        
        // Get documents from 'other' collection
        $otherMedia = collect();
        $otherMedia = $otherMedia->merge($driver->getMedia('other'));
        $otherMedia = $otherMedia->merge($driver->getMedia('miscellaneous'));
        
        Log::info('Other media found', ['count' => $otherMedia->count()]);
        
        foreach ($otherMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'other',
                'collection' => $document->collection_name,
                'related_info' => 'Other Document'
            ];
        }
        
        Log::info('Total other documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get driving record documents
     */
    private function getDrivingRecordDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting driving record documents', ['driver_id' => $driver->id]);
        
        // Get documents from driving_records collection
        $drivingMedia = $driver->getMedia('driving_records');
        Log::info('Driving record media found', ['count' => $drivingMedia->count()]);
        
        foreach ($drivingMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'driving_record',
                'collection' => $document->collection_name,
                'related_info' => 'Driving Record'
            ];
        }
        
        Log::info('Total driving record documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get criminal record documents
     */
    private function getCriminalRecordDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting criminal record documents', ['driver_id' => $driver->id]);
        
        // Get documents from criminal_records collection
        $criminalMedia = $driver->getMedia('criminal_records');
        Log::info('Criminal record media found', ['count' => $criminalMedia->count()]);
        
        foreach ($criminalMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'criminal_record',
                'collection' => $document->collection_name,
                'related_info' => 'Criminal Record'
            ];
        }
        
        Log::info('Total criminal record documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get medical record documents
     */
    private function getMedicalRecordDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting medical record documents', ['driver_id' => $driver->id]);
        
        // Get documents from medical_records collection
        $medicalMedia = $driver->getMedia('medical_records');
        Log::info('Medical records media found', ['count' => $medicalMedia->count()]);
        
        foreach ($medicalMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'medical_record',
                'collection' => $document->collection_name,
                'related_info' => 'Medical Record'
            ];
        }
        
        Log::info('Total medical record documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get clearing house record documents
     */
    private function getClearingHouseRecordDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting clearing house record documents', ['driver_id' => $driver->id]);
        
        // Get documents from clearing_house_records collection
        $clearingHouseMedia = $driver->getMedia('clearing_house_records');
        Log::info('Clearing house records media found', ['count' => $clearingHouseMedia->count()]);
        
        foreach ($clearingHouseMedia as $document) {
            $originalName = $document->getCustomProperty('original_name') ?? $document->name;
            $documents[] = [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $originalName,
                'url' => $document->getUrl(),
                'size' => $this->formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'clearing_house_record',
                'collection' => $document->collection_name,
                'related_info' => 'Clearing House Record'
            ];
        }
        
        Log::info('Total clearing house record documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get vehicle verification documents
     */
    private function getVehicleVerificationDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting vehicle verification documents', ['driver_id' => $driver->id]);
        
        // Ensure relations are loaded
        $driver->load(['vehicles', 'vehicleAssignments', 'vehicleAssignments.vehicle']);
        
        // Get documents from direct vehicle_verifications collections
        $verificationCollections = ['vehicle_verifications', 'vehicle_verification', 'verifications', 'verification_documents'];
        foreach ($verificationCollections as $collection) {
            $vehicleMedia = $driver->getMedia($collection);
            Log::info('Vehicle verification media in collection', ['collection' => $collection, 'count' => $vehicleMedia->count()]);
            
            foreach ($vehicleMedia as $document) {
                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                $documents[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $originalName,
                    'url' => $document->getUrl(),
                    'size' => $this->formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'vehicle_verification',
                    'collection' => $document->collection_name,
                    'related_info' => 'Vehicle Verification (Direct)'
                ];
            }
        }
        
        // Check vehicles relation for vehicle documents
        if ($driver->vehicles) {
            Log::info('Vehicles relation found', ['count' => $driver->vehicles->count()]);
            foreach ($driver->vehicles as $vehicle) {
                // Check if vehicle has media capability before trying to get media
                if (method_exists($vehicle, 'getMedia')) {
                    // Get vehicle verification documents from various collections
                    $vehicleCollections = ['default', 'documents', 'files', 'verification', 'vehicle_verification', 'vehicle_documents'];
                    foreach ($vehicleCollections as $collection) {
                        $vehicleMedia = $vehicle->getMedia($collection);
                        if ($vehicleMedia->count() > 0) {
                            Log::info('Vehicle media found', ['vehicle_id' => $vehicle->id, 'collection' => $collection, 'count' => $vehicleMedia->count()]);
                            
                            foreach ($vehicleMedia as $document) {
                                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                                $documents[] = [
                                    'id' => $document->id,
                                    'name' => $document->name,
                                    'original_name' => $originalName,
                                    'url' => $document->getUrl(),
                                    'size' => $this->formatFileSize($document->size),
                                    'date' => $document->created_at->format('M d, Y'),
                                    'type' => 'vehicle_verification',
                                    'collection' => $document->collection_name,
                                    'related_info' => 'Vehicle: ' . ($vehicle->make ?? 'N/A') . ' ' . ($vehicle->model ?? 'N/A') . ' (' . ($vehicle->year ?? 'N/A') . ')'
                                ];
                            }
                        }
                    }
                } else {
                    Log::info('Vehicle does not have media capability', ['vehicle_id' => $vehicle->id, 'vehicle_class' => get_class($vehicle)]);
                }
            }
        }
        
        // Check vehicleAssignments relation for assignment documents
        if ($driver->vehicleAssignments) {
            Log::info('Vehicle assignments relation found', ['count' => $driver->vehicleAssignments->count()]);
            foreach ($driver->vehicleAssignments as $assignment) {
                // Check if assignment has media capability before trying to get media
                if (method_exists($assignment, 'getMedia')) {
                    // Get assignment verification documents from various collections
                    $assignmentCollections = ['default', 'documents', 'files', 'assignment', 'verification'];
                    foreach ($assignmentCollections as $collection) {
                        $assignmentMedia = $assignment->getMedia($collection);
                        if ($assignmentMedia->count() > 0) {
                            Log::info('Vehicle assignment media found', ['assignment_id' => $assignment->id, 'collection' => $collection, 'count' => $assignmentMedia->count()]);
                            
                            foreach ($assignmentMedia as $document) {
                                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                                $documents[] = [
                                    'id' => $document->id,
                                    'name' => $document->name,
                                    'original_name' => $originalName,
                                    'url' => $document->getUrl(),
                                    'size' => $this->formatFileSize($document->size),
                                    'date' => $document->created_at->format('M d, Y'),
                                    'type' => 'vehicle_verification',
                                    'collection' => $document->collection_name,
                                    'related_info' => 'Vehicle Assignment: ' . ($assignment->vehicle->make ?? 'N/A') . ' ' . ($assignment->vehicle->model ?? 'N/A')
                                ];
                            }
                        }
                    }
                } else {
                    Log::info('Vehicle assignment does not have media capability', ['assignment_id' => $assignment->id, 'assignment_class' => get_class($assignment)]);
                }
            }
        }
        
        // Check for VehicleVerificationToken documents if the class exists
        if (class_exists('App\Models\VehicleVerificationToken')) {
            $verificationTokens = \App\Models\VehicleVerificationToken::where('driver_id', $driver->id)->get();
            Log::info('Vehicle verification tokens found', ['count' => $verificationTokens->count()]);
            
            foreach ($verificationTokens as $token) {
                // Check if token has media capability before trying to get media
                if (method_exists($token, 'getMedia')) {
                    // Get token verification documents from various collections
                    $tokenCollections = ['default', 'documents', 'files', 'verification', 'consent', 'lease_agreement'];
                    foreach ($tokenCollections as $collection) {
                        $tokenMedia = $token->getMedia($collection);
                        if ($tokenMedia->count() > 0) {
                            Log::info('Verification token media found', ['token_id' => $token->id, 'collection' => $collection, 'count' => $tokenMedia->count()]);
                            
                            foreach ($tokenMedia as $document) {
                                $originalName = $document->getCustomProperty('original_name') ?? $document->name;
                                $documents[] = [
                                    'id' => $document->id,
                                    'name' => $document->name,
                                    'original_name' => $originalName,
                                    'url' => $document->getUrl(),
                                    'size' => $this->formatFileSize($document->size),
                                    'date' => $document->created_at->format('M d, Y'),
                                    'type' => 'vehicle_verification',
                                    'collection' => $document->collection_name,
                                    'related_info' => 'Verification Token: ' . ($token->verification_type ?? 'N/A')
                                ];
                            }
                        }
                    }
                } else {
                    Log::info('Verification token does not have media capability', ['token_id' => $token->id, 'token_class' => get_class($token)]);
                }
            }
        }
        
        Log::info('Total vehicle verification documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get individual application form documents
     */
    private function getIndividualApplicationDocuments(UserDriverDetail $driver)
    {
        $documents = [];
        
        Log::info('Getting individual application documents', ['driver_id' => $driver->id]);
        
        // Get documents from driver_applications directory
        $applicationPath = "driver/{$driver->id}/driver_applications";
        
        if (Storage::disk('public')->exists($applicationPath)) {
            $files = Storage::disk('public')->files($applicationPath);
            Log::info('Individual application files found', ['path' => $applicationPath, 'count' => count($files)]);
            
            foreach ($files as $file) {
                $fileName = basename($file);
                $fileSize = Storage::disk('public')->size($file);
                $lastModified = Storage::disk('public')->lastModified($file);
                
                $documents[] = [
                    'id' => 'app_' . md5($file),
                    'name' => $fileName,
                    'original_name' => $fileName, // For storage files, name is already readable
                    'url' => Storage::disk('public')->url($file),
                    'size' => $this->formatFileSize($fileSize),
                    'date' => Carbon::createFromTimestamp($lastModified)->format('M d, Y'),
                    'type' => 'individual_application',
                    'collection' => 'driver_applications',
                    'related_info' => 'Individual Application Form'
                ];
            }
        } else {
            Log::info('Individual application path does not exist', ['path' => $applicationPath]);
        }
        
        Log::info('Total individual application documents found', ['count' => count($documents)]);
        return $documents;
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats($documentsByCategory)
    {
        $stats = [
            'total_documents' => 0,
            'categories_with_documents' => 0,
            'recent_documents' => 0
        ];
        
        $recentDate = Carbon::now()->subDays(30);
        
        foreach ($documentsByCategory as $category => $documents) {
            $count = count($documents);
            $stats['total_documents'] += $count;
            
            if ($count > 0) {
                $stats['categories_with_documents']++;
            }
            
            // Count recent documents
            foreach ($documents as $document) {
                $docDate = Carbon::createFromFormat('M d, Y', $document['date']);
                if ($docDate->gte($recentDate)) {
                    $stats['recent_documents']++;
                }
            }
        }
        
        return $stats;
    }

    /**
     * Create ZIP file with all documents
     */
    private function createDocumentZip(UserDriverDetail $driver, $documentsByCategory)
    {
        Log::info('Starting ZIP creation for driver', [
            'driver_id' => $driver->id,
            'categories_count' => count($documentsByCategory)
        ]);

        $zip = new ZipArchive();
        $zipPath = storage_path('app/temp/driver_' . $driver->id . '_documents_' . time() . '.zip');
        
        // Ensure temp directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            Log::error('Failed to create ZIP file', ['zip_path' => $zipPath]);
            return false;
        }
        
        $hasDocuments = false;
        $totalDocuments = 0;
        $addedDocuments = 0;
        $failedDocuments = 0;
        
        foreach ($documentsByCategory as $categoryName => $documents) {
            if (empty($documents)) {
                Log::info('Skipping empty category', ['category' => $categoryName]);
                continue;
            }
            
            $categoryFolder = ucfirst(str_replace('_', ' ', $categoryName));
            $categoryCount = count($documents);
            $totalDocuments += $categoryCount;
            
            Log::info('Processing category', [
                'category' => $categoryName,
                'folder' => $categoryFolder,
                'documents_count' => $categoryCount
            ]);
            
            foreach ($documents as $document) {
                try {
                    $filePath = $this->getDocumentPath($document);
                    if ($filePath && file_exists($filePath)) {
                        // Use original name if available, otherwise use document name
                        $originalName = $document['original_name'] ?? $document['name'];
                        
                        // Clean the filename and ensure it has an extension
                        $cleanName = $this->cleanFileName($originalName, $document);
                        $fileName = $categoryFolder . '/' . $cleanName;
                        
                        $result = $zip->addFile($filePath, $fileName);
                        
                        if ($result) {
                            $hasDocuments = true;
                            $addedDocuments++;
                            Log::info('Document added to ZIP', [
                                'category' => $categoryName,
                                'original_name' => $originalName,
                                'clean_name' => $cleanName,
                                'document_name' => $document['name'],
                                'file_path' => $filePath,
                                'zip_path' => $fileName
                            ]);
                        } else {
                            $failedDocuments++;
                            Log::warning('Failed to add document to ZIP', [
                                'document_id' => $document['id'],
                                'document_name' => $document['name'],
                                'original_name' => $originalName,
                                'file_path' => $filePath
                            ]);
                        }
                    } else {
                        $failedDocuments++;
                        Log::warning('Document file not found', [
                            'document_id' => $document['id'],
                            'document_name' => $document['name'],
                            'original_name' => $document['original_name'] ?? 'N/A',
                            'file_path' => $filePath,
                            'file_exists' => $filePath ? file_exists($filePath) : false
                        ]);
                    }
                } catch (\Exception $e) {
                    $failedDocuments++;
                    Log::error('Exception while adding document to ZIP', [
                        'document_id' => $document['id'],
                        'document_name' => $document['name'] ?? 'unknown',
                        'original_name' => $document['original_name'] ?? 'N/A',
                        'category' => $categoryName,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }
        
        $zip->close();
        
        Log::info('ZIP creation completed', [
            'driver_id' => $driver->id,
            'zip_path' => $zipPath,
            'total_documents' => $totalDocuments,
            'added_documents' => $addedDocuments,
            'failed_documents' => $failedDocuments,
            'has_documents' => $hasDocuments
        ]);
        
        if (!$hasDocuments) {
            unlink($zipPath);
            Log::warning('No documents were added to ZIP, file deleted', ['zip_path' => $zipPath]);
            return false;
        }
        
        return $zipPath;
    }

    /**
     * Create ZIP file with selected documents
     */
    private function createSelectedDocumentsZip($documentIds)
    {
        $zip = new ZipArchive();
        $zipPath = storage_path('app/temp/selected_documents_' . time() . '.zip');
        
        // Ensure temp directory exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        $hasDocuments = false;
        
        foreach ($documentIds as $documentId) {
            try {
                // Handle token-based documents
                if (strpos($documentId, 'token_') === 0) {
                    $tokenId = str_replace('token_', '', $documentId);
                    if (class_exists(EmploymentVerificationToken::class)) {
                        $token = EmploymentVerificationToken::find($tokenId);
                        if ($token && Storage::disk('public')->exists($token->document_path)) {
                            $filePath = Storage::disk('public')->path($token->document_path);
                            $zip->addFile($filePath, 'employment_verification_' . $tokenId . '.pdf');
                            $hasDocuments = true;
                        }
                    }
                } elseif (strpos($documentId, 'app_') === 0) {
                    // Handle individual application documents
                    // We need to find the document by reconstructing the path
                    // This is a limitation - we'd need to pass more context to handle this properly
                    // For now, we'll skip these in selected downloads
                    continue;
                } else {
                    // Handle regular media documents
                    $media = Media::find($documentId);
                    if ($media && file_exists($media->getPath())) {
                        $zip->addFile($media->getPath(), $media->name);
                        $hasDocuments = true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not add selected document to ZIP', [
                    'document_id' => $documentId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $zip->close();
        
        if (!$hasDocuments) {
            unlink($zipPath);
            return false;
        }
        
        return $zipPath;
    }

    /**
     * Get document file path
     */
    private function getDocumentPath($document)
    {
        Log::info('Getting document path', [
            'document_id' => $document['id'] ?? 'unknown',
            'document_type' => $document['type'] ?? 'unknown',
            'document_name' => $document['name'] ?? 'unknown'
        ]);

        if (isset($document['type'])) {
            switch ($document['type']) {
                case 'employment_auto':
                    // Handle token-based documents
                    $tokenId = str_replace('token_', '', $document['id']);
                    if (class_exists(EmploymentVerificationToken::class)) {
                        $token = EmploymentVerificationToken::find($tokenId);
                        if ($token && Storage::disk('public')->exists($token->document_path)) {
                            $path = Storage::disk('public')->path($token->document_path);
                            Log::info('Employment auto document path', ['path' => $path, 'exists' => file_exists($path)]);
                            return file_exists($path) ? $path : null;
                        }
                    }
                    break;
                    
                case 'individual_application':
                    // Handle individual application documents from storage
                    $fileName = $document['name'];
                    $driverId = request()->route('driver')->id ?? null;
                    if ($driverId) {
                        $filePath = "driver/{$driverId}/driver_applications/{$fileName}";
                        if (Storage::disk('public')->exists($filePath)) {
                            $path = Storage::disk('public')->path($filePath);
                            Log::info('Individual application document path', ['path' => $path, 'exists' => file_exists($path)]);
                            return file_exists($path) ? $path : null;
                        }
                    }
                    break;
                    
                default:
                    // Handle regular media documents - get the actual file path from storage
                    $media = Media::find($document['id']);
                    if ($media) {
                        // Try to get the actual file path from the media library storage
                        $diskName = $media->disk;
                        $mediaPath = $media->getPathRelativeToRoot();
                        
                        // Get the full path using the storage disk
                        if ($diskName && Storage::disk($diskName)->exists($mediaPath)) {
                            $path = Storage::disk($diskName)->path($mediaPath);
                            Log::info('Regular media document path from storage', [
                                'media_id' => $media->id,
                                'disk' => $diskName,
                                'relative_path' => $mediaPath,
                                'full_path' => $path,
                                'exists' => file_exists($path)
                            ]);
                            return file_exists($path) ? $path : null;
                        }
                        
                        // Fallback to direct getPath method
                        $path = $media->getPath();
                        Log::info('Regular media document path fallback', [
                            'media_id' => $media->id,
                            'path' => $path,
                            'exists' => file_exists($path)
                        ]);
                        return file_exists($path) ? $path : null;
                    }
                    break;
            }
        } else {
            // Handle regular media documents (fallback) - get the actual file path from storage
            $media = Media::find($document['id']);
            if ($media) {
                // Try to get the actual file path from the media library storage
                $diskName = $media->disk;
                $mediaPath = $media->getPathRelativeToRoot();
                
                // Get the full path using the storage disk
                if ($diskName && Storage::disk($diskName)->exists($mediaPath)) {
                    $path = Storage::disk($diskName)->path($mediaPath);
                    Log::info('Fallback media document path from storage', [
                        'media_id' => $media->id,
                        'disk' => $diskName,
                        'relative_path' => $mediaPath,
                        'full_path' => $path,
                        'exists' => file_exists($path)
                    ]);
                    return file_exists($path) ? $path : null;
                }
                
                // Final fallback to direct getPath method
                $path = $media->getPath();
                Log::info('Final fallback media document path', [
                    'media_id' => $media->id,
                    'path' => $path,
                    'exists' => file_exists($path)
                ]);
                return file_exists($path) ? $path : null;
            }
        }
        
        Log::warning('Could not determine document path', ['document' => $document]);
        return null;
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get file size for storage files
     */
    private function getFileSize($path)
    {
        try {
            $size = Storage::disk('public')->size($path);
            return $this->formatFileSize($size);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Clean filename for ZIP archive
     */
    private function cleanFileName($originalName, $document)
    {
        $extension = '';
        $baseName = '';
        
        // First, try to get extension from original name if it's readable
        if ($originalName && !$this->isEncryptedFilename($originalName)) {
            $pathInfo = pathinfo($originalName);
            if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) {
                $extension = '.' . strtolower($pathInfo['extension']);
                $baseName = $pathInfo['filename'];
            } else {
                $baseName = $originalName;
            }
        }
        
        // If no extension found, try to get it from document name
        if (empty($extension) && isset($document['name'])) {
            $pathInfo = pathinfo($document['name']);
            if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) {
                $extension = '.' . strtolower($pathInfo['extension']);
                if (empty($baseName)) {
                    $baseName = $pathInfo['filename'];
                }
            }
        }
        
        // If still no extension, try to get it from Media model using mime_type
        if (empty($extension) && isset($document['id']) && is_numeric($document['id'])) {
            try {
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($document['id']);
                if ($media) {
                    // Try to get extension from file_name property
                    if ($media->file_name) {
                        $pathInfo = pathinfo($media->file_name);
                        if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) {
                            $extension = '.' . strtolower($pathInfo['extension']);
                        }
                    }
                    
                    // If still no extension, determine from mime_type
                    if (empty($extension) && $media->mime_type) {
                        $extension = $this->getExtensionFromMimeType($media->mime_type);
                    }
                    
                    // Use original file name if available and readable
                    if (empty($baseName) && $media->file_name && !$this->isEncryptedFilename($media->file_name)) {
                        $pathInfo = pathinfo($media->file_name);
                        $baseName = $pathInfo['filename'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Could not load Media model for extension detection', [
                    'document_id' => $document['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Special handling for specific document types
        if (isset($document['type'])) {
            switch ($document['type']) {
                case 'employment_auto':
                case 'employment_verification':
                    $extension = '.pdf';
                    break;
                case 'individual_application':
                    // Individual applications are usually PDFs
                    if (empty($extension)) {
                        $extension = '.pdf';
                    }
                    break;
            }
        }
        
        // Special handling for employment verification collection
        if (isset($document['collection']) && $document['collection'] === 'employment_verification') {
            $extension = '.pdf';
        }
        
        // If still no extension, provide fallback based on document type/collection
        if (empty($extension)) {
            $extension = $this->getFallbackExtension($document);
        }
        
        // Create base name if not already set
        if (empty($baseName)) {
            if (isset($document['collection'])) {
                $baseName = ucfirst(str_replace('_', ' ', $document['collection']));
            } elseif (isset($document['type'])) {
                $baseName = ucfirst(str_replace('_', ' ', $document['type']));
            } else {
                $baseName = 'Document';
            }
            
            // Add document ID to make it unique
            if (isset($document['id'])) {
                $baseName .= '_' . $document['id'];
            }
        }
        
        return $this->sanitizeFilename($baseName . $extension);
    }
    
    /**
     * Get file extension from mime type
     */
    private function getExtensionFromMimeType($mimeType)
    {
        $mimeToExtension = [
            // Images
            'image/jpeg' => '.jpg',
            'image/jpg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/bmp' => '.bmp',
            'image/webp' => '.webp',
            'image/svg+xml' => '.svg',
            
            // Documents
            'application/pdf' => '.pdf',
            'application/msword' => '.doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
            'application/vnd.ms-excel' => '.xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
            'application/vnd.ms-powerpoint' => '.ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
            
            // Text files
            'text/plain' => '.txt',
            'text/csv' => '.csv',
            'text/html' => '.html',
            'text/xml' => '.xml',
            
            // Archives
            'application/zip' => '.zip',
            'application/x-rar-compressed' => '.rar',
            'application/x-7z-compressed' => '.7z',
        ];
        
        return $mimeToExtension[strtolower($mimeType)] ?? '.pdf'; // Default to PDF
    }
    
    /**
     * Get fallback extension based on document type/collection
     */
    private function getFallbackExtension($document)
    {
        // License documents are usually images
        if (isset($document['collection'])) {
            $collection = strtolower($document['collection']);
            if (strpos($collection, 'license') !== false) {
                return '.jpg';
            }
            if (strpos($collection, 'photo') !== false || strpos($collection, 'image') !== false) {
                return '.jpg';
            }
        }
        
        if (isset($document['type'])) {
            $type = strtolower($document['type']);
            if (strpos($type, 'license') !== false) {
                return '.jpg';
            }
            if (strpos($type, 'photo') !== false || strpos($type, 'image') !== false) {
                return '.jpg';
            }
        }
        
        // Default to PDF for most documents
        return '.pdf';
    }

    /**
     * Check if filename appears to be encrypted/encoded
     */
    private function isEncryptedFilename($filename)
    {
        // Check for Laravel Media Library encrypted patterns
        if (strpos($filename, '-meta') !== false && strpos($filename, '==') !== false) {
            return true;
        }
        
        // Check for base64-like patterns
        if (preg_match('/^[A-Za-z0-9+\/]+=*$/', $filename)) {
            return true;
        }
        
        // Check for very long random-looking strings
        if (strlen($filename) > 50 && !preg_match('/\s/', $filename)) {
            return true;
        }
        
        return false;
    }

    /**
     * Sanitize filename for safe use in ZIP
     */
    private function sanitizeFilename($filename)
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[<>:"|?*]/', '_', $filename);
        $filename = preg_replace('/[\/\\\\]/', '_', $filename);
        
        // Limit length
        if (strlen($filename) > 200) {
            $pathInfo = pathinfo($filename);
            $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
            $baseName = substr($pathInfo['filename'], 0, 200 - strlen($extension));
            $filename = $baseName . $extension;
        }
        
        return $filename;
    }
}