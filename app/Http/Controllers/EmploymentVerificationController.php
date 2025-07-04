<?php

namespace App\Http\Controllers;

use App\Models\Admin\Driver\DriverEmploymentCompany;
use App\Models\Admin\Driver\EmploymentVerificationToken;
use App\Models\UserDriverDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EmploymentVerificationController extends Controller
{
    /**
     * Muestra el formulario de verificación de empleo
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function showVerificationForm($token)
    {
        // Buscar el token de verificación
        $verification = EmploymentVerificationToken::where('token', $token)
            ->first();
            
        // Si no existe el token, mostrar página de error
        if (!$verification) {
            return view('employment-verification.error');
        }
        
        // Si el token ha expirado, mostrar página de expirado
        if ($verification->expires_at <= now()) {
            return view('employment-verification.expired');
        }
        
        // Si el token ya fue verificado, redirigir a la página de agradecimiento
        if ($verification->verified_at !== null) {
            return redirect()->route('employment-verification.thank-you');
        }

        // Obtener detalles del empleo
        $employmentCompany = DriverEmploymentCompany::find($verification->employment_company_id);
        $driver = UserDriverDetail::find($verification->driver_id);

        if (!$employmentCompany || !$driver) {
            return view('employment-verification.error');
        }
        
        // Obtener información adicional
        $masterCompany = null;
        if ($employmentCompany->master_company_id) {
            $masterCompany = \App\Models\Admin\Driver\MasterCompany::find($employmentCompany->master_company_id);
        }
        
        // Obtener el SSN del conductor desde DriverMedicalQualification
        $medicalQualification = \App\Models\Admin\Driver\DriverMedicalQualification::where('user_driver_detail_id', $driver->id)->first();
        $ssn = $medicalQualification ? $medicalQualification->social_security_number : null;

        return view('employment-verification.form', [
            'verification' => $verification,
            'employmentCompany' => $employmentCompany,
            'driver' => $driver,
            'token' => $token,
            'masterCompany' => $masterCompany,
            'ssn' => $ssn
        ]);
    }

    /**
     * Procesa la verificación de empleo
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processVerification(Request $request, $token)
    {
        try {
            // Buscar el token de verificación
            $verification = EmploymentVerificationToken::where('token', $token)->first();
            
            if (!$verification) {
                Log::error('Token de verificación no encontrado', ['token' => $token]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Token de verificación no encontrado'], 404);
                }
                return redirect()->route('employment-verification.error');
            }
            
            if ($verification->expires_at < now()) {
                Log::error('El token de verificación ha expirado', ['token' => $token]);
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'El token de verificación ha expirado'], 400);
                }
                return redirect()->route('employment-verification.expired');
            }
            
            // Validar la solicitud
            $validator = validator($request->all(), [
                'verification_status' => 'required|in:verified,rejected',
                'verification_notes' => 'nullable|string',
                'verification_by' => 'required|string|max:255',
                'signature' => 'required|string',
                'employment_confirmed' => 'required|boolean',
                // Nuevos campos de verificación
                'dates_confirmed' => 'required',
                'correct_dates' => 'nullable|string',
                'drove_commercial' => 'required',
                'safe_driver' => 'required',
                'had_accidents' => 'required',
                'reason_confirmed' => 'required',
                'different_reason' => 'nullable|string',
                'positive_drug_test' => 'required',
                'positive_alcohol_test' => 'required',
                'refused_test' => 'required',
                'completed_rehab' => 'required',
                'other_violations' => 'required',
                'violation_details' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                Log::error('Validación fallida en verificación de empleo', [
                    'token' => $token,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Iniciar transacción
            DB::beginTransaction();
            
            // Actualizar el token de verificación
            $verification->update([
                'verified' => true,
                'verified_at' => now(),
                'verification_status' => $request->verification_status,
                'verification_notes' => $request->verification_notes,
                'verification_by' => $request->verification_by ?? 'online_form'
            ]);
            
            // Obtener el registro de empleo
            $employmentCompany = DriverEmploymentCompany::find($verification->employment_company_id);
            
            if ($employmentCompany) {
                // Actualizar el estado de verificación
                $employmentCompany->update([
                    'verification_status' => $request->verification_status,
                    'employment_confirmed' => $request->employment_confirmed
                ]);
                
                // Preparar datos de rendimiento de seguridad
                $safetyPerformanceData = [
                    'dates_confirmed' => $request->dates_confirmed,
                    'correct_dates' => $request->correct_dates,
                    'drove_commercial' => $request->drove_commercial,
                    'safe_driver' => $request->safe_driver,
                    'had_accidents' => $request->had_accidents,
                    'reason_confirmed' => $request->reason_confirmed,
                    'different_reason' => $request->different_reason,
                    'positive_drug_test' => $request->positive_drug_test,
                    'positive_alcohol_test' => $request->positive_alcohol_test,
                    'refused_test' => $request->refused_test,
                    'completed_rehab' => $request->completed_rehab,
                    'other_violations' => $request->other_violations,
                    'violation_details' => $request->violation_details,
                    'verified_at' => now()->toDateTimeString(),
                ];
                
                // Guardar en un campo JSON o en la columna de metadatos si existe
                if (Schema::hasColumn('driver_employment_companies', 'safety_performance_data')) {
                    $employmentCompany->update(['safety_performance_data' => json_encode($safetyPerformanceData)]);
                } else {
                    // Si no existe la columna, guardar en metadata o crear un registro relacionado
                    // Esto dependerá de la estructura de la base de datos
                    if (Schema::hasColumn('driver_employment_companies', 'metadata')) {
                        $metadata = json_decode($employmentCompany->metadata, true) ?: [];
                        $metadata['safety_performance_data'] = $safetyPerformanceData;
                        $employmentCompany->update(['metadata' => json_encode($metadata)]);
                    }
                }
                
                // Guardar la firma y el PDF
                if ($request->has('signature') && !empty($request->signature)) {
                    // Obtener el driver relacionado
                    $driver = $employmentCompany->userDriverDetail ?? $employmentCompany->driver;
                    
                    if ($driver) {
                        try {
                            // Procesar la firma (base64 a imagen)
                            $image_parts = explode(";", $request->signature);
                            $image_type_aux = explode("image/", $image_parts[0]);
                            $image_type = $image_type_aux[1];
                            $image_base64 = explode(",", $image_parts[1]);
                            $image = base64_decode($image_base64[0]);
                            
                            // Generar nombre de archivo para la firma
                            $signatureName = 'employment_verification_signature_' . time() . '.png';
                            
                            // Crear directorio para la firma - EXACTAMENTE driver/{id}/certification/
                            $signaturePath = 'driver/' . $driver->id . '/certification/';
                            $fullSignaturePath = storage_path('app/public/' . $signaturePath);
                            
                            // Asegurarse de que el directorio exista
                            if (!file_exists($fullSignaturePath)) {
                                mkdir($fullSignaturePath, 0755, true);
                            }
                            
                            // Guardar la firma directamente en el directorio del conductor
                            $signatureFilePath = $fullSignaturePath . $signatureName;
                            file_put_contents($signatureFilePath, $image);
                            
                            // Verificar que la firma se guardó correctamente
                            if (!file_exists($signatureFilePath) || filesize($signatureFilePath) === 0) {
                                throw new \Exception("Error al guardar la firma del conductor");
                            }
                            
                            // Guardar la ruta relativa para uso posterior
                            $relativeSignaturePath = $signaturePath . $signatureName;
                            
                            // Generar el PDF
                            // Obtener el SSN del conductor desde DriverMedicalQualification
                            $driverMedical = \App\Models\Admin\Driver\DriverMedicalQualification::where('user_driver_detail_id', $driver->id)->first();
                            $ssn = $driverMedical ? $driverMedical->social_security_number : null;
                            
                            $data = [
                                'verification' => $verification,
                                'employmentCompany' => $employmentCompany,
                                'driver' => $driver,
                                'signature' => $request->signature,
                                'safetyPerformanceData' => $safetyPerformanceData,
                                'ssn' => $ssn,
                                'verification_by' => $request->verification_by // Pasar directamente el valor del formulario
                            ];
                            $pdf = PDF::loadView('employment-verification.pdf', $data);

                            // Determinar el nombre de la empresa para el nombre del archivo
                            $companyName = 'unknown';
                            if (isset($employmentCompany->company) && !empty($employmentCompany->company->name)) {
                                $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $employmentCompany->company->name);
                            } elseif (!empty($employmentCompany->company_name)) {
                                $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $employmentCompany->company_name);
                            }
                            
                            // Nombre del archivo PDF
                            $pdfName = 'employment_verification_' . $companyName . '_' . time() . '.pdf';
                            
                            // Crear directorio para el PDF - EXACTAMENTE driver/{id}/
                            $pdfPath = 'driver/' . $driver->id . '/';
                            $fullPdfPath = storage_path('app/public/' . $pdfPath);
                            
                            // Asegurarse de que el directorio exista
                            if (!file_exists($fullPdfPath)) {
                                mkdir($fullPdfPath, 0755, true);
                            }
                            
                            // Guardar el PDF directamente en el directorio del conductor
                            $pdfFilePath = $fullPdfPath . $pdfName;
                            $pdf->save($pdfFilePath);
                            
                            // Verificar que el PDF se guardó correctamente
                            if (!file_exists($pdfFilePath) || filesize($pdfFilePath) === 0) {
                                throw new \Exception("Error al guardar el PDF del conductor");
                            }
                            
                            // Guardar la ruta relativa para uso posterior
                            $relativePdfPath = $pdfPath . $pdfName;
                            
                            // Actualizar el token de verificación con las rutas de los archivos
                            $verification->update([
                                'signature_path' => $relativeSignaturePath,
                                'document_path' => $relativePdfPath
                            ]);
                            
                            // Registrar éxito en logs
                            Log::info('Archivos guardados correctamente en las rutas específicas', [
                                'signature_path' => $relativeSignaturePath,
                                'pdf_path' => $relativePdfPath,
                                'driver_id' => $driver->id,
                                'token' => $token
                            ]);
                            
                        } catch (\Exception $e) {
                            Log::error('Error al generar o guardar los archivos', [
                                'error' => $e->getMessage(),
                                'token' => $token,
                                'trace' => $e->getTraceAsString()
                            ]);
                            throw new \Exception("Error al generar o guardar los archivos: {$e->getMessage()}");
                        }
                    }
                }
            }

            DB::commit();

            // Verificar si la solicitud es AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Verification processed successfully',
                    'redirect' => route('employment-verification.thank-you')
                ]);
            }
            
            return redirect()->route('employment-verification.thank-you');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar verificación de empleo', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Verificar si la solicitud es AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error processing verification: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error processing verification. Please try again.');
        }
    }

    /**
     * Muestra la página de agradecimiento después de la verificación
     *
     * @return \Illuminate\View\View
     */
    public function thankYou()
    {
        return view('employment-verification.thank-you');
    }

    /**
     * Muestra la página de error cuando el token ha expirado
     *
     * @return \Illuminate\View\View
     */
    public function expired()
    {
        return view('employment-verification.expired');
    }

    /**
     * Muestra la página de error
     *
     * @return \Illuminate\View\View
     */
    public function error()
    {
        return view('employment-verification.error');
    }
}
