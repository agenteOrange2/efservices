<?php

namespace App\Services;

use App\Models\TempDriverUpload;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class LicenseMigrationService
{
    /**
     * Migrate temporary license files to permanent driver record
     *
     * @param string $sessionId
     * @param UserDriverDetail $driverDetail
     * @return array
     * @throws Exception
     */
    public function migrateLicenseFiles(string $sessionId, UserDriverDetail $driverDetail): array
    {
        Log::info('LicenseMigrationService: Iniciando migración de archivos', [
            'session_id' => $sessionId,
            'driver_id' => $driverDetail->id,
            'user_id' => $driverDetail->user_id
        ]);

        $results = [
            'success' => false,
            'migrated_files' => [],
            'errors' => [],
            'temp_files_cleaned' => 0
        ];

        DB::beginTransaction();

        try {
            // Obtener archivos temporales de la sesión
            $tempUploads = TempDriverUpload::bySession($sessionId)
                ->notExpired()
                ->with('media')
                ->get();

            if ($tempUploads->isEmpty()) {
                Log::warning('LicenseMigrationService: No se encontraron archivos temporales', [
                    'session_id' => $sessionId
                ]);
                
                $results['success'] = true;
                $results['message'] = 'No temporary files found to migrate';
                DB::commit();
                return $results;
            }

            Log::info('LicenseMigrationService: Archivos temporales encontrados', [
                'session_id' => $sessionId,
                'count' => $tempUploads->count(),
                'file_types' => $tempUploads->pluck('file_type')->toArray()
            ]);

            // Migrar cada archivo temporal
            foreach ($tempUploads as $tempUpload) {
                try {
                    $migrationResult = $this->migrateSingleFile($tempUpload, $driverDetail);
                    $results['migrated_files'][] = $migrationResult;
                    
                    Log::info('LicenseMigrationService: Archivo migrado exitosamente', [
                        'temp_upload_id' => $tempUpload->id,
                        'file_type' => $tempUpload->file_type,
                        'new_media_id' => $migrationResult['new_media_id'] ?? null
                    ]);
                    
                } catch (Exception $e) {
                    Log::error('LicenseMigrationService: Error migrando archivo individual', [
                        'temp_upload_id' => $tempUpload->id,
                        'file_type' => $tempUpload->file_type,
                        'error' => $e->getMessage()
                    ]);
                    
                    $results['errors'][] = [
                        'temp_upload_id' => $tempUpload->id,
                        'file_type' => $tempUpload->file_type,
                        'error' => $e->getMessage()
                    ];
                }
            }

            // Limpiar archivos temporales después de la migración
            $cleanupResult = $this->cleanupTempFiles($sessionId);
            $results['temp_files_cleaned'] = $cleanupResult['cleaned_count'];

            // Verificar si hubo errores críticos
            if (empty($results['errors']) || count($results['migrated_files']) > 0) {
                $results['success'] = true;
                $results['message'] = 'Migration completed successfully';
                DB::commit();
                
                Log::info('LicenseMigrationService: Migración completada exitosamente', [
                    'session_id' => $sessionId,
                    'driver_id' => $driverDetail->id,
                    'migrated_count' => count($results['migrated_files']),
                    'error_count' => count($results['errors'])
                ]);
            } else {
                throw new Exception('All file migrations failed');
            }

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('LicenseMigrationService: Error crítico en migración', [
                'session_id' => $sessionId,
                'driver_id' => $driverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['success'] = false;
            $results['message'] = 'Migration failed: ' . $e->getMessage();
            $results['errors'][] = [
                'type' => 'critical',
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    /**
     * Migrate a single temporary file to permanent storage
     *
     * @param TempDriverUpload $tempUpload
     * @param UserDriverDetail $driverDetail
     * @return array
     * @throws Exception
     */
    private function migrateSingleFile(TempDriverUpload $tempUpload, UserDriverDetail $driverDetail): array
    {
        $tempMedia = $tempUpload->getFirstMedia($tempUpload->file_type);
        
        if (!$tempMedia) {
            throw new Exception("No media found for temp upload ID: {$tempUpload->id}");
        }

        // Verificar si el archivo temporal aún existe
        if (!file_exists($tempMedia->getPath())) {
            throw new Exception("Temporary file not found: {$tempMedia->getPath()}");
        }

        // Eliminar archivo existente del mismo tipo si existe
        $existingMedia = $driverDetail->getFirstMedia($tempUpload->file_type);
        if ($existingMedia) {
            Log::info('LicenseMigrationService: Eliminando archivo existente', [
                'driver_id' => $driverDetail->id,
                'file_type' => $tempUpload->file_type,
                'existing_media_id' => $existingMedia->id
            ]);
            
            $existingMedia->delete();
        }

        // Generar unique_id para el archivo definitivo
        $uniqueId = Str::uuid()->toString();
        
        // Copiar archivo temporal a ubicación definitiva
        $newMedia = $driverDetail->addMediaFromExistingFile($tempMedia->getPath())
            ->usingName($tempUpload->original_name)
            ->usingFileName($tempMedia->file_name)
            ->withCustomProperties(array_merge(
                $tempMedia->custom_properties ?? [],
                ['unique_id' => $uniqueId]
            ))
            ->toMediaCollection($tempUpload->file_type);

        Log::info('LicenseMigrationService: Archivo copiado a ubicación definitiva', [
            'temp_media_id' => $tempMedia->id,
            'new_media_id' => $newMedia->id,
            'temp_path' => $tempMedia->getPath(),
            'new_path' => $newMedia->getPath(),
            'unique_id' => $uniqueId
        ]);

        return [
            'temp_upload_id' => $tempUpload->id,
            'temp_media_id' => $tempMedia->id,
            'new_media_id' => $newMedia->id,
            'file_type' => $tempUpload->file_type,
            'original_name' => $tempUpload->original_name,
            'unique_id' => $uniqueId,
            'temp_path' => $tempMedia->getPath(),
            'new_path' => $newMedia->getPath(),
            'new_url' => $newMedia->getUrl()
        ];
    }

    /**
     * Clean up temporary files for a session
     *
     * @param string $sessionId
     * @return array
     */
    public function cleanupTempFiles(string $sessionId): array
    {
        Log::info('LicenseMigrationService: Iniciando limpieza de archivos temporales', [
            'session_id' => $sessionId
        ]);

        $result = [
            'cleaned_count' => 0,
            'errors' => []
        ];

        try {
            $tempUploads = TempDriverUpload::bySession($sessionId)->get();
            
            foreach ($tempUploads as $tempUpload) {
                try {
                    // Eliminar archivos de media asociados
                    $tempUpload->clearMediaCollection($tempUpload->file_type);
                    
                    // Eliminar registro temporal
                    $tempUpload->delete();
                    
                    $result['cleaned_count']++;
                    
                    Log::info('LicenseMigrationService: Archivo temporal limpiado', [
                        'temp_upload_id' => $tempUpload->id,
                        'file_type' => $tempUpload->file_type
                    ]);
                    
                } catch (Exception $e) {
                    Log::error('LicenseMigrationService: Error limpiando archivo temporal', [
                        'temp_upload_id' => $tempUpload->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    $result['errors'][] = [
                        'temp_upload_id' => $tempUpload->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
        } catch (Exception $e) {
            Log::error('LicenseMigrationService: Error crítico en limpieza', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            $result['errors'][] = [
                'type' => 'critical',
                'error' => $e->getMessage()
            ];
        }

        Log::info('LicenseMigrationService: Limpieza completada', [
            'session_id' => $sessionId,
            'cleaned_count' => $result['cleaned_count'],
            'error_count' => count($result['errors'])
        ]);

        return $result;
    }

    /**
     * Clean up expired temporary files (for scheduled cleanup)
     *
     * @return array
     */
    public function cleanupExpiredFiles(): array
    {
        Log::info('LicenseMigrationService: Iniciando limpieza de archivos expirados');

        $result = [
            'cleaned_count' => 0,
            'errors' => []
        ];

        try {
            $expiredUploads = TempDriverUpload::where('expires_at', '<', now())->get();
            
            Log::info('LicenseMigrationService: Archivos expirados encontrados', [
                'count' => $expiredUploads->count()
            ]);
            
            foreach ($expiredUploads as $tempUpload) {
                try {
                    // Eliminar archivos de media asociados
                    $tempUpload->clearMediaCollection($tempUpload->file_type);
                    
                    // Eliminar registro temporal
                    $tempUpload->delete();
                    
                    $result['cleaned_count']++;
                    
                    Log::info('LicenseMigrationService: Archivo expirado limpiado', [
                        'temp_upload_id' => $tempUpload->id,
                        'session_id' => $tempUpload->session_id,
                        'file_type' => $tempUpload->file_type,
                        'expired_at' => $tempUpload->expires_at
                    ]);
                    
                } catch (Exception $e) {
                    Log::error('LicenseMigrationService: Error limpiando archivo expirado', [
                        'temp_upload_id' => $tempUpload->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    $result['errors'][] = [
                        'temp_upload_id' => $tempUpload->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
        } catch (Exception $e) {
            Log::error('LicenseMigrationService: Error crítico en limpieza de expirados', [
                'error' => $e->getMessage()
            ]);
            
            $result['errors'][] = [
                'type' => 'critical',
                'error' => $e->getMessage()
            ];
        }

        Log::info('LicenseMigrationService: Limpieza de expirados completada', [
            'cleaned_count' => $result['cleaned_count'],
            'error_count' => count($result['errors'])
        ]);

        return $result;
    }

    /**
     * Validate that all required license files are present for a session
     *
     * @param string $sessionId
     * @return array
     */
    public function validateSessionFiles(string $sessionId): array
    {
        $tempUploads = TempDriverUpload::bySession($sessionId)
            ->notExpired()
            ->get();

        $fileTypes = $tempUploads->pluck('file_type')->toArray();
        $requiredTypes = ['license_front', 'license_back'];
        $missingTypes = array_diff($requiredTypes, $fileTypes);

        return [
            'valid' => empty($missingTypes),
            'present_files' => $fileTypes,
            'missing_files' => $missingTypes,
            'file_count' => count($fileTypes)
        ];
    }

    /**
     * Get migration status for a session
     *
     * @param string $sessionId
     * @return array
     */
    public function getMigrationStatus(string $sessionId): array
    {
        $tempUploads = TempDriverUpload::bySession($sessionId)->get();
        $validation = $this->validateSessionFiles($sessionId);

        return [
            'session_id' => $sessionId,
            'temp_files_count' => $tempUploads->count(),
            'expired_files_count' => $tempUploads->filter(fn($upload) => $upload->isExpired())->count(),
            'ready_for_migration' => $validation['valid'] && $tempUploads->count() > 0,
            'validation' => $validation
        ];
    }
}