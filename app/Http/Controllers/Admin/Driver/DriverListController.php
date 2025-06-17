<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Carrier;
use Illuminate\Http\Request;

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

        // Base query for approved drivers
        $query = UserDriverDetail::with(['user', 'carrier', 'application'])
            ->whereHas('application', function($q) {
                $q->where('status', DriverApplication::STATUS_APPROVED);
            })
            ->orderBy('created_at', 'desc');

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

        return view('admin.drivers.list-driver.index', [
            'drivers' => $drivers,
            'carriers' => $carriers,
            'search' => $search,
            'carrierFilter' => $carrierFilter,
            'perPage' => $perPage
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
        // Caso 1: El segundo parámetro es un objeto Media
        if ($mediaOrModel instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            $media = $mediaOrModel;
            $folder = $collectionOrFolder; // En este caso, el tercer parámetro es el nombre de la carpeta
            
            $mediaPath = $media->getPath();
            if (file_exists($mediaPath)) {
                $destination = $zipPath ? $zipPath : $folder . '/' . $media->file_name;
                $zip->addFile($mediaPath, $destination);
            }
        }
        // Caso 2: El segundo parámetro es un modelo con media
        else if ($mediaOrModel->hasMedia($collectionOrFolder)) {
            $media = $mediaOrModel->getFirstMedia($collectionOrFolder);
            if ($media) {
                $extension = $media->extension ?: pathinfo($media->file_name, PATHINFO_EXTENSION);
                $destination = $zipPath ? $zipPath . '.' . $extension : $collectionOrFolder . '/' . $media->file_name;
                $zip->addFile($media->getPath(), $destination);
            }
        }
    }

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
        
        // Cargar documentos por categoría
        $driverPath = 'driver/' . $driver->id;
        $documentsByCategory = [
            'license' => [],
            'medical' => [],
            'training' => [],
            'courses' => [],
            'accidents' => [],
            'traffic' => [],
            'inspections' => [],
            'testing' => [],
            'records' => [],
            'certification' => [], // Añadir categoría para documentos de certificación
            'other' => []
        ];
        
        // Verificar y cargar documentos de cada categoría
        $categoryDirs = [
            'license' => $driverPath . '/licenses',
            'medical' => $driverPath . '/medical',
            'training' => $driverPath . '/training',
            'courses' => $driverPath . '/courses',
            'accidents' => $driverPath . '/accidents',
            'traffic' => $driverPath . '/traffic',
            'inspections' => $driverPath . '/inspections',
            'testing' => $driverPath . '/testing',
            'records' => $driverPath . '/records',
            'certification' => $driverPath . '/driver_applications', // Añadir directorio de documentos de certificación
            'other' => $driverPath . '/documents'
        ];
        
        foreach ($categoryDirs as $category => $dir) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($dir)) {
                $files = \Illuminate\Support\Facades\Storage::disk('public')->files($dir);
                
                foreach ($files as $file) {
                    $fileName = basename($file);
                    // Añadir información adicional para documentos de certificación
                    $docInfo = [
                        'name' => $fileName,
                        'path' => $file,
                        'url' => asset('storage/' . $file),
                        'size' => $this->formatFileSize(\Illuminate\Support\Facades\Storage::disk('public')->size($file)),
                        'date' => date('Y-m-d H:i:s', \Illuminate\Support\Facades\Storage::disk('public')->lastModified($file))
                    ];
                    
                    // Añadir información descriptiva para documentos de certificación
                    if ($category === 'certification') {
                        switch ($fileName) {
                            case 'general_information.pdf':
                                $docInfo['related_info'] = 'Información General';
                                break;
                            case 'address_information.pdf':
                                $docInfo['related_info'] = 'Información de Dirección';
                                break;
                            case 'application_details.pdf':
                                $docInfo['related_info'] = 'Detalles de Aplicación';
                                break;
                            case 'drivers_licenses.pdf':
                                $docInfo['related_info'] = 'Licencias de Conducir';
                                break;
                            case 'calificacion_medica.pdf':
                                $docInfo['related_info'] = 'Calificación Médica';
                                break;
                            case 'training_schools.pdf':
                                $docInfo['related_info'] = 'Escuelas de Entrenamiento';
                                break;
                            case 'traffic_violations.pdf':
                                $docInfo['related_info'] = 'Violaciones de Tráfico';
                                break;
                            case 'accident_record.pdf':
                                $docInfo['related_info'] = 'Registro de Accidentes';
                                break;
                            case 'fmcsr_requirements.pdf':
                                $docInfo['related_info'] = 'Requisitos FMCSR';
                                break;
                            case 'employment_history.pdf':
                                $docInfo['related_info'] = 'Historial de Empleo';
                                break;
                            case 'certification.pdf':
                                $docInfo['related_info'] = 'Certificación';
                                break;
                            case 'complete_application.pdf':
                            case 'solicitud.pdf':
                                $docInfo['related_info'] = 'Aplicación Completa';
                                break;
                            case 'lease_agreement.pdf':
                                $docInfo['related_info'] = 'Contrato de Arrendamiento';
                                break;
                        }
                    }
                    
                    $documentsByCategory[$category][] = $docInfo;
                }
            }
        }
        
        // Organizar los documentos de las relaciones
        $trainingSchoolDocuments = [];
        foreach ($driver->trainingSchools as $school) {
            if ($school->getDocuments('school_certificates') && $school->getDocuments('school_certificates')->count() > 0) {
                foreach ($school->getDocuments('school_certificates') as $certificate) {
                    $doc = [
                        'name' => $certificate->file_name,
                        'url' => $certificate->getUrl(),
                        'size' => $this->formatFileSize($certificate->size),
                        'date' => $certificate->created_at->format('Y-m-d H:i:s'),
                        'school' => $school->school_name
                    ];
                    $trainingSchoolDocuments[] = $doc;
                    $documentsByCategory['training'][] = $doc;
                }
            }
        }
        
        $courseDocuments = [];
        foreach ($driver->courses as $course) {
            if ($course->getMedia('course_certificates') && $course->getMedia('course_certificates')->count() > 0) {
                foreach ($course->getMedia('course_certificates') as $certificate) {
                    $doc = [
                        'name' => $certificate->file_name,
                        'url' => $certificate->getUrl(),
                        'size' => $this->formatFileSize($certificate->size),
                        'date' => $certificate->created_at->format('Y-m-d H:i:s'),
                        'course' => $course->course_name
                    ];
                    $courseDocuments[] = $doc;
                    $documentsByCategory['courses'][] = $doc;
                }
            }
        }
        
        $accidentDocuments = [];
        foreach ($driver->accidents as $accident) {
            if ($accident->getMedia('accident_report') && $accident->getMedia('accident_report')->count() > 0) {
                foreach ($accident->getMedia('accident_report') as $report) {
                    $doc = [
                        'name' => $report->file_name,
                        'url' => $report->getUrl(),
                        'size' => $this->formatFileSize($report->size),
                        'date' => $report->created_at->format('Y-m-d H:i:s'),
                        'accident_date' => $accident->accident_date->format('Y-m-d')
                    ];
                    $accidentDocuments[] = $doc;
                    $documentsByCategory['accidents'][] = $doc;
                }
            }
        }
        
        $trafficDocuments = [];
        foreach ($driver->trafficConvictions as $traffic) {
            if ($traffic->getMedia('traffic_report') && $traffic->getMedia('traffic_report')->count() > 0) {
                foreach ($traffic->getMedia('traffic_report') as $report) {
                    $doc = [
                        'name' => $report->file_name,
                        'url' => $report->getUrl(),
                        'size' => $this->formatFileSize($report->size),
                        'date' => $report->created_at->format('Y-m-d H:i:s'),
                        'conviction_date' => $traffic->conviction_date->format('Y-m-d')
                    ];
                    $trafficDocuments[] = $doc;
                    $documentsByCategory['traffic'][] = $doc;
                }
            }
        }
        
        $inspectionDocuments = [];
        foreach ($driver->inspections as $inspection) {
            if ($inspection->getMedia('inspection_report') && $inspection->getMedia('inspection_report')->count() > 0) {
                foreach ($inspection->getMedia('inspection_report') as $report) {
                    $doc = [
                        'name' => $report->file_name,
                        'url' => $report->getUrl(),
                        'size' => $this->formatFileSize($report->size),
                        'date' => $report->created_at->format('Y-m-d H:i:s'),
                        'inspection_date' => $inspection->inspection_date->format('Y-m-d'),
                        'vehicle' => $inspection->vehicle ? $inspection->vehicle->make . ' ' . $inspection->vehicle->model : 'N/A'
                    ];
                    $inspectionDocuments[] = $doc;
                    $documentsByCategory['inspections'][] = $doc;
                }
            }
        }
        
        $testingDocuments = [];
        foreach ($driver->testings as $testing) {
            // Drug test PDF
            if ($testing->getMedia('drug_test_pdf') && $testing->getMedia('drug_test_pdf')->count() > 0) {
                foreach ($testing->getMedia('drug_test_pdf') as $pdf) {
                    $doc = [
                        'name' => $pdf->file_name,
                        'url' => $pdf->getUrl(),
                        'size' => $this->formatFileSize($pdf->size),
                        'date' => $pdf->created_at->format('Y-m-d H:i:s'),
                        'test_date' => $testing->test_date ? $testing->test_date->format('Y-m-d') : 'N/A',
                        'type' => 'Drug Test PDF'
                    ];
                    $testingDocuments[] = $doc;
                    $documentsByCategory['testing'][] = $doc;
                }
            }
            
            // Test results
            if ($testing->getMedia('test_results') && $testing->getMedia('test_results')->count() > 0) {
                foreach ($testing->getMedia('test_results') as $result) {
                    $doc = [
                        'name' => $result->file_name,
                        'url' => $result->getUrl(),
                        'size' => $this->formatFileSize($result->size),
                        'date' => $result->created_at->format('Y-m-d H:i:s'),
                        'test_date' => $testing->test_date ? $testing->test_date->format('Y-m-d') : 'N/A',
                        'type' => 'Test Results'
                    ];
                    $testingDocuments[] = $doc;
                    $documentsByCategory['testing'][] = $doc;
                }
            }
        }

        // Agregar documentos de licencias
        foreach ($driver->licenses as $license) {
            if ($license->getMedia('license_front') && $license->getMedia('license_front')->count() > 0) {
                foreach ($license->getMedia('license_front') as $doc) {
                    $documentsByCategory['license'][] = [
                        'name' => $doc->file_name,
                        'url' => $doc->getUrl(),
                        'size' => $this->formatFileSize($doc->size),
                        'date' => $doc->created_at->format('Y-m-d H:i:s'),
                        'type' => 'License Front'
                    ];
                }
            }
            if ($license->getMedia('license_back') && $license->getMedia('license_back')->count() > 0) {
                foreach ($license->getMedia('license_back') as $doc) {
                    $documentsByCategory['license'][] = [
                        'name' => $doc->file_name,
                        'url' => $doc->getUrl(),
                        'size' => $this->formatFileSize($doc->size),
                        'date' => $doc->created_at->format('Y-m-d H:i:s'),
                        'type' => 'License Back'
                    ];
                }
            }
        }

        // Agregar documentos médicos
        if ($driver->medicalQualification) {
            if ($driver->medicalQualification->getMedia('medical_card') && $driver->medicalQualification->getMedia('medical_card')->count() > 0) {
                foreach ($driver->medicalQualification->getMedia('medical_card') as $doc) {
                    $documentsByCategory['medical'][] = [
                        'name' => $doc->file_name,
                        'url' => $doc->getUrl(),
                        'size' => $this->formatFileSize($doc->size),
                        'date' => $doc->created_at->format('Y-m-d H:i:s'),
                        'type' => 'Medical Card'
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
                'type' => 'Driving Record'
            ];
        }
        if ($medicalRecord) {
            $documentsByCategory['records'][] = [
                'name' => $medicalRecord->file_name,
                'url' => $medicalRecord->getUrl(),
                'size' => $this->formatFileSize($medicalRecord->size),
                'date' => $medicalRecord->created_at->format('Y-m-d H:i:s'),
                'type' => 'Medical Record'
            ];
        }
        if ($criminalRecord) {
            $documentsByCategory['records'][] = [
                'name' => $criminalRecord->file_name,
                'url' => $criminalRecord->getUrl(),
                'size' => $this->formatFileSize($criminalRecord->size),
                'date' => $criminalRecord->created_at->format('Y-m-d H:i:s'),
                'type' => 'Criminal Record'
            ];
        }

        return view('admin.drivers.list-driver.driver-show', [
            'driver' => $driver,
            'drivingRecord' => $drivingRecord,
            'medicalRecord' => $medicalRecord,
            'criminalRecord' => $criminalRecord,
            'documentsByCategory' => $documentsByCategory,
            'trainingSchoolDocuments' => $trainingSchoolDocuments,
            'courseDocuments' => $courseDocuments,
            'accidentDocuments' => $accidentDocuments,
            'trafficDocuments' => $trafficDocuments,
            'inspectionDocuments' => $inspectionDocuments,
            'testingDocuments' => $testingDocuments
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
    }
    

}