<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VehicleVerificationToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use Illuminate\Support\Str;

class VehicleVerificationController extends Controller
{
    /**
     * Muestra la página de verificación de vehículo para un token dado.
     */
    public function showVerificationForm($token)
    {
        Log::info('Iniciando verificación de token', [
            'token' => $token,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Buscar el token en la base de datos
        $verification = VehicleVerificationToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->where('verified', false)
            ->first();
        
        if (!$verification) {
            Log::warning('Token no encontrado o inválido', [
                'token' => $token,
                'expired_tokens' => VehicleVerificationToken::where('token', $token)->where('expires_at', '<=', now())->count(),
                'verified_tokens' => VehicleVerificationToken::where('token', $token)->where('verified', true)->count(),
                'total_tokens' => VehicleVerificationToken::where('token', $token)->count()
            ]);
            return view('vehicle-verification.already-verified');
        }
        
        Log::info('Token encontrado exitosamente', [
            'token' => $token,
            'verification_id' => $verification->id,
            'application_id' => $verification->driver_application_id,
            'vehicle_id' => $verification->vehicle_id,
            'expires_at' => $verification->expires_at->toDateTimeString()
        ]);
        
        // Cargar relaciones necesarias
        $verification->load(['vehicle', 'driverApplication.details']);
        
        // Verificar que tenemos todos los datos necesarios
        if (!$verification->vehicle || !$verification->driverApplication) {
            Log::error('Datos incompletos para verificación de vehículo', [
                'token' => $token,
                'verification_id' => $verification->id,
                'has_vehicle' => (bool)$verification->vehicle,
                'has_application' => (bool)$verification->driverApplication,
                'vehicle_id' => $verification->vehicle_id,
                'application_id' => $verification->driver_application_id
            ]);
            return view('vehicle-verification.error', [
                'message' => 'No se encontraron todos los datos necesarios para la verificación.'
            ]);
        }
        
        Log::info('Relaciones cargadas exitosamente', [
            'token' => $token,
            'vehicle_id' => $verification->vehicle->id,
            'application_id' => $verification->driverApplication->id
        ]);
        
        // Obtener los datos del vehículo y la aplicación
        $vehicle = $verification->vehicle;
        $application = $verification->driverApplication;
        $applicationDetails = $application->details;
        
        // Verificar que tenemos los detalles de la aplicación
        if (!$applicationDetails) {
            Log::error('Detalles de aplicación no encontrados', [
                'token' => $token,
                'application_id' => $application->id,
                'verification_id' => $verification->id,
                'application_exists' => (bool)$application,
                'details_relationship' => $application->details()->exists(),
                'details_count' => $application->details()->count()
            ]);
            return view('vehicle-verification.error', [
                'message' => 'No se encontraron los detalles de la aplicación para la verificación.'
            ]);
        }
        
        Log::info('Detalles de aplicación encontrados exitosamente', [
            'token' => $token,
            'application_id' => $application->id,
            'details_id' => $applicationDetails->id,
            'applying_position' => $applicationDetails->applying_position
        ]);
        
        // Determinar si es owner_operator o third_party_driver
        $isOwnerOperator = $applicationDetails->applying_position === 'owner_operator';
        
        // Cargar relaciones adicionales
        $application->load(['user', 'ownerOperatorDetail', 'thirdPartyDetail']);
        $vehicle->load(['driver.user', 'driver.carrier']);
        
        // Preparar los datos para la vista
        $data = [
            'verification' => $verification,
            'vehicle' => $vehicle,
            'application' => $application,
            'applicationDetails' => $applicationDetails,
            'isOwnerOperator' => $isOwnerOperator
        ];
        
        return view('vehicle-verification.form', $data);
    }
    
    /**
     * Procesa la verificación de un vehículo.
     */
    public function processVerification(Request $request, $token)
    {
        // Convertir el checkbox a boolean antes de la validación
        $request->merge([
            'agree_terms' => $request->has('agree_terms') && $request->input('agree_terms') !== null
        ]);
        
        // Validar los datos de entrada
        $validated = $request->validate([
            'signature' => 'required|string',
            'agree_terms' => 'required|boolean'
        ]);
        
        // Buscar el token de verificación
        $verification = VehicleVerificationToken::where('token', $token)->first();
        
        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Token de verificación no válido.'
            ], 400);
        }
        
        // Verificar que el token no haya expirado
        if ($verification->expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'El token de verificación ha expirado.'
            ], 400);
        }
        
        // Verificar que el token no haya sido usado
        if ($verification->verified) {
            return response()->json([
                'success' => false,
                'message' => 'Este vehículo ya ha sido verificado.'
            ], 400);
        }
        
        try {
            DB::beginTransaction();

            // Procesar la firma (convertir base64 a imagen PNG)
            if (!empty($validated['signature']) && strpos($validated['signature'], 'data:image') === 0) {
                // Guardar la firma como base64 en el modelo
                $verification->signature_data = $validated['signature'];
                $verification->verified = true;
                $verification->verified_at = now();
                $verification->save();
                
                Log::info('Firma guardada como base64', [
                    'verification_id' => $verification->id,
                    'signature_length' => strlen($validated['signature'])
                ]);
                
                // Guardar la firma como archivo PNG usando Media Library
                try {
                    // Decodificar la firma base64
                    $signatureData = base64_decode(explode(',', $validated['signature'])[1]);
                    
                    // Crear archivo temporal
                    $tempFile = tempnam(sys_get_temp_dir(), 'sig') . '.png';
                    file_put_contents($tempFile, $signatureData);
                    
                    Log::info('Firma guardada como archivo temporal', [
                        'verification_id' => $verification->id,
                        'temp_file' => $tempFile
                    ]);
                    
                    // Guardar en media library
                    $verification->clearMediaCollection('signature');
                    $verification->addMedia($tempFile)
                        ->toMediaCollection('signature');
                    
                    Log::info('Firma guardada en Media Library', [
                        'verification_id' => $verification->id,
                        'media' => $verification->getMedia('signature')->first() ? 
                            $verification->getMedia('signature')->first()->id : 'No media'
                    ]);
                    
                    // Eliminar archivo temporal
                    @unlink($tempFile);
                    
                } catch (\Exception $e) {
                    Log::error('Error al guardar la firma como archivo', [
                        'verification_id' => $verification->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                // Obtener el UserDriverDetail para guardar en la misma ruta que las fotos
                $userDriverDetail = null;
                
                if ($verification->vehicle && $verification->vehicle->driver) {
                    $userDriverDetail = $verification->vehicle->driver;
                    
                    Log::info('Usando UserDriverDetail del vehículo', [
                        'verification_id' => $verification->id,
                        'vehicle_id' => $verification->vehicle->id,
                        'driver_id' => $userDriverDetail->id
                    ]);
                } else if ($verification->driverApplication && $verification->driverApplication->userDriverDetail) {
                    $userDriverDetail = $verification->driverApplication->userDriverDetail;
                    
                    Log::info('Usando UserDriverDetail de la aplicación', [
                        'verification_id' => $verification->id,
                        'driver_application_id' => $verification->driverApplication->id,
                        'driver_id' => $userDriverDetail->id
                    ]);
                } else {
                    // Si no se puede obtener el UserDriverDetail, usar el ID 4 como fallback
                    $driverId = 4;
                    
                    Log::info('Usando ID 4 como fallback', [
                        'verification_id' => $verification->id
                    ]);
                }
                
                // Crear directorio de destino usando la misma estructura que CustomPathGenerator
                $directory = $userDriverDetail ? "driver/{$userDriverDetail->id}/vehicle_verifications" : "driver/4/vehicle_verifications";
                Storage::disk('public')->makeDirectory($directory);
                
                // Eliminar archivos antiguos antes de generar nuevos
                try {
                    // Obtener todos los archivos en el directorio
                    $files = Storage::disk('public')->files($directory);
                    
                    // Eliminar archivos de consentimiento antiguos
                    foreach ($files as $file) {
                        $fileName = basename($file);
                        if (Str::startsWith($fileName, 'third_party_consent_') || $fileName === 'consentimiento_propietario_third_party.pdf') {
                            Log::info('Eliminando archivo antiguo de consentimiento', ['file' => $file]);
                            Storage::disk('public')->delete($file);
                        }
                    }
                    
                    // Eliminar archivos de lease agreement antiguos
                    foreach ($files as $file) {
                        $fileName = basename($file);
                        if (Str::startsWith($fileName, 'lease_agreement_')) {
                            Log::info('Eliminando archivo antiguo de lease agreement', ['file' => $file]);
                            Storage::disk('public')->delete($file);
                        }
                    }
                    
                    Log::info('Archivos antiguos eliminados correctamente');
                } catch (\Exception $e) {
                    Log::error('Error al eliminar archivos antiguos', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                // Generar el documento de consentimiento usando el template correcto
                try {
                    $pdf = $this->generateThirdPartyConsentDocument($verification);
                    Log::info('PDF de consentimiento generado correctamente');
                    
                    // Obtener el UserDriverDetail para guardar en la misma ruta que las fotos
                    $userDriverDetail = null;
                    
                    if ($verification->vehicle && $verification->vehicle->driver) {
                        $userDriverDetail = $verification->vehicle->driver;
                        
                        Log::info('Usando UserDriverDetail del vehículo', [
                            'verification_id' => $verification->id,
                            'vehicle_id' => $verification->vehicle->id,
                            'driver_id' => $userDriverDetail->id
                        ]);
                    } else if ($verification->driverApplication && $verification->driverApplication->userDriverDetail) {
                        $userDriverDetail = $verification->driverApplication->userDriverDetail;
                        
                        Log::info('Usando UserDriverDetail de la aplicación', [
                            'verification_id' => $verification->id,
                            'driver_application_id' => $verification->driverApplication->id,
                            'driver_id' => $userDriverDetail->id
                        ]);
                    } else {
                        // Si no se puede obtener el UserDriverDetail, usar el ID 4 como fallback
                        $driverId = 4;
                        
                        Log::info('Usando ID 4 como fallback', [
                            'verification_id' => $verification->id
                        ]);
                    }
                    
                    // Crear directorio de destino usando la misma estructura que CustomPathGenerator
                    $directory = $userDriverDetail ? "driver/{$userDriverDetail->id}/vehicle_verifications" : "driver/4/vehicle_verifications";
                    Storage::disk('public')->makeDirectory($directory);
                    
                    // Generar un nombre único para el documento de consentimiento usando timestamp
                    $timestamp = now()->format('YmdHis');
                    $path = "{$directory}/third_party_consent_{$timestamp}.pdf";
                    
                    // Registrar que estamos creando una nueva versión del documento
                    Log::info('Creando nueva versión del PDF de consentimiento', [
                        'verification_id' => $verification->id,
                        'path' => $path,
                        'timestamp' => $timestamp
                    ]);
                    
                    // Guardar el nuevo documento con la firma actualizada
                    Storage::disk('public')->put($path, $pdf->output());
                    
                    // Logging para verificar la ruta del PDF de consentimiento
                    Log::info('PDF de consentimiento guardado correctamente', [
                        'verification_id' => $verification->id,
                        'path' => $path,
                        'full_path' => Storage::disk('public')->path($path)
                    ]);
                    
                    // Actualizar la ruta del documento de consentimiento
                    $verification->document_path = $path;
                    $verification->save();
                    
                    // Generar el documento PDF de lease agreement
                    $pdfLeaseAgreement = $this->generateLeaseAgreementDocument($verification);
                    Log::info('PDF de lease agreement generado correctamente');
                    
                    // Determinar el nombre del archivo según el tipo de conductor
                    $applicationDetails = $verification->driverApplication->details;
                    $isOwnerOperator = $applicationDetails->applying_position === 'owner_operator';
                    $leaseAgreementFilename = $isOwnerOperator ? 
                        'lease_agreement_owner_operator' : 
                        'lease_agreement_third_party';
                    
                    // Usar el mismo timestamp para el lease agreement para mantener consistencia
                    $leaseAgreementPath = "{$directory}/{$leaseAgreementFilename}_{$timestamp}.pdf";
                    
                    // Registrar que estamos creando una nueva versión del documento de lease agreement
                    Log::info('Creando nueva versión del PDF de lease agreement', [
                        'verification_id' => $verification->id,
                        'path' => $leaseAgreementPath,
                        'timestamp' => $timestamp
                    ]);
                    
                    // Guardar el nuevo documento de lease agreement
                    Storage::disk('public')->put($leaseAgreementPath, $pdfLeaseAgreement->output());
                    
                    // Logging para verificar la ruta del PDF de lease agreement
                    Log::info('PDF de lease agreement guardado correctamente', [
                        'verification_id' => $verification->id,
                        'path' => $leaseAgreementPath,
                        'full_path' => Storage::disk('public')->path($leaseAgreementPath)
                    ]);
                    
                    // Actualizar el estado del vehículo a 'pending'
                    if ($verification->vehicle) {
                        $verification->vehicle->update(['status' => 'pending']);
                    }
                    
                } catch (\Exception $pdfException) {
                    Log::error('Error al generar el PDF', [
                        'error' => $pdfException->getMessage(),
                        'trace' => $pdfException->getTraceAsString()
                    ]);
                    throw $pdfException; // Re-lanzar la excepción para que sea manejada por el catch principal
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Verificación completada con éxito.'
                ]);
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'La firma es requerida.'
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en la verificación', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la verificación: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Guarda una firma en base64 en Media Library.
     */
    private function saveSignatureToMediaLibrary(VehicleVerificationToken $verification, $signatureData)
    {
        // Extraer los datos de la imagen base64
        $image_parts = explode(";base64,", $signatureData);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        
        // Crear un archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'signature_');
        file_put_contents($tempFile, $image_base64);
        
        // Guardar en Media Library
        $verification->addMedia($tempFile)
            ->usingName('signature_' . $verification->id)
            ->usingFileName('signature_' . $verification->id . '.' . $image_type)
            ->toMediaCollection('signature');
            
        // Eliminar el archivo temporal
        @unlink($tempFile);
    }
    
    /**
     * Guarda una firma desde una ruta en Media Library.
     */
    private function saveSignatureFromPath(VehicleVerificationToken $verification, $path)
    {
        $fullPath = storage_path('app/public/' . $path);
        
        if (file_exists($fullPath)) {
            $verification->addMedia($fullPath)
                ->usingName('signature_' . $verification->id)
                ->toMediaCollection('signature');
        }
    }
    
    /**
     * Generar el documento de consentimiento en PDF.
     */
    private function generateThirdPartyConsentDocument(VehicleVerificationToken $verification)
    {
        try {
            // Cargar relaciones necesarias
            $verification->load(['vehicle', 'driverApplication.details', 'driverApplication.user', 'driverApplication.ownerOperatorDetail', 'driverApplication.thirdPartyDetail', 'vehicle.driver.user', 'vehicle.driver.carrier']);
            
            // Obtener los datos del vehículo y la aplicación
            $vehicle = $verification->vehicle;
            $application = $verification->driverApplication;
            $applicationDetails = $application->details;
            
            // Obtener los datos del conductor
            $driverDetails = null;
            if ($vehicle && $vehicle->driver) {
                $driverDetails = $vehicle->driver;
            } else if ($application && $application->userDriverDetail) {
                $driverDetails = $application->userDriverDetail;
            }
            
            // Si no hay detalles del conductor, usar datos básicos
            if (!$driverDetails && $application && $application->user) {
                $driverDetails = new \stdClass();
                $driverDetails->user = $application->user;
                $driverDetails->middle_name = '';
                $driverDetails->last_name = '';
                $driverDetails->phone = '';
            }
            
            // Preparar los datos para la vista
            $data = [
                'verification' => $verification,
                'vehicle' => $vehicle,
                'driverDetails' => $driverDetails,
                'date' => now()->format('m/d/Y'),
                'signaturePath' => null,
                'signatureData' => $verification->signature_data
            ];
            
            // Obtener la firma si existe en Media Library
            $media = $verification->getMedia('signature')->first();
            if ($media) {
                $data['signaturePath'] = $media->getPath();
            }
            
            // Generar el PDF usando la vista third-party-consent.blade.php
            return PDF::loadView('pdfs.third-party-consent', $data);
            
        } catch (\Exception $e) {
            Log::error('Error al generar el PDF de consentimiento', [
                'verification_id' => $verification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-lanzar la excepción para que sea manejada por el método que llama
        }
    }

    /**
     * Generar el documento de lease agreement en PDF.
     */
    private function generateLeaseAgreementDocument(VehicleVerificationToken $verification)
    {
        try {
            // Verificar que tenemos todos los datos necesarios
            if (!$verification->vehicle) {
                throw new \Exception('No se encontró el vehículo asociado al token de verificación');
            }
            
            if (!$verification->driverApplication) {
                throw new \Exception('No se encontró la aplicación del conductor asociada al token de verificación');
            }
            
            $vehicle = $verification->vehicle;
            $application = $verification->driverApplication;
            $applicationDetails = $application->details;
            
            // Obtener el driver detail directamente del vehículo
            $driverDetails = $vehicle->driver;
            if (!$driverDetails) {
                throw new \Exception('No se encontraron los detalles del conductor asociados al vehículo');
            }
            
            $user = $driverDetails->user;
            if (!$user) {
                throw new \Exception('No se encontró el usuario asociado a los detalles del conductor');
            }

            // Intentar obtener la firma desde Media Library
            $signatureData = null;
            $signaturePath = null;
            
            // Primero intentar obtener la firma desde Media Library
            $signatureMedia = $verification->getMedia('signature')->first();
            if ($signatureMedia) {
                $signaturePath = $signatureMedia->getPath();
                
                if (file_exists($signaturePath)) {
                    // Convertir la imagen a base64
                    $signatureData = 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath));
                }
            }
            
            // Si no se encontró en Media Library, usar signature_data como respaldo
            if (empty($signatureData) && !empty($verification->signature_data)) {
                // Si signature_data contiene JSON (guardado como JSON string), extraer la ruta
                if (strpos($verification->signature_data, '{') === 0) {
                    try {
                        $signatureJson = json_decode($verification->signature_data, true);
                        if (isset($signatureJson['path'])) {
                            $signaturePath = storage_path('app/public/' . $signatureJson['path']);
                            if (file_exists($signaturePath)) {
                                $signatureData = 'data:image/png;base64,' . base64_encode(file_get_contents($signaturePath));
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error al decodificar JSON de firma', [
                            'error' => $e->getMessage()
                        ]);
                    }
                } else {
                    // Usar signature_data directamente
                    $signatureData = $verification->signature_data;
                    
                    // Verificar que la firma comienza con el formato correcto de base64
                    if (strpos($signatureData, 'data:image') !== 0) {
                        // Si no tiene el prefijo correcto, agregarlo
                        $signatureData = 'data:image/png;base64,' . base64_encode($signatureData);
                    }
                }
            }
            
            // Determinar si es owner_operator o third_party_driver
            $isOwnerOperator = $applicationDetails->applying_position === 'owner_operator';
            
            // Preparar los datos para el PDF según el tipo de conductor
            if ($isOwnerOperator) {
                // Obtener los detalles de Owner Operator desde la nueva tabla
                $ownerOperatorDetails = $application->ownerOperatorDetail;
                
                if (!$ownerOperatorDetails) {
                    Log::warning('No se encontraron detalles de Owner Operator en la nueva tabla', [
                        'application_id' => $application->id
                    ]);
                    
                    // Intentar usar los datos de la tabla anterior como respaldo
                    $data = [
                        'carrierName' => $driverDetails->carrier->name ?? 'EF Services',
                        'carrierAddress' => $driverDetails->carrier->address ?? '',
                        'ownerName' => $applicationDetails->owner_name ?? '',
                        'ownerDba' => '',
                        'ownerAddress' => '',
                        'ownerPhone' => $applicationDetails->owner_phone ?? '',
                        'ownerContact' => '',
                        'ownerFein' => '',
                        'vehicleYear' => $vehicle->year,
                        'vehicleMake' => $vehicle->make,
                        'vehicleVin' => $vehicle->vin,
                        'vehicleUnit' => $vehicle->company_unit_number,
                        'signedDate' => Carbon::now()->format('m/d/Y'),
                        'carrierMc' => $driverDetails->carrier->mc_number ?? '',
                        'carrierUsdot' => $driverDetails->carrier->state_dot ?? '',
                        'signaturePath' => $signaturePath ?? null,
                        'signatureData' => $signatureData
                    ];
                } else {
                    // Usar los datos de la nueva tabla
                    $data = [
                        'carrierName' => $driverDetails->carrier->name ?? 'EF Services',
                        'carrierAddress' => $driverDetails->carrier->address ?? '',
                        'ownerName' => $ownerOperatorDetails->owner_name,
                        'ownerDba' => '',
                        'ownerAddress' => '',
                        'ownerPhone' => $ownerOperatorDetails->owner_phone,
                        'ownerContact' => '',
                        'ownerFein' => '',
                        'vehicleYear' => $vehicle->year,
                        'vehicleMake' => $vehicle->make,
                        'vehicleVin' => $vehicle->vin,
                        'vehicleUnit' => $vehicle->company_unit_number,
                        'signedDate' => Carbon::now()->format('m/d/Y'),
                        'carrierMc' => $driverDetails->carrier->mc_number ?? '',
                        'carrierUsdot' => $driverDetails->carrier->state_dot ?? '',
                        'signaturePath' => $signaturePath ?? null,
                        'signatureData' => $signatureData
                    ];
                }
                
                $pdfFilename = 'lease_agreement_owner_operator.pdf';
            } else {
                // Third Party Driver
                // Obtener los detalles de Third Party desde la nueva tabla
                $thirdPartyDetails = $application->thirdPartyDetail;
                
                if (!$thirdPartyDetails) {
                    Log::warning('No se encontraron detalles de Third Party en la nueva tabla', [
                        'application_id' => $application->id
                    ]);
                    
                    // Intentar usar los datos de la tabla anterior como respaldo
                    $data = [
                        'carrierName' => $driverDetails->carrier->name ?? 'EF Services',
                        'carrierAddress' => $driverDetails->carrier->address ?? '',
                        'ownerName' => $applicationDetails->third_party_name ?? '',
                        'ownerDba' => $applicationDetails->third_party_dba ?? '',
                        'ownerAddress' => $applicationDetails->third_party_address ?? '',
                        'ownerPhone' => $applicationDetails->third_party_phone ?? '',
                        'ownerContact' => $applicationDetails->third_party_contact ?? '',
                        'ownerFein' => $applicationDetails->third_party_fein ?? '',
                        'vehicleYear' => $vehicle->year,
                        'vehicleMake' => $vehicle->make,
                        'vehicleVin' => $vehicle->vin,
                        'vehicleUnit' => $vehicle->company_unit_number,
                        'signedDate' => Carbon::now()->format('m/d/Y'),
                        'carrierMc' => $driverDetails->carrier->mc_number ?? '',
                        'carrierUsdot' => $driverDetails->carrier->state_dot ?? '',
                        'signaturePath' => $signaturePath ?? null,
                        'signatureData' => $signatureData
                    ];
                } else {
                    // Usar los datos de la nueva tabla
                    $data = [
                        'carrierName' => $driverDetails->carrier->name ?? 'EF Services',
                        'carrierAddress' => $driverDetails->carrier->address ?? '',
                        'ownerName' => $thirdPartyDetails->third_party_name,
                        'ownerDba' => $thirdPartyDetails->third_party_dba,
                        'ownerAddress' => $thirdPartyDetails->third_party_address,
                        'ownerPhone' => $thirdPartyDetails->third_party_phone,
                        'ownerContact' => $thirdPartyDetails->third_party_contact,
                        'ownerFein' => $thirdPartyDetails->third_party_fein,
                        'vehicleYear' => $vehicle->year,
                        'vehicleMake' => $vehicle->make,
                        'vehicleVin' => $vehicle->vin,
                        'vehicleUnit' => $vehicle->company_unit_number,
                        'signedDate' => Carbon::now()->format('m/d/Y'),
                        'carrierMc' => $driverDetails->carrier->mc_number ?? '',
                        'carrierUsdot' => $driverDetails->carrier->state_dot ?? '',
                        'signaturePath' => $signaturePath ?? null,
                        'signatureData' => $signatureData
                    ];
                }
                
                $pdfFilename = 'lease_agreement_third_party.pdf';
            }
            
            return PDF::loadView('pdfs.lease-agreement', $data);
            
        } catch (\Exception $e) {
            Log::error('Error al generar el PDF de lease agreement', [
                'verification_id' => $verification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-lanzar la excepción para que sea manejada por el método que llama
        }
    }
    
    /**
     * Muestra la página de agradecimiento después de completar la verificación.
     */
    public function showThankYou($token)
    {
        // Buscar el token en la base de datos
        $verification = VehicleVerificationToken::where('token', $token)
            ->where('verified', true)
            ->first();
        
        if (!$verification) {
            return redirect()->route('vehicle.verification.form', ['token' => $token]);
        }
        
        // Cargar relaciones necesarias
        $verification->load(['vehicle', 'driverApplication.details', 'driverApplication.user', 'driverApplication.ownerOperatorDetail', 'driverApplication.thirdPartyDetail', 'vehicle.driver.user', 'vehicle.driver.carrier']);
        
        return view('vehicle-verification.thank-you', [
            'verification' => $verification,
            'documentUrl' => url('storage/' . $verification->document_path)
        ]);
    }
}
