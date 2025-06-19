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

        return view('employment-verification.form', [
            'verification' => $verification,
            'employmentCompany' => $employmentCompany,
            'driver' => $driver,
            'token' => $token
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
            ]);
            
            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Si el token ya fue verificado, simplemente redirigir a la página de agradecimiento
            if ($verification->verified_at !== null) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Verification already processed',
                        'redirect' => route('employment-verification.thank-you')
                    ]);
                }
                return redirect()->route('employment-verification.thank-you');
            }
            
            DB::beginTransaction();

            // Actualizar el token de verificación
            $verification->update([
                'verified_at' => now(),
                'verification_status' => $request->verification_status,
                'verification_notes' => $request->verification_notes,
            ]);

            // Actualizar el registro de empleo
            $employmentCompany = DriverEmploymentCompany::find($verification->employment_company_id);
            if ($employmentCompany) {
                // Datos básicos de verificación
                $employmentCompany->update([
                    'verification_status' => $request->verification_status,
                    'verification_notes' => $request->verification_notes,
                ]);
                
                // Guardar datos adicionales de verificación en JSON
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

                // Guardar la firma como documento
                if ($request->has('signature') && !empty($request->signature)) {
                    // Decodificar la imagen base64
                    $image = $request->signature;
                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    
                    // Crear directorio temporal si no existe
                    $tempDir = storage_path('app/temp');
                    if (!file_exists($tempDir)) {
                        if (!mkdir($tempDir, 0755, true)) {
                            Log::error('No se pudo crear el directorio temporal', [
                                'dir' => $tempDir,
                                'token' => $token
                            ]);
                            throw new \Exception("No se pudo crear el directorio temporal: {$tempDir}");
                        }
                    }
                    
                    // Verificar permisos de escritura
                    if (!is_writable($tempDir)) {
                        Log::error('El directorio temporal no tiene permisos de escritura', [
                            'dir' => $tempDir,
                            'token' => $token,
                            'permisos' => substr(sprintf('%o', fileperms($tempDir)), -4)
                        ]);
                        throw new \Exception("El directorio temporal no tiene permisos de escritura: {$tempDir}");
                    }
                    
                    // Verificar que el directorio se haya creado correctamente
                    if (!is_dir($tempDir) || !is_writable($tempDir)) {
                        throw new \Exception("No se puede crear o escribir en el directorio temporal: {$tempDir}");
                    }
                    
                    // Crear un archivo temporal
                    $imageName = 'employment_verification_' . time() . '.png';
                    $tempPath = $tempDir . '/' . $imageName;
                    
                    // Guardar la imagen en el directorio temporal
                    file_put_contents($tempPath, base64_decode($image));
                    
                    // Añadir el archivo como media al registro de empleo
                    $employmentCompany->addMedia($tempPath)
                        ->usingName('Employment Verification Signature')
                        ->usingFileName($imageName)
                        ->toMediaCollection('signature');
                    
                    // Obtener el driver asociado para guardar también el documento en sus archivos
                    $driver = $employmentCompany->userDriverDetail;
                    if ($driver) {
                        // Crear un PDF con la información de verificación
                        try {
                            $pdf = PDF::loadView('employment-verification.pdf', [
                                'verification' => $verification,
                                'employmentCompany' => $employmentCompany,
                                'driver' => $driver,
                                'signature' => $request->signature,
                                'safetyPerformanceData' => $safetyPerformanceData
                            ]);
                            
                            // Usar un nombre de compañía seguro, o un valor predeterminado si no está disponible
                            $companyName = 'company';
                            if (isset($employmentCompany->company) && !empty($employmentCompany->company->name)) {
                                $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $employmentCompany->company->name);
                            } elseif (!empty($employmentCompany->company_name)) {
                                $companyName = preg_replace('/[^a-zA-Z0-9]/', '_', $employmentCompany->company_name);
                            }
                            
                            // Guardar el PDF temporalmente (usando el mismo directorio temporal que ya verificamos)
                            $pdfName = 'employment_verification_' . $companyName . '_' . time() . '.pdf';
                            $pdfPath = $tempDir . '/' . $pdfName;
                            
                            // Intentar guardar el PDF y verificar el resultado
                            try {
                                $pdf->save($pdfPath);
                                
                                // Verificar que el archivo se haya creado correctamente
                                if (!file_exists($pdfPath)) {
                                    throw new \Exception("El archivo PDF no existe después de guardarlo");
                                }
                                
                                // Verificar que el archivo tenga contenido
                                if (filesize($pdfPath) === 0) {
                                    throw new \Exception("El archivo PDF está vacío");
                                }
                                
                                // Registrar éxito en logs
                                Log::info('PDF generado correctamente', [
                                    'path' => $pdfPath,
                                    'size' => filesize($pdfPath),
                                    'token' => $token
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al guardar el PDF', [
                                    'error' => $e->getMessage(),
                                    'path' => $pdfPath,
                                    'token' => $token
                                ]);
                                throw new \Exception("Error al guardar el PDF: {$e->getMessage()}");
                            }
                        } catch (\Exception $pdfError) {
                            Log::error('Error al generar el PDF de verificación', [
                                'error' => $pdfError->getMessage(),
                                'token' => $token
                            ]);
                            throw $pdfError; // Re-lanzar para que sea capturado por el try-catch principal
                        }
                        
                        // Añadir el PDF a los documentos del conductor
                        $documentName = 'Employment Verification - ' . $companyName;
                        $driver->addMedia($pdfPath)
                            ->usingName($documentName)
                            ->usingFileName($pdfName)
                            ->toMediaCollection('employment_verification_documents');
                            
                            // Crear el directorio público si no existe
                            $publicPath = 'driver/' . $driver->user_id . '/employment_verifications/';
                            $fullPublicPath = storage_path('app/public/' . $publicPath);
                            
                            if (!file_exists($fullPublicPath)) {
                                if (!mkdir($fullPublicPath, 0755, true)) {
                                    Log::error('No se pudo crear el directorio público', [
                                        'dir' => $fullPublicPath,
                                        'token' => $token
                                    ]);
                                    throw new \Exception("No se pudo crear el directorio público: {$fullPublicPath}");
                                }
                            }
                            
                            // Verificar permisos de escritura en el directorio público
                            if (!is_writable($fullPublicPath)) {
                                Log::error('El directorio público no tiene permisos de escritura', [
                                    'dir' => $fullPublicPath,
                                    'token' => $token,
                                    'permisos' => substr(sprintf('%o', fileperms($fullPublicPath)), -4)
                                ]);
                                throw new \Exception("El directorio público no tiene permisos de escritura: {$fullPublicPath}");
                            }
                            
                            // Guardar una copia en el almacenamiento público
                            try {
                                // Verificar que el archivo temporal exista y tenga contenido
                                if (!file_exists($pdfPath) || filesize($pdfPath) === 0) {
                                    throw new \Exception("El archivo PDF temporal no existe o está vacío: {$pdfPath}");
                                }
                                
                                $fileContent = file_get_contents($pdfPath);
                                if (empty($fileContent)) {
                                    throw new \Exception("No se pudo leer el contenido del archivo PDF temporal");
                                }
                                
                                // Guardar en almacenamiento público
                                if (!Storage::disk('public')->put($publicPath . $pdfName, $fileContent)) {
                                    throw new \Exception("No se pudo guardar el archivo en el almacenamiento público");
                                }
                                
                                // Verificar que se haya guardado correctamente
                                if (!Storage::disk('public')->exists($publicPath . $pdfName)) {
                                    throw new \Exception("El archivo no existe en el almacenamiento público después de guardarlo");
                                }
                                
                                Log::info('PDF guardado en almacenamiento público', [
                                    'path' => $publicPath . $pdfName,
                                    'token' => $token
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Error al guardar el PDF en almacenamiento público', [
                                    'error' => $e->getMessage(),
                                    'path' => $publicPath . $pdfName,
                                    'token' => $token
                                ]);
                                throw new \Exception("Error al guardar el PDF en almacenamiento público: {$e->getMessage()}");
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
