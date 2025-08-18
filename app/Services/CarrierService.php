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
    public function getAllCarriers(array $filters = []): Collection
    {
        try {
            $query = Carrier::with([
                'membership:id,name,price',
                'userCarriers:id,carrier_id,user_id,phone,job_position,status',
                'userCarriers.user:id,name,email,status'
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
                      ->orWhere('dot_number', 'like', '%' . $filters['search'] . '%');
                });
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
                'drivers:id,carrier_id,user_id,license_number,status'
            ])->find($carrierId);
        } catch (Exception $e) {
            Log::error('Error al obtener carrier por ID: ' . $e->getMessage());
            throw new Exception('Error al obtener los datos del transportista');
        }
    }

    /**
     * Crear un nuevo carrier con transacción
     */
    public function createCarrier(array $data): Carrier
    {
        DB::beginTransaction();
        
        try {
            // Validar datos requeridos
            $this->validateCarrierData($data);

            // Crear el carrier
            $carrier = Carrier::create([
                'name' => $data['name'],
                'address' => $data['address'],
                'ein_number' => $data['ein_number'],
                'dot_number' => $data['dot_number'] ?? null,
                'mc_number' => $data['mc_number'] ?? null,
                'id_plan' => $data['id_plan'],
                'status' => $data['status'] ?? 'active',
                'document_status' => $data['document_status'] ?? 'pending'
            ]);

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
    public function updateCarrier(int $carrierId, array $data): Carrier
    {
        DB::beginTransaction();
        
        try {
            $carrier = Carrier::findOrFail($carrierId);
            
            // Validar datos
            $this->validateCarrierData($data, $carrierId);

            // Actualizar carrier
            $carrier->update([
                'name' => $data['name'] ?? $carrier->name,
                'address' => $data['address'] ?? $carrier->address,
                'ein_number' => $data['ein_number'] ?? $carrier->ein_number,
                'dot_number' => $data['dot_number'] ?? $carrier->dot_number,
                'mc_number' => $data['mc_number'] ?? $carrier->mc_number,
                'id_plan' => $data['id_plan'] ?? $carrier->id_plan,
                'status' => $data['status'] ?? $carrier->status,
                'document_status' => $data['document_status'] ?? $carrier->document_status
            ]);

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
            $carrier->update(['status' => 'inactive']);
            
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
                'active' => Carrier::where('status', 'active')->count(),
                'inactive' => Carrier::where('status', 'inactive')->count(),
                'pending_documents' => Carrier::where('document_status', 'pending')->count(),
                'approved_documents' => Carrier::where('document_status', 'approved')->count(),
                'rejected_documents' => Carrier::where('document_status', 'rejected')->count(),
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
    private function validateCarrierData(array $data, ?int $carrierId = null): void
    {
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
}