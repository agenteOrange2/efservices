@php
    use App\Helpers\ImageCompressionHelper;
    
    // Inicializar arrays para documentos y estadísticas
    $documentsByCategory = [];
    $totalDocuments = 0;
    $totalCategories = 0;
    $recentDocuments = 0;
    
    // 1. LICENCIAS - Obtener documentos de licencias
    $licenseDocuments = [];
    foreach ($driver->licenses as $license) {
        // Obtener documentos de license_front
        $frontMedia = $license->getMedia('license_front');
        foreach ($frontMedia as $document) {
            $licenseDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'license_front',
                'collection' => $document->collection_name,
                'related_info' => 'License Front - ' . $license->license_number
            ];
        }
        
        // Obtener documentos de license_back
        $backMedia = $license->getMedia('license_back');
        foreach ($backMedia as $document) {
            $licenseDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'license_back',
                'collection' => $document->collection_name,
                'related_info' => 'License Back - ' . $license->license_number
            ];
        }
        
        // Obtener otros documentos de licencia
        $licenseDocsMedia = $license->getMedia('license_documents');
        foreach ($licenseDocsMedia as $document) {
            $licenseDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'license_document',
                'collection' => $document->collection_name,
                'related_info' => 'License Document - ' . $license->license_number
            ];
        }
    }
    if (!empty($licenseDocuments)) {
        $documentsByCategory['licenses'] = $licenseDocuments;
    }
    
    // 2. DOCUMENTOS MÉDICOS
    $medicalDocuments = [];
    if ($driver->medicalQualification) {
        // Obtener documentos de todas las colecciones médicas
        $medicalCollections = ['medical_certificate', 'test_results', 'additional_documents', 'medical_documents', 'medical_card'];
        
        foreach ($medicalCollections as $collection) {
            $medicalMedia = $driver->medicalQualification->getMedia($collection);
            foreach ($medicalMedia as $document) {
                $medicalDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'medical',
                    'collection' => $document->collection_name,
                    'related_info' => 'Medical Qualification - ' . ucfirst(str_replace('_', ' ', $collection))
                ];
            }
        }
    }
    if (!empty($medicalDocuments)) {
        $documentsByCategory['medical'] = $medicalDocuments;
    }
    
    // 3. ESCUELAS DE ENTRENAMIENTO
    $trainingSchoolDocuments = [];
    foreach ($driver->trainingSchools as $school) {
        // Obtener documentos de la colección específica de certificados de escuela
        $media = $school->getMedia('school_certificates');
        foreach ($media as $document) {
            $trainingSchoolDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'training_school',
                'collection' => $document->collection_name,
                'related_info' => $school->school_name ?? 'Training School'
            ];
        }
    }
    if (!empty($trainingSchoolDocuments)) {
        $documentsByCategory['training_schools'] = $trainingSchoolDocuments;
    }
    
    // 4. CURSOS
    $courseDocuments = [];
    foreach ($driver->courses as $course) {
        // Obtener documentos de la colección específica de certificados de cursos
        $media = $course->getMedia('course_certificates');
        foreach ($media as $document) {
            $courseDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'course',
                'collection' => $document->collection_name,
                'related_info' => $course->organization_name ?? 'Course'
            ];
        }
    }
    if (!empty($courseDocuments)) {
        $documentsByCategory['courses'] = $courseDocuments;
    }
    
    // 5. ACCIDENTES
    $accidentDocuments = [];
    foreach ($driver->accidents as $accident) {
        // Obtener documentos de la colección específica de imágenes de accidentes
        $media = $accident->getMedia('accident-images');
        foreach ($media as $document) {
            $accidentDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'accident',
                'collection' => $document->collection_name,
                'related_info' => $accident->accident_date ? \Carbon\Carbon::parse($accident->accident_date)->format('M d, Y') : 'Accident'
            ];
        }
    }
    if (!empty($accidentDocuments)) {
        $documentsByCategory['accidents'] = $accidentDocuments;
    }
    
    // 6. VIOLACIONES DE TRÁFICO
    $trafficViolationDocuments = [];
    foreach ($driver->trafficConvictions as $conviction) {
        // Obtener documentos de la colección específica de imágenes de tráfico
        $media = $conviction->getMedia('traffic_images');
        foreach ($media as $document) {
            $trafficViolationDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'traffic_violation',
                'collection' => $document->collection_name,
                'related_info' => $conviction->conviction_date ? \Carbon\Carbon::parse($conviction->conviction_date)->format('M d, Y') : 'Traffic Violation'
            ];
        }
    }
    if (!empty($trafficViolationDocuments)) {
        $documentsByCategory['traffic_violations'] = $trafficViolationDocuments;
    }
    
    // 7. INSPECCIONES
    $inspectionDocuments = [];
    foreach ($driver->inspections as $inspection) {
        $media = $inspection->getMedia();
        foreach ($media as $document) {
            $inspectionDocuments[] = [
                'id' => $document->id,
                'name' => $document->name,
                'url' => $document->getUrl(),
                'size' => ImageCompressionHelper::formatFileSize($document->size),
                'date' => $document->created_at->format('M d, Y'),
                'type' => 'inspection',
                'collection' => $document->collection_name,
                'related_info' => $inspection->inspection_date ? \Carbon\Carbon::parse($inspection->inspection_date)->format('M d, Y') : 'Inspection'
            ];
        }
    }
    if (!empty($inspectionDocuments)) {
        $documentsByCategory['inspections'] = $inspectionDocuments;
    }
    
    // 8. TESTING
    $testingDocuments = [];
    if ($driver->testings) {
        foreach ($driver->testings as $testing) {
            // Obtener documentos de drug test
            $drugTestMedia = $testing->getMedia('drug_test_pdf');
            foreach ($drugTestMedia as $document) {
                $testingDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'testing',
                    'collection' => $document->collection_name,
                    'related_info' => 'Drug Test - ' . ($testing->test_date ? $testing->test_date->format('M d, Y') : 'N/A')
                ];
            }
            
            // Obtener resultados de test
            $testResultsMedia = $testing->getMedia('test_results');
            foreach ($testResultsMedia as $document) {
                $testingDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'testing',
                    'collection' => $document->collection_name,
                    'related_info' => 'Test Results - ' . ($testing->test_date ? $testing->test_date->format('M d, Y') : 'N/A')
                ];
            }
            
            // Obtener certificados de test
            $testCertificatesMedia = $testing->getMedia('test_certificates');
            foreach ($testCertificatesMedia as $document) {
                $testingDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'testing',
                    'collection' => $document->collection_name,
                    'related_info' => 'Test Certificate - ' . ($testing->test_date ? $testing->test_date->format('M d, Y') : 'N/A')
                ];
            }
        }
    }
    if (!empty($testingDocuments)) {
        $documentsByCategory['testing'] = $testingDocuments;
    }
    
    // 9. INSPECTIONS
    $inspectionDocuments = [];
    if ($driver->inspections) {
        foreach ($driver->inspections as $inspection) {
            $inspectionMedia = $inspection->getMedia('inspection_documents');
            foreach ($inspectionMedia as $document) {
                $inspectionDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'inspections',
                    'collection' => $document->collection_name,
                    'related_info' => 'Inspection - ' . ($inspection->inspection_date ? $inspection->inspection_date->format('M d, Y') : 'N/A') . ' (' . ucfirst($inspection->inspection_type ?? 'N/A') . ')'
                ];
            }
        }
    }
    if (!empty($inspectionDocuments)) {
        $documentsByCategory['inspections'] = $inspectionDocuments;
    }
    
    // 10. VERIFICACIONES DE VEHÍCULOS
    $vehicleVerificationDocuments = [];
    $vehicleVerificationsPath = "driver/{$driver->id}/vehicle_verifications";
    
    if (\Storage::disk('public')->exists($vehicleVerificationsPath)) {
        $vehicleFiles = \Storage::disk('public')->files($vehicleVerificationsPath);
        
        foreach ($vehicleFiles as $filePath) {
            $fileName = basename($filePath);
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            
            // Solo procesar archivos PDF
            if (strtolower($fileExtension) === 'pdf') {
                $fileSize = \Storage::disk('public')->size($filePath);
                $fileModified = \Storage::disk('public')->lastModified($filePath);
                
                // Determinar el tipo de documento basado en el nombre del archivo
                $relatedInfo = 'Vehicle Verification Document';
                if (strpos(strtolower($fileName), 'inspection') !== false) {
                    $relatedInfo = 'Vehicle Inspection Report';
                } elseif (strpos(strtolower($fileName), 'verification') !== false) {
                    $relatedInfo = 'Vehicle Verification Report';
                } elseif (strpos(strtolower($fileName), 'certificate') !== false) {
                    $relatedInfo = 'Vehicle Certificate';
                }
                
                $vehicleVerificationDocuments[] = [
                    'id' => 'vehicle_' . md5($filePath),
                    'name' => $fileName,
                    'url' => \Storage::disk('public')->url($filePath),
                    'size' => ImageCompressionHelper::formatFileSize($fileSize),
                    'date' => \Carbon\Carbon::createFromTimestamp($fileModified)->format('M d, Y'),
                    'type' => 'vehicle_verification',
                    'collection' => 'vehicle_verifications',
                    'related_info' => $relatedInfo
                ];
            }
        }
    }
    
    if (!empty($vehicleVerificationDocuments)) {
        $documentsByCategory['vehicle_verifications'] = $vehicleVerificationDocuments;
    }
    
    // 11. FORMULARIOS DE APLICACIÓN
    $applicationDocuments = [];
    if ($driver->application) {
        // Application PDF
        if ($driver->application->hasMedia('application_pdf')) {
            $media = $driver->application->getMedia('application_pdf');
            foreach ($media as $document) {
                $applicationDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'application_pdf',
                    'collection' => $document->collection_name,
                    'related_info' => 'Complete Application PDF'
                ];
            }
        }
        
        // Signed Application
        if ($driver->application->hasMedia('signed_application')) {
            $media = $driver->application->getMedia('signed_application');
            foreach ($media as $document) {
                $applicationDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'signed_application',
                    'collection' => $document->collection_name,
                    'related_info' => 'Signed Application'
                ];
            }
        }
    }
    
    // Documentos individuales del driver (formularios adicionales)
    $individualApplicationMedia = collect();
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('signed_application'));
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('application_pdf'));
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('lease_agreement'));
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('contract_documents'));
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('application_forms'));
    $individualApplicationMedia = $individualApplicationMedia->merge($driver->getMedia('individual_forms'));
    
    foreach ($individualApplicationMedia as $document) {
        $relatedInfo = 'Individual Application Form';
        switch ($document->collection_name) {
            case 'signed_application':
                $relatedInfo = 'Signed Application Form';
                break;
            case 'application_pdf':
                $relatedInfo = 'Application PDF Form';
                break;
            case 'lease_agreement':
                $relatedInfo = 'Lease Agreement';
                break;
            case 'contract_documents':
                $relatedInfo = 'Contract Document';
                break;
            case 'application_forms':
                $relatedInfo = 'Application Form';
                break;
            case 'individual_forms':
                $relatedInfo = 'Individual Form';
                break;
        }
        
        $applicationDocuments[] = [
            'id' => $document->id,
            'name' => $document->name,
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'individual_application',
            'collection' => $document->collection_name,
            'related_info' => $relatedInfo
        ];
    }
    
    // Escanear archivos PDF individuales en storage/app/public/driver/{id}/driver_applications/
    $driverApplicationsPath = "driver/{$driver->id}/driver_applications";
    if (\Storage::disk('public')->exists($driverApplicationsPath)) {
        $individualFiles = \Storage::disk('public')->files($driverApplicationsPath);
        
        foreach ($individualFiles as $filePath) {
            $fileName = basename($filePath);
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            
            // Solo procesar archivos PDF
            if (strtolower($fileExtension) === 'pdf') {
                $fileSize = \Storage::disk('public')->size($filePath);
                $fileModified = \Storage::disk('public')->lastModified($filePath);
                
                // Determinar el tipo de documento basado en el nombre del archivo
                $relatedInfo = 'Individual Application PDF';
                if (strpos(strtolower($fileName), 'criminal') !== false) {
                    $relatedInfo = 'Criminal History Investigation';
                } elseif (strpos(strtolower($fileName), 'complete_application') !== false) {
                    $relatedInfo = 'Complete Application Form';
                } elseif (strpos(strtolower($fileName), 'application') !== false) {
                    $relatedInfo = 'Application Form PDF';
                } elseif (strpos(strtolower($fileName), 'certification') !== false) {
                    $relatedInfo = 'Certification Document';
                }
                
                $applicationDocuments[] = [
                    'id' => 'file_' . md5($filePath),
                    'name' => $fileName,
                    'url' => \Storage::disk('public')->url($filePath),
                    'size' => ImageCompressionHelper::formatFileSize($fileSize),
                    'date' => \Carbon\Carbon::createFromTimestamp($fileModified)->format('M d, Y'),
                    'type' => 'individual_pdf',
                    'collection' => 'driver_applications',
                    'related_info' => $relatedInfo
                ];
            }
        }
    }
    
    if (!empty($applicationDocuments)) {
        $documentsByCategory['application_forms'] = $applicationDocuments;
    }
    
    // 11. VERIFICACIÓN DE EMPLEO
    $employmentVerificationDocuments = [];
    if ($driver->employmentCompanies && $driver->employmentCompanies->count() > 0) {
        foreach ($driver->employmentCompanies as $empCompany) {
            // Documentos manuales
            $manualDocs = $empCompany->getMedia('employment_verification_documents');
            foreach ($manualDocs as $document) {
                $companyName = $empCompany->company_name ?: 
                    ($empCompany->masterCompany ? $empCompany->masterCompany->name : 'Company ' . $empCompany->id);
                $employmentVerificationDocuments[] = [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => $document->getUrl(),
                    'size' => ImageCompressionHelper::formatFileSize($document->size),
                    'date' => $document->created_at->format('M d, Y'),
                    'type' => 'employment_verification',
                    'collection' => $document->collection_name,
                    'related_info' => 'Manual Upload',
                    'company_name' => $companyName
                ];
            }
            
            // Documentos automáticos por verificación por correo
            $tokens = \App\Models\Admin\Driver\EmploymentVerificationToken::where('employment_company_id', $empCompany->id)
                ->whereNotNull('verified_at')
                ->where('document_path', '!=', null)
                ->get();
                
            foreach ($tokens as $token) {
                if (\Storage::disk('public')->exists($token->document_path)) {
                    $companyName = $empCompany->company_name ?: 
                        ($empCompany->masterCompany ? $empCompany->masterCompany->name : 'Company ' . $empCompany->id);
                    $employmentVerificationDocuments[] = [
                        'id' => 'token_' . $token->id,
                        'name' => 'Verification Document',
                        'url' => \Storage::disk('public')->url($token->document_path),
                        'size' => 'N/A',
                        'date' => \Carbon\Carbon::parse($token->verified_at)->format('M d, Y'),
                        'type' => 'employment_verification_email',
                        'collection' => 'email_verified',
                        'related_info' => 'Email Verified',
                        'company_name' => $companyName
                    ];
                }
            }
        }
    }
    if (!empty($employmentVerificationDocuments)) {
        $documentsByCategory['employment_verification'] = $employmentVerificationDocuments;
    }
    
    // 12. REGISTROS ESPECÍFICOS (Driving, Criminal, Medical, Clearing House)
    $recordsDocuments = [];
    
    // Driving Record
    $drivingRecordMedia = $driver->getMedia('driving_records');
    foreach ($drivingRecordMedia as $document) {
        $recordsDocuments[] = [
            'id' => $document->id,
            'name' => 'driving_record.pdf',
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'driving_record',
            'collection' => $document->collection_name,
            'related_info' => 'Driving Record'
        ];
    }
    
    // Criminal Record
    $criminalRecordMedia = $driver->getMedia('criminal_records');
    foreach ($criminalRecordMedia as $document) {
        $recordsDocuments[] = [
            'id' => $document->id,
            'name' => 'criminal_record.pdf',
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'criminal_record',
            'collection' => $document->collection_name,
            'related_info' => 'Criminal Record'
        ];
    }
    
    // Medical Record
    $medicalRecordMedia = $driver->getMedia('medical_records');
    foreach ($medicalRecordMedia as $document) {
        $recordsDocuments[] = [
            'id' => $document->id,
            'name' => 'medical_record.pdf',
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'medical_record',
            'collection' => $document->collection_name,
            'related_info' => 'Medical Record'
        ];
    }
    
    // Clearing House
    $clearingHouseMedia = $driver->getMedia('clearing_house');
    foreach ($clearingHouseMedia as $document) {
        $recordsDocuments[] = [
            'id' => $document->id,
            'name' => 'clearing_house.pdf',
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'clearing_house',
            'collection' => $document->collection_name,
            'related_info' => 'Clearing House Record'
        ];
    }
    
    // Documentos generales adicionales (si existen)
    $generalRecordsMedia = collect();
    $generalRecordsMedia = $generalRecordsMedia->merge($driver->getMedia('records'));
    $generalRecordsMedia = $generalRecordsMedia->merge($driver->getMedia('general'));
    $generalRecordsMedia = $generalRecordsMedia->merge($driver->getMedia('documents'));
    
    foreach ($generalRecordsMedia as $document) {
        $recordsDocuments[] = [
            'id' => $document->id,
            'name' => $document->name,
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'general_record',
            'collection' => $document->collection_name,
            'related_info' => 'General Record'
        ];
    }
    
    if (!empty($recordsDocuments)) {
        $documentsByCategory['records'] = $recordsDocuments;
    }

    
    // 12. OTROS DOCUMENTOS
    $otherMedia = collect();
    $otherMedia = $otherMedia->merge($driver->getMedia('other'));
    $otherMedia = $otherMedia->merge($driver->getMedia('miscellaneous'));
    
    $otherDocuments = [];
    foreach ($otherMedia as $document) {
        $otherDocuments[] = [
            'id' => $document->id,
            'name' => $document->name,
            'url' => $document->getUrl(),
            'size' => ImageCompressionHelper::formatFileSize($document->size),
            'date' => $document->created_at->format('M d, Y'),
            'type' => 'other',
            'collection' => $document->collection_name,
            'related_info' => 'Other Document'
        ];
    }
    if (!empty($otherDocuments)) {
        $documentsByCategory['other'] = $otherDocuments;
    }
    
    // Calcular estadísticas
    foreach ($documentsByCategory as $category => $documents) {
        $totalDocuments += count($documents);
        $totalCategories++;
        
        // Contar documentos recientes (últimos 30 días)
        foreach ($documents as $document) {
            $documentDate = \Carbon\Carbon::createFromFormat('M d, Y', $document['date']);
            if ($documentDate->diffInDays(now()) <= 30) {
                $recentDocuments++;
            }
        }
    }
    
    $documentStats = [
        'total_documents' => $totalDocuments,
        'categories_with_documents' => $totalCategories,
        'recent_documents' => $recentDocuments
    ];
    
    // Etiquetas de categorías
    $categoryLabels = [
        'licenses' => 'Licenses',
        'medical' => 'Medical Documents',
        'training_schools' => 'Training Schools',
        'courses' => 'Courses',
        'accidents' => 'Accidents',
        'traffic_violations' => 'Traffic Violations',
        'testing' => 'Testing',
        'inspections' => 'Inspections',
        'vehicle_verifications' => 'Vehicle Verifications',
        'records' => 'Records',
        'employment_verification' => 'Employment Verification',
        'application_forms' => 'Application Forms',
        'other' => 'Other Documents'
    ];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header with Statistics -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Driver Documents</h3>
            <div class="flex space-x-2">
                @if($documentStats['total_documents'] > 0)
                    <form action="{{ route('admin.drivers.documents.download-all', $driver->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download All
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Documents</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $documentStats['total_documents'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Categories</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $documentStats['categories_with_documents'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Recent (30 days)</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $documentStats['recent_documents'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Content -->
    <div class="p-6">
        @if($documentStats['total_documents'] == 0)
            <!-- No Documents State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
                <p class="mt-1 text-sm text-gray-500">No documents have been uploaded for this driver yet.</p>
            </div>
        @else
            <!-- Documents by Category -->
            @foreach($documentsByCategory as $categoryKey => $documents)
                @if(count($documents) > 0)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-3">
                                    {{ count($documents) }}
                                </span>
                                {{ $categoryLabels[$categoryKey] }}
                            </h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($documents as $document)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $document['name'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ $document['size'] }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-1">
                                                <p class="text-xs text-gray-600">
                                                    <span class="font-medium">Date:</span> {{ $document['date'] }}
                                                </p>
                                                <p class="text-xs text-gray-600">
                                                    <span class="font-medium">Type:</span> {{ ucfirst(str_replace('_', ' ', $document['type'])) }}
                                                </p>
                                                @if(isset($document['related_info']))
                                                    <p class="text-xs text-gray-600">
                                                        <span class="font-medium">Info:</span> {{ $document['related_info'] }}
                                                    </p>
                                                @endif
                                                @if(isset($document['company_name']))
                                                    <p class="text-xs text-gray-600">
                                                        <span class="font-medium">Company:</span> {{ $document['company_name'] }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex space-x-2">
                                        <a href="{{ $document['url'] }}" target="_blank" class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ $document['url'] }}" download class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent shadow-sm text-xs leading-4 font-medium rounded-md text-white bg-primary hover:bg-secondary">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>