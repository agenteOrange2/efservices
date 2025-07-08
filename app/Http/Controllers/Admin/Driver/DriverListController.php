<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DriverListController extends Controller
{
    /**
     * Display a listing of approved drivers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->input('search', '');
        $carrierFilter = $request->input('carrier', '');
        $perPage = $request->input('per_page', 10);
        $tab = $request->input('tab', 'all'); // Added tab parameter
        
        // Base query for all approved drivers
        $query = UserDriverDetail::with(['user', 'carrier', 'application'])
            ->whereHas('application', function($q) {
                $q->where('status', DriverApplication::STATUS_APPROVED);
            });
        
        // Apply tab filters
        switch ($tab) {
            case 'active':
                $query->where('status', UserDriverDetail::STATUS_ACTIVE);
                break;
            case 'inactive':
                $query->where('status', UserDriverDetail::STATUS_INACTIVE);
                break;
            case 'new':
                $query->whereDate('created_at', '>=', now()->subDays(30));
                break;
            // Default 'all' tab doesn't need additional filtering
        }
            
        $query->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Apply carrier filter if provided
        if (!empty($carrierFilter)) {
            $query->where('carrier_id', $carrierFilter);
        }

        // Get paginated results
        $drivers = $query->paginate($perPage);
        
        // Get all carriers for the filter dropdown
        $carriers = Carrier::orderBy('name')->get();

        // Calculate completion percentage for each driver
        foreach ($drivers as $driver) {
            $driver->completion_percentage = $this->calculateProfileCompleteness($driver);
        }
        
        // Get counts for tabs - using debug logging to verify counts
        $totalDriversCount = UserDriverDetail::whereHas('application', function($q) {
            $q->where('status', DriverApplication::STATUS_APPROVED);
        })->count();
        
        $activeDriversCount = UserDriverDetail::whereHas('application', function($q) {
            $q->where('status', DriverApplication::STATUS_APPROVED);
        })->where('status', UserDriverDetail::STATUS_ACTIVE)->count();
        
        // Corrige el conteo de conductores inactivos asegurándose que la condición sea exacta
        $inactiveQuery = UserDriverDetail::whereHas('application', function($q) {
            $q->where('status', DriverApplication::STATUS_APPROVED);
        })->where('status', UserDriverDetail::STATUS_INACTIVE);
        
        // Registra la consulta SQL para debug
        \Illuminate\Support\Facades\Log::debug('SQL inactivos: ' . $inactiveQuery->toSql());
        $inactiveDriversCount = $inactiveQuery->count();
        \Illuminate\Support\Facades\Log::debug('Contador de conductores inactivos: ' . $inactiveDriversCount);
        
        // Corrige el conteo de conductores nuevos asegurándose que la condición de fecha sea correcta
        $newQuery = UserDriverDetail::whereHas('application', function($q) {
            $q->where('status', DriverApplication::STATUS_APPROVED);
        })->whereDate('created_at', '>=', now()->subDays(30));
        
        // Registra la consulta SQL para debug
        \Illuminate\Support\Facades\Log::debug('SQL nuevos: ' . $newQuery->toSql());
        $newDriversCount = $newQuery->count();
        \Illuminate\Support\Facades\Log::debug('Contador de conductores nuevos: ' . $newDriversCount);

        return view('admin.drivers.list-driver.index', [
            'drivers' => $drivers,
            'carriers' => $carriers,
            'search' => $search,
            'carrierFilter' => $carrierFilter,
            'perPage' => $perPage,
            'currentTab' => $tab,
            'totalDriversCount' => $totalDriversCount,
            'activeDriversCount' => $activeDriversCount,
            'inactiveDriversCount' => $inactiveDriversCount,
            'newDriversCount' => $newDriversCount
        ]);
    }

    /**
     * Calculate profile completeness percentage for a driver
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return int
     */
    private function calculateProfileCompleteness(UserDriverDetail $driver)
    {
        $completedSteps = 0;
        $totalSteps = 6; // Total number of steps in driver registration

        // Check if basic info is complete
        if ($driver->user && $driver->user->email && $driver->phone) {
            $completedSteps++;
        }

        // Check if license info is complete
        if ($driver->licenses()->exists()) {
            $completedSteps++;
        }

        // Check if medical info is complete
        if ($driver->medicalQualification()->exists()) {
            $completedSteps++;
        }

        // Check if experience/training info is complete
        if ($driver->experiences()->exists() || $driver->trainingSchools()->exists()) {
            $completedSteps++;
        }

        // Check if employment history is complete
        if ($driver->employmentCompanies()->exists()) {
            $completedSteps++;
        }

        // Check if all documents are uploaded
        if ($driver->hasRequiredDocuments()) {
            $completedSteps++;
        }

        return round(($completedSteps / $totalSteps) * 100);
    }

    /**
     * Show the details for a specific driver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Format file size to human readable format
     *
     * @param int $size File size in bytes
     * @return string Formatted file size
     */
    protected function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }


    
    /**
     * Helper method to add a media item to a ZIP archive
     *
     * @param \ZipArchive $zip ZIP archive
     * @param mixed $mediaOrModel Media item or model with media
     * @param string $collectionOrFolder Collection name or folder name
     * @param string|null $zipPath Custom path in ZIP (optional)
     */
    protected function addMediaToZip($zip, $mediaOrModel, $collectionOrFolder, $zipPath = null)
    {
        // If it's a model with media collection
        if (method_exists($mediaOrModel, 'getMedia')) {
            $media = $mediaOrModel->getMedia($collectionOrFolder);
            foreach ($media as $index => $item) {
                $extension = $item->extension ?: pathinfo($item->file_name, PATHINFO_EXTENSION);
                $destination = $zipPath ? $zipPath . '_' . ($index + 1) . '.' . $extension : $collectionOrFolder . '/' . $item->file_name;
                $zip->addFile($item->getPath(), $destination);
            }
        }
        // If it's a single media item
        else if (method_exists($mediaOrModel, 'getFirstMedia')) {
            $media = $mediaOrModel->getFirstMedia($collectionOrFolder);
            if ($media) {
                $extension = $media->extension ?: pathinfo($media->file_name, PATHINFO_EXTENSION);
                $destination = $zipPath ? $zipPath . '.' . $extension : $collectionOrFolder . '/' . $media->file_name;
                $zip->addFile($media->getPath(), $destination);
            }
        }
    }

    /**
     * Show the details for a specific driver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $driver = UserDriverDetail::with([
            'user', 
            'carrier', 
            'application',
            'licenses',
            'medicalQualification',
            'experiences',
            'trainingSchools',
            'trafficConvictions',
            'accidents',
            'employmentCompanies',
            'employmentCompanies.company',
            'unemploymentPeriods',
            'relatedEmployments',
            'courses',
            // Cargar relaciones adicionales para los documentos y registros
            'application.addresses',
            'trainingSchools.media',
            'courses.media',
            'licenses.media',
            'medicalQualification.media',
            'accidents.media',
            'trafficConvictions.media',
            // Cargar pruebas e inspecciones
            'testings',
            'testings.media',
            'inspections',
            'inspections.vehicle',
            'inspections.media',
            ])->findOrFail($id);
        
        // Verificar si existen los records específicos
        $drivingRecord = $driver->getMedia('driving_records')->first();
        $medicalRecord = $driver->getMedia('medical_records')->first();
        $criminalRecord = $driver->getMedia('criminal_records')->first();
        $clearingHouseRecord = $driver->getMedia('clearing_house')->first();
        
        // Cargar documentos por categoría usando Spatie Media Library
        $documentsByCategory = [
            'license' => [],
            'medical' => [],
            'training_schools' => [],
            'courses' => [],
            'accidents' => [],
            'traffic' => [],
            'inspections' => [],
            'testing' => [],
            'records' => [],
            'certification' => [],
            'other' => []
        ];
        
        // Cargar documentos de licencias
        if ($driver->licenses) {
            foreach ($driver->licenses as $license) {
                // Documentos de licencia (license_documents)
                if ($license->getMedia('license_documents')->count() > 0) {
                    foreach ($license->getMedia('license_documents') as $media) {
                        $documentsByCategory['license'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'License ' . $license->license_number
                        ];
                    }
                }
                
                // Frente de licencia (license_front)
                if ($license->getMedia('license_front')->count() > 0) {
                    foreach ($license->getMedia('license_front') as $media) {
                        $documentsByCategory['license'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'License Front - ' . $license->license_number
                        ];
                    }
                }
                
                // Reverso de licencia (license_back)
                if ($license->getMedia('license_back')->count() > 0) {
                    foreach ($license->getMedia('license_back') as $media) {
                        $documentsByCategory['license'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'License Back - ' . $license->license_number
                        ];
                    }
                }
            }
        }
        
        // Cargar documentos médicos
        if ($driver->medicalQualification) {
            if ($driver->medicalQualification->getMedia('medical_card')->count() > 0) {
                foreach ($driver->medicalQualification->getMedia('medical_card') as $media) {
                    $documentsByCategory['medical'][] = [
                        'name' => $media->file_name,
                        'path' => $media->getPath(),
                        'url' => $media->getUrl(),
                        'size' => $this->formatFileSize($media->size),
                        'date' => $media->created_at->format('Y-m-d H:i:s'),
                        'media_id' => $media->id,
                        'related_info' => 'Medical Card'
                    ];
                }
            }
        }
        
        // Cargar documentos de medical records
        if ($driver->getMedia('medical_records')->count() > 0) {
            foreach ($driver->getMedia('medical_records') as $media) {
                $documentsByCategory['medical'][] = [
                    'name' => $media->file_name,
                    'path' => $media->getPath(),
                    'url' => $media->getUrl(),
                    'size' => $this->formatFileSize($media->size),
                    'date' => $media->created_at->format('Y-m-d H:i:s'),
                    'media_id' => $media->id,
                    'related_info' => 'Medical Record'
                ];
            }
        }
        
        // Cargar documentos de training schools
        if ($driver->trainingSchools) {
            foreach ($driver->trainingSchools as $school) {
                if ($school->getMedia('school_certificates')->count() > 0) {
                    foreach ($school->getMedia('school_certificates') as $media) {
                        $documentsByCategory['training_schools'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => $school->name ?? 'Training School Certificate'
                        ];
                    }
                }
            }
        }
        
        // Cargar documentos de courses
        if ($driver->courses) {
            foreach ($driver->courses as $course) {
                if ($course->getMedia('course_certificates')->count() > 0) {
                    foreach ($course->getMedia('course_certificates') as $media) {
                        $documentsByCategory['courses'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => $course->organization_name ?? 'Course Certificate'
                        ];
                    }
                }
            }
        }
        
        // Cargar documentos de accidents
        if ($driver->accidents) {
            foreach ($driver->accidents as $accident) {
                if ($accident->getMedia('accident-images')->count() > 0) {
                    foreach ($accident->getMedia('accident-images') as $media) {
                        $documentsByCategory['accidents'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'Accident on ' . ($accident->accident_date ? $accident->accident_date->format('Y-m-d') : 'N/A')
                        ];
                    }
                }
            }
        }
        
        // Cargar documentos de traffic convictions
        if ($driver->trafficConvictions) {
            foreach ($driver->trafficConvictions as $conviction) {
                // Intentar con traffic-documents
                if ($conviction->getMedia('traffic-tickets')->count() > 0) {
                    foreach ($conviction->getMedia('traffic-tickets') as $media) {
                        $documentsByCategory['traffic'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'Traffic Violation on ' . ($conviction->conviction_date ? $conviction->conviction_date->format('Y-m-d') : 'N/A')
                        ];
                    }
                }                                
            }
        }
        
        // Cargar documentos de inspecciones
        if ($driver->inspections) {
            foreach ($driver->inspections as $inspection) {                
                // Intentar con inspection_documents (sin guión)
                if ($inspection->getMedia('inspection_documents')->count() > 0) {
                    foreach ($inspection->getMedia('inspection_documents') as $media) {
                        $documentsByCategory['inspections'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'Inspection on ' . ($inspection->inspection_date ? $inspection->inspection_date->format('Y-m-d') : 'N/A') . 
                                     ($inspection->vehicle ? ' - ' . $inspection->vehicle->make . ' ' . $inspection->vehicle->model : '')
                        ];
                    }
                }                                
            }
        }
        
        // Cargar documentos de drug testing
        if ($driver->testings) {
            foreach ($driver->testings as $test) {
                // Documentos de drug test PDF
                if ($test->getMedia('drug_test_pdf')->count() > 0) {
                    foreach ($test->getMedia('drug_test_pdf') as $media) {
                        $documentsByCategory['testing'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'Drug Test Report on ' . ($test->test_date ? $test->test_date->format('Y-m-d') : 'N/A')
                        ];
                    }
                }
                
                // Documentos de resultados de pruebas
                if ($test->getMedia('test_results')->count() > 0) {
                    foreach ($test->getMedia('test_results') as $media) {
                        $documentsByCategory['testing'][] = [
                            'name' => $media->file_name,
                            'path' => $media->getPath(),
                            'url' => $media->getUrl(),
                            'size' => $this->formatFileSize($media->size),
                            'date' => $media->created_at->format('Y-m-d H:i:s'),
                            'media_id' => $media->id,
                            'related_info' => 'Test Results on ' . ($test->test_date ? $test->test_date->format('Y-m-d') : 'N/A')
                        ];
                    }
                }
            }
        }

        // Verificar si existen documentos de certificación generados por DriverCertificationStep
        $driverAppPath = 'driver/' . $driver->id . '/driver_applications';
        $hasApplicationForms = Storage::disk('public')->exists($driverAppPath);
        $applicationFormExists = false;
        $hasCertification = false; // Inicializar la variable para usarla en la vista
        
        // Verificar si el conductor tiene certificación con firma
        if ($driver->certification && $driver->certification->signature) {
            $hasCertification = true;
        }
        
        if ($hasApplicationForms) {
            // Buscar archivos PDF en el directorio de aplicaciones
            $applicationFiles = Storage::disk('public')->files($driverAppPath);
            
            foreach ($applicationFiles as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                    $fileName = pathinfo($file, PATHINFO_FILENAME);
                    $fileBaseName = pathinfo($file, PATHINFO_BASENAME);
                    $fileSize = Storage::disk('public')->size($file);
                    $fileDate = Storage::disk('public')->lastModified($file);
                    
                    // Formatear el nombre del archivo para mostrar
                    $displayName = str_replace('_', ' ', $fileName);
                    $displayName = ucwords($displayName);
                    
                    $documentsByCategory['certification'][] = [
                        'name' => $displayName . '.pdf',
                        'path' => $file,
                        'url' => asset('storage/' . $file),
                        'size' => $this->formatFileSize($fileSize),
                        'date' => date('Y-m-d H:i:s', $fileDate),
                        'related_info' => 'Generated Application Form'
                    ];
                    
                    $applicationFormExists = true;
                }
            }
        }
        
        // Guardar estado para la vista
        $data['applicationFormExists'] = $applicationFormExists;
        $data['hasCertification'] = $driver->certification && $driver->certification->signature ? true : false;
        
        // Cargar documentos de aplicación (certification/application forms)
        if ($driver->application) {
            // PDF de la aplicación
            if ($driver->application->getMedia('application_pdf')->count() > 0) {
                foreach ($driver->application->getMedia('application_pdf') as $media) {
                    $documentsByCategory['certification'][] = [
                        'name' => $media->file_name,
                        'path' => $media->getPath(),
                        'url' => $media->getUrl(),
                        'size' => $this->formatFileSize($media->size),
                        'date' => $media->created_at->format('Y-m-d H:i:s'),
                        'media_id' => $media->id,
                        'related_info' => 'Application PDF'
                    ];
                }
            }
            
            // Otros documentos de aplicación
            if ($driver->application->getMedia('application_documents')->count() > 0) {
                foreach ($driver->application->getMedia('application_documents') as $media) {
                    $documentsByCategory['certification'][] = [
                        'name' => $media->file_name,
                        'path' => $media->getPath(),
                        'url' => $media->getUrl(),
                        'size' => $this->formatFileSize($media->size),
                        'date' => $media->created_at->format('Y-m-d H:i:s'),
                        'media_id' => $media->id,
                        'related_info' => 'Application Document'
                    ];
                }
            }
        }
        
        // Agregar records específicos
        if ($drivingRecord) {
            $documentsByCategory['records'][] = [
                'name' => $drivingRecord->file_name,
                'url' => $drivingRecord->getUrl(),
                'size' => $this->formatFileSize($drivingRecord->size),
                'date' => $drivingRecord->created_at->format('Y-m-d H:i:s'),
                'related_info' => 'Driving Record'
            ];
        }
        if ($medicalRecord) {
            $documentsByCategory['records'][] = [
                'name' => $medicalRecord->file_name,
                'url' => $medicalRecord->getUrl(),
                'size' => $this->formatFileSize($medicalRecord->size),
                'date' => $medicalRecord->created_at->format('Y-m-d H:i:s'),
                'related_info' => 'Medical Record'
            ];
        }
        if ($criminalRecord) {
            $documentsByCategory['records'][] = [
                'name' => $criminalRecord->file_name,
                'url' => $criminalRecord->getUrl(),
                'size' => $this->formatFileSize($criminalRecord->size),
                'date' => $criminalRecord->created_at->format('Y-m-d H:i:s'),
                'related_info' => 'Criminal Record'
            ];
        }
        
        // Agregar Clearing House record
        $clearingHouse = $driver->getMedia('clearing_house')->first();
        if ($clearingHouse) {
            $documentsByCategory['records'][] = [
                'name' => $clearingHouse->file_name,
                'url' => $clearingHouse->getUrl(),
                'size' => $this->formatFileSize($clearingHouse->size),
                'date' => $clearingHouse->created_at->format('Y-m-d H:i:s'),
                'related_info' => 'Clearing House'
            ];
        }

        return view('admin.drivers.list-driver.driver-show', [
            'driver' => $driver,
            'drivingRecord' => $drivingRecord,
            'medicalRecord' => $medicalRecord,
            'criminalRecord' => $criminalRecord,
            'clearingHouseRecord' => $clearingHouseRecord,
            'documentsByCategory' => $documentsByCategory,
            'hasCertification' => $hasCertification,
            'applicationFormExists' => $applicationFormExists
        ]);
    }

    /**
     * Deactivate a driver.
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return \Illuminate\Http\Response
     */
    public function deactivate(UserDriverDetail $driver)
    {
        $driver->status = UserDriverDetail::STATUS_INACTIVE;
        $driver->save();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver has been deactivated.');
    }

    /**
     * Activate a driver.
     *
     * @param  \App\Models\UserDriverDetail  $driver
     * @return \Illuminate\Http\Response
     */
    public function activate(UserDriverDetail $driver)
    {
        $driver->status = UserDriverDetail::STATUS_ACTIVE;
        $driver->save();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver has been activated.');
    }

    /**
     * Download driver documents as ZIP.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadDocuments($id)
    {
        try {
            $driver = UserDriverDetail::findOrFail($id);
            $zipFileName = 'driver_' . $id . '_documents_' . date('Y-m-d') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                // Add license documents
                if ($driver->licenses && $driver->licenses->count() > 0) {
                    foreach ($driver->licenses as $license) {
                        $this->addMediaToZip($zip, $license, 'license_front', 'Licenses/License_' . $license->license_number . '_Front');
                        $this->addMediaToZip($zip, $license, 'license_back', 'Licenses/License_' . $license->license_number . '_Back');
                    }
                }
                
                // Add medical card
                if ($driver->medicalQualification) {
                    $this->addMediaToZip($zip, $driver->medicalQualification, 'medical_card', 'Medical/Medical_Card');
                }
                
                // Add training certificates
                foreach ($driver->trainingSchools as $school) {
                    $certificates = $school->getMedia('school_certificates');
                    foreach ($certificates as $index => $certificate) {
                        $localName = 'Training/' . $school->school_name . '/Certificate_' . ($index + 1) . '.' . $certificate->extension;
                        $zip->addFile($certificate->getPath(), $localName);
                    }
                }
                
                // Add application PDF
                if ($driver->application && $driver->application->hasMedia('application_pdf')) {
                    $applicationPdf = $driver->application->getFirstMedia('application_pdf');
                    $zip->addFile($applicationPdf->getPath(), 'Application/Complete_Application.pdf');
                }
                
                // Add lease agreement documents
                $basePath = storage_path('app/public/driver/' . $driver->id . '/vehicle_verifications/');
                $leaseAgreementThirdPartyPath = $basePath . 'lease_agreement_third_party.pdf';
                $leaseAgreementOwnerPath = $basePath . 'lease_agreement_owner_operator.pdf';
                
                if (file_exists($leaseAgreementThirdPartyPath)) {
                    $zip->addFile($leaseAgreementThirdPartyPath, 'Lease_Agreements/Third_Party_Lease_Agreement.pdf');
                }
                
                if (file_exists($leaseAgreementOwnerPath)) {
                    $zip->addFile($leaseAgreementOwnerPath, 'Lease_Agreements/Owner_Operator_Lease_Agreement.pdf');
                }
                
                $zip->close();
                
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
            
            return back()->with('error', 'Could not create ZIP file');
        } catch (\Exception $e) {
            Log::error('Error downloading documents', [
                'driver_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error downloading documents: ' . $e->getMessage());
        }
    }
    
    /**
     * Regenera los documentos de certificación para un conductor
     * 
     * @param int $id ID del conductor
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateApplicationForms($id)
    {
        try {
            $driver = UserDriverDetail::findOrFail($id);
            
            // Verificar si el conductor tiene certificación
            if (!$driver->certification || !$driver->certification->signature) {
                return back()->with('error', 'El conductor no tiene una certificación con firma para generar documentos.');
            }
            
            // Crear instancia del componente Livewire
            $certificationStep = new \App\Livewire\Admin\Driver\DriverCertificationStep();
            $certificationStep->mount($id);
            $certificationStep->signature = $driver->certification->signature;
            $certificationStep->certificationAccepted = true;
            
            // Simular el proceso de completar la certificación
            $certificationStep->complete();
            
            return back()->with('success', 'Los documentos de certificación han sido regenerados correctamente.');
            
        } catch (\Exception $e) {
            Log::error('Error regenerando documentos de certificación', [
                'driver_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error regenerando documentos: ' . $e->getMessage());
        }
    }
    

}