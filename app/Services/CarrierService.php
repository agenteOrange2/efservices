<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\User;
use App\Models\UserCarrierDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CarrierService
{
    /**
     * Obtener todos los carriers con eager loading optimizado
     */
    public function getAllCarriers(array $filters = []): Collection|\Illuminate\Pagination\LengthAwarePaginator
    {
        try {
            $query = Carrier::with([
                'membership:id,name,price,description',
                'userCarriers:id,carrier_id,user_id,phone,job_position,status,created_at',
                'userCarriers.user:id,name,email,status,access_type',
                'documents:id,carrier_id,document_type_id,status',
                'documents.documentType:id,name',
                'bankingDetails:id,carrier_id,account_holder_name,account_number,country_code,status,rejection_reason'
            ])
            ->select([
                'id', 'name', 'slug', 'address', 'state', 'zipcode',
                'ein_number', 'dot_number', 'mc_number', 'status',
                'document_status', 'id_plan', 'created_at', 'updated_at'
            ]);

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['document_status'])) {
                $query->where('document_status', $filters['document_status']);
            }

            if (!empty($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('ein_number', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('dot_number', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('mc_number', 'like', '%' . $filters['search'] . '%');
                });
            }

            // Paginación si se especifica
            if (!empty($filters['per_page'])) {
                return $query->orderBy('created_at', 'desc')
                            ->paginate($filters['per_page']);
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (Exception $e) {
            Log::error('Error al obtener carriers: ' . $e->getMessage());
            throw new Exception('Error al obtener la lista de transportistas');
        }
    }

    /**
     * Obtener un carrier por ID con relaciones
     */
    public function getCarrierById(int $carrierId): ?Carrier
    {
        try {
            return Carrier::with([
                'membership:id,name,price,description',
                'userCarriers:id,carrier_id,user_id,phone,job_position,status,created_at',
                'userCarriers.user:id,name,email,status,access_type',
                'vehicles:id,carrier_id,make,model,year,vin,status',
                'userDrivers:id,carrier_id,user_id,status'
            ])->find($carrierId);
        } catch (Exception $e) {
            Log::error('Error al obtener carrier por ID: ' . $e->getMessage());
            throw new Exception('Error al obtener los datos del transportista');
        }
    }

    /**
     * Obtener carrier con todos los detalles para la vista show
     * Optimizado con eager loading eficiente y mejor manejo de errores
     */
    public function getCarrierWithDetails(int $carrierId): array
    {
        try {
            // Validar que el carrier existe antes de procesar
            if (!Carrier::where('id', $carrierId)->exists()) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                    "Carrier with ID {$carrierId} not found"
                );
            }

            // Obtener carrier con todas las relaciones necesarias en una sola consulta optimizada
            $carrier = Carrier::with([
                'membership:id,name,price,description',
                'bankingDetails:id,carrier_id,account_holder_name,account_number,country_code,status,rejection_reason,created_at',
                'userCarriers:id,carrier_id,user_id,phone,job_position,status,created_at',
                'userCarriers.user:id,name,email,status,access_type',
                'userDrivers:id,carrier_id,user_id,status,created_at',
                'userDrivers.user:id,name,email,status',
                'documents:id,carrier_id,document_type_id,status,date,created_at',
                'documents.documentType:id,name,requirement,default_file_path'
            ])->findOrFail($carrierId);

            // Extraer relaciones ya cargadas para evitar consultas adicionales
            $userCarriers = $carrier->userCarriers ?? collect();
            $drivers = $carrier->userDrivers ?? collect();
            $documents = $carrier->documents ?? collect();

            // Validar que las relaciones críticas existen
            if (!$carrier->membership) {
                Log::warning('Carrier without membership plan', ['carrier_id' => $carrierId]);
            }

            // Filtrar documentos por estado de manera eficiente
            $documentsByStatus = $documents->groupBy('status');
            $pendingDocuments = $documentsByStatus->get('pending', collect());
            $approvedDocuments = $documentsByStatus->get('approved', collect());
            $rejectedDocuments = $documentsByStatus->get('rejected', collect());

            // Obtener tipos de documentos faltantes de manera optimizada
            $existingDocumentTypeIds = $documents->pluck('document_type_id')->unique()->toArray();
            $missingDocumentTypes = collect();
            
            if (!empty($existingDocumentTypeIds)) {
                $missingDocumentTypes = \App\Models\DocumentType::select('id', 'name', 'requirement')
                    ->whereNotIn('id', $existingDocumentTypeIds)
                    ->orderBy('name')
                    ->get();
            } else {
                // Si no hay documentos, todos los tipos están faltantes
                $missingDocumentTypes = \App\Models\DocumentType::select('id', 'name', 'requirement')
                    ->orderBy('name')
                    ->get();
            }

            // Calcular estadísticas adicionales
            $stats = [
                'total_users' => $userCarriers->count(),
                'active_users' => $userCarriers->where('status', 1)->count(),
                'total_drivers' => $drivers->count(),
                'active_drivers' => $drivers->where('status', 1)->count(),
                'total_documents' => $documents->count(),
                'pending_documents_count' => $pendingDocuments->count(),
                'approved_documents_count' => $approvedDocuments->count(),
                'rejected_documents_count' => $rejectedDocuments->count(),
                'missing_documents_count' => $missingDocumentTypes->count(),
                'document_completion_percentage' => $documents->count() > 0 
                    ? round(($approvedDocuments->count() / $documents->count()) * 100, 1) 
                    : 0
            ];

            Log::info('Carrier details loaded successfully', [
                'carrier_id' => $carrierId,
                'stats' => $stats
            ]);

            return [
                'carrier' => $carrier,
                'userCarriers' => $userCarriers,
                'drivers' => $drivers,
                'documents' => $documents,
                'pendingDocuments' => $pendingDocuments,
                'approvedDocuments' => $approvedDocuments,
                'rejectedDocuments' => $rejectedDocuments,
                'missingDocumentTypes' => $missingDocumentTypes,
                'stats' => $stats
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Carrier not found', [
                'carrier_id' => $carrierId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("El transportista con ID {$carrierId} no fue encontrado");
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles completos del carrier', [
                'carrier_id' => $carrierId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw new Exception('Error al obtener los detalles del transportista: ' . $e->getMessage());
        }
    }

    /**
     * Crear un nuevo carrier con transacción
     */
    public function createCarrier(array $data, $logoFile = null): Carrier
    {
        DB::beginTransaction();
        
        try {
            // Validar datos requeridos
            $this->validateCarrierData($data);

            // Formatear EIN antes de crear el registro
            if (!empty($data['ein_number'])) {
                $data['ein_number'] = $this->formatEIN($data['ein_number']);
            }

            // Crear el carrier
            $carrier = Carrier::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'state' => $data['state'],
                'zipcode' => $data['zipcode'],
                'ein_number' => $data['ein_number'],
                'dot_number' => $data['dot_number'] ?? null,
                'mc_number' => $data['mc_number'] ?? null,
                'state_dot' => $data['state_dot'] ?? null,
                'ifta_account' => $data['ifta_account'] ?? null,
                'id_plan' => $data['id_plan'],
                'status' => $data['status'] ?? Carrier::STATUS_PENDING,
                'document_status' => $data['document_status'] ?? Carrier::DOCUMENT_STATUS_PENDING
            ]);

            // Manejar el archivo de logo si se proporciona
            if ($logoFile) {
                $carrier->addMediaFromRequest('logo_carrier')
                    ->toMediaCollection('logo_carrier');
            }

            // Si se proporciona información del usuario, crear la relación
            if (!empty($data['user_data'])) {
                $this->createCarrierUserRelation($carrier->id, $data['user_data']);
            }

            DB::commit();
            Log::info('Carrier creado exitosamente: ' . $carrier->id);
            
            return $this->getCarrierById($carrier->id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear carrier: ' . $e->getMessage());
            throw new Exception('Error al crear el transportista: ' . $e->getMessage());
        }
    }



    /**
     * Actualizar un carrier con transacción
     */
    public function updateCarrier(int $carrierId, array $data, $logoFile = null): Carrier
    {
        DB::beginTransaction();
        
        try {
            $carrier = Carrier::findOrFail($carrierId);
            
            // Validar datos
            $this->validateCarrierData($data, $carrierId);

            // Formatear EIN antes de actualizar el registro
            if (!empty($data['ein_number'])) {
                $data['ein_number'] = $this->formatEIN($data['ein_number']);
            }

            // Actualizar carrier
            $carrier->update([
                'name' => $data['name'] ?? $carrier->name,
                'address' => $data['address'] ?? $carrier->address,
                'state' => $data['state'] ?? $carrier->state,
                'zipcode' => $data['zipcode'] ?? $carrier->zipcode,
                'ein_number' => $data['ein_number'] ?? $carrier->ein_number,
                'dot_number' => $data['dot_number'] ?? $carrier->dot_number,
                'mc_number' => $data['mc_number'] ?? $carrier->mc_number,
                'state_dot' => $data['state_dot'] ?? $carrier->state_dot,
                'ifta_account' => $data['ifta_account'] ?? $carrier->ifta_account,
                'id_plan' => $data['id_plan'] ?? $carrier->id_plan,
                'status' => $data['status'] ?? $carrier->status,
                'document_status' => $data['document_status'] ?? $carrier->document_status,
                'referrer_token' => $data['referrer_token'] ?? $carrier->referrer_token
            ]);

            // Manejar el archivo de logo si se proporciona
            if ($logoFile) {
                $carrier->addMediaFromRequest('logo_carrier')
                    ->toMediaCollection('logo_carrier');
            }

            DB::commit();
            Log::info('Carrier actualizado exitosamente: ' . $carrierId);
            
            return $this->getCarrierById($carrierId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar carrier: ' . $e->getMessage());
            throw new Exception('Error al actualizar el transportista: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un carrier (soft delete)
     */
    public function deleteCarrier(int $carrierId): bool
    {
        DB::beginTransaction();
        
        try {
            $carrier = Carrier::findOrFail($carrierId);
            
            // Verificar si tiene relaciones activas
            $activeRelations = $this->hasActiveRelations($carrierId);
            if ($activeRelations) {
                throw new Exception('No se puede eliminar el transportista porque tiene relaciones activas');
            }

            // Soft delete
            $carrier->update(['status' => Carrier::STATUS_INACTIVE]);
            
            DB::commit();
            Log::info('Carrier eliminado exitosamente: ' . $carrierId);
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar carrier: ' . $e->getMessage());
            throw new Exception('Error al eliminar el transportista: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de carriers
     */
    public function getCarrierStats(): array
    {
        try {
            return [
                'total' => Carrier::count(),
                'active' => Carrier::where('status', Carrier::STATUS_ACTIVE)->count(),
                'inactive' => Carrier::where('status', Carrier::STATUS_INACTIVE)->count(),
                'pending' => Carrier::where('status', Carrier::STATUS_PENDING)->count(),
                'pending_validation' => Carrier::where('status', Carrier::STATUS_PENDING_VALIDATION)->count(),
                'pending_documents' => Carrier::where('document_status', Carrier::DOCUMENT_STATUS_PENDING)->count(),
                'in_progress_documents' => Carrier::where('document_status', Carrier::DOCUMENT_STATUS_IN_PROGRESS)->count(),
                'completed_documents' => Carrier::where('document_status', Carrier::DOCUMENT_STATUS_COMPLETED)->count(),
                'recent' => Carrier::where('created_at', '>=', now()->subDays(30))->count()
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas de carriers: ' . $e->getMessage());
            throw new Exception('Error al obtener las estadísticas');
        }
    }

    /**
     * Validar datos del carrier
     */
    private function validateCarrierData(array &$data, ?int $carrierId = null): void
    {
        // Validar campos requeridos
        $requiredFields = ['name', 'address', 'state', 'zipcode', 'ein_number'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }

        // Formatear EIN antes de validar
        if (!empty($data['ein_number'])) {
            $data['ein_number'] = $this->formatEIN($data['ein_number']);
        }

        // Validar formato EIN (XX-XXXXXXX)
        if (!empty($data['ein_number']) && !preg_match('/^\d{2}-\d{7}$/', $data['ein_number'])) {
            throw new Exception('El formato del número EIN debe ser XX-XXXXXXX');
        }

        // Validar EIN único
        if (!empty($data['ein_number'])) {
            $query = Carrier::where('ein_number', $data['ein_number']);
            if ($carrierId) {
                $query->where('id', '!=', $carrierId);
            }
            if ($query->exists()) {
                throw new Exception('El número EIN ya está registrado');
            }
        }

        // Validar DOT único si se proporciona
        if (!empty($data['dot_number'])) {
            $query = Carrier::where('dot_number', $data['dot_number']);
            if ($carrierId) {
                $query->where('id', '!=', $carrierId);
            }
            if ($query->exists()) {
                throw new Exception('El número DOT ya está registrado');
            }
        }

        // Validar MC único si se proporciona
        if (!empty($data['mc_number'])) {
            $query = Carrier::where('mc_number', $data['mc_number']);
            if ($carrierId) {
                $query->where('id', '!=', $carrierId);
            }
            if ($query->exists()) {
                throw new Exception('El número MC ya está registrado');
            }
        }

        // Validar estado válido
        if (isset($data['status']) && !in_array($data['status'], [
            Carrier::STATUS_INACTIVE,
            Carrier::STATUS_ACTIVE,
            Carrier::STATUS_PENDING,
            Carrier::STATUS_PENDING_VALIDATION
        ])) {
            throw new Exception('Estado de carrier inválido');
        }
    }

    /**
     * Crear relación usuario-carrier
     */
    private function createCarrierUserRelation(int $carrierId, array $userData): void
    {
        UserCarrierDetail::create([
            'carrier_id' => $carrierId,
            'user_id' => $userData['user_id'],
            'phone' => $userData['phone'] ?? null,
            'job_position' => $userData['job_position'] ?? 'owner',
            'status' => 'active'
        ]);
    }

    /**
     * Verificar si el carrier tiene relaciones activas
     */
    private function hasActiveRelations(int $carrierId): bool
    {
        // Verificar usuarios activos
        $activeUsers = UserCarrierDetail::where('carrier_id', $carrierId)
            ->where('status', 'active')
            ->exists();

        // Verificar vehículos activos (si existe la tabla)
        $activeVehicles = DB::table('vehicles')
            ->where('carrier_id', $carrierId)
            ->where('status', 'active')
            ->exists();

        return $activeUsers || $activeVehicles;
    }

    /**
     * Format EIN number to XX-XXXXXXX format
     */
    private function formatEIN($ein)
    {
        if (empty($ein)) {
            return $ein;
        }

        // Remove all non-numeric characters
        $cleanEin = preg_replace('/[^0-9]/', '', $ein);
        
        // If we have exactly 9 digits, format as XX-XXXXXXX
        if (strlen($cleanEin) === 9) {
            return substr($cleanEin, 0, 2) . '-' . substr($cleanEin, 2);
        }
        
        // Return the cleaned value if it doesn't match expected length
        return strtoupper(trim($ein));
    }
}