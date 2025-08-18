<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Exception;

class DriverService
{
    /**
     * Obtener todos los conductores con eager loading optimizado
     */
    public function getAllDrivers(array $filters = []): Collection
    {
        try {
            $query = UserDriverDetail::with([
                'user:id,name,email,status,access_type,created_at',
                'carrier:id,name,status,document_status'
            ]);

            // Aplicar filtros
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['carrier_id'])) {
                $query->where('carrier_id', $filters['carrier_id']);
            }

            if (!empty($filters['search'])) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                })->orWhere('license_number', 'like', '%' . $filters['search'] . '%');
            }

            if (!empty($filters['license_status'])) {
                $query->where('license_status', $filters['license_status']);
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (Exception $e) {
            Log::error('Error al obtener conductores: ' . $e->getMessage());
            throw new Exception('Error al obtener la lista de conductores');
        }
    }

    /**
     * Obtener un conductor por ID con relaciones
     */
    public function getDriverById(int $driverId): ?UserDriverDetail
    {
        try {
            return UserDriverDetail::with([
                'user:id,name,email,status,access_type,created_at,updated_at',
                'carrier:id,name,status,document_status,address',
                'vehicles:id,driver_id,make,model,year,vin,status',
                'trips:id,driver_id,origin,destination,status,created_at'
            ])->find($driverId);
        } catch (Exception $e) {
            Log::error('Error al obtener conductor por ID: ' . $e->getMessage());
            throw new Exception('Error al obtener los datos del conductor');
        }
    }

    /**
     * Obtener conductores por carrier
     */
    public function getDriversByCarrier(int $carrierId): Collection
    {
        try {
            return UserDriverDetail::with([
                'user:id,name,email,status,access_type',
                'vehicles:id,driver_id,make,model,year,status'
            ])
            ->where('carrier_id', $carrierId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        } catch (Exception $e) {
            Log::error('Error al obtener conductores por carrier: ' . $e->getMessage());
            throw new Exception('Error al obtener los conductores del transportista');
        }
    }

    /**
     * Crear un nuevo conductor con transacción
     */
    public function createDriver(array $data): UserDriverDetail
    {
        DB::beginTransaction();
        
        try {
            // Validar datos requeridos
            $this->validateDriverData($data);

            // Crear usuario si no existe
            $user = null;
            if (!empty($data['user_id'])) {
                $user = User::findOrFail($data['user_id']);
            } else {
                $user = $this->createDriverUser($data['user_data']);
            }

            // Crear detalles del conductor
            $driverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $data['carrier_id'],
                'license_number' => $data['license_number'],
                'license_type' => $data['license_type'] ?? 'CDL-A',
                'license_expiry' => $data['license_expiry'] ?? null,
                'license_status' => $data['license_status'] ?? 'active',
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'emergency_phone' => $data['emergency_phone'] ?? null,
                'hire_date' => $data['hire_date'] ?? now(),
                'status' => $data['status'] ?? 'active'
            ]);

            // Actualizar tipo de acceso del usuario
            $user->update(['access_type' => 'driver']);

            DB::commit();
            Log::info('Conductor creado exitosamente: ' . $driverDetail->id);
            
            return $this->getDriverById($driverDetail->id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear conductor: ' . $e->getMessage());
            throw new Exception('Error al crear el conductor: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar un conductor con transacción
     */
    public function updateDriver(int $driverId, array $data): UserDriverDetail
    {
        DB::beginTransaction();
        
        try {
            $driver = UserDriverDetail::findOrFail($driverId);
            
            // Validar datos
            $this->validateDriverData($data, $driverId);

            // Actualizar detalles del conductor
            $driver->update([
                'license_number' => $data['license_number'] ?? $driver->license_number,
                'license_type' => $data['license_type'] ?? $driver->license_type,
                'license_expiry' => $data['license_expiry'] ?? $driver->license_expiry,
                'license_status' => $data['license_status'] ?? $driver->license_status,
                'phone' => $data['phone'] ?? $driver->phone,
                'address' => $data['address'] ?? $driver->address,
                'emergency_contact' => $data['emergency_contact'] ?? $driver->emergency_contact,
                'emergency_phone' => $data['emergency_phone'] ?? $driver->emergency_phone,
                'status' => $data['status'] ?? $driver->status
            ]);

            // Actualizar datos del usuario si se proporcionan
            if (!empty($data['user_data'])) {
                $driver->user->update([
                    'name' => $data['user_data']['name'] ?? $driver->user->name,
                    'email' => $data['user_data']['email'] ?? $driver->user->email,
                    'status' => $data['user_data']['status'] ?? $driver->user->status
                ]);
            }

            DB::commit();
            Log::info('Conductor actualizado exitosamente: ' . $driverId);
            
            return $this->getDriverById($driverId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar conductor: ' . $e->getMessage());
            throw new Exception('Error al actualizar el conductor: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un conductor (soft delete)
     */
    public function deleteDriver(int $driverId): bool
    {
        DB::beginTransaction();
        
        try {
            $driver = UserDriverDetail::findOrFail($driverId);
            
            // Verificar si tiene viajes activos
            $activeTrips = $this->hasActiveTrips($driverId);
            if ($activeTrips) {
                throw new Exception('No se puede eliminar el conductor porque tiene viajes activos');
            }

            // Soft delete
            $driver->update(['status' => 'inactive']);
            $driver->user->update(['status' => 'inactive']);
            
            DB::commit();
            Log::info('Conductor eliminado exitosamente: ' . $driverId);
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar conductor: ' . $e->getMessage());
            throw new Exception('Error al eliminar el conductor: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de conductores
     */
    public function getDriverStats(int $carrierId = null): array
    {
        try {
            $query = UserDriverDetail::query();
            
            if ($carrierId) {
                $query->where('carrier_id', $carrierId);
            }

            $baseQuery = clone $query;

            return [
                'total' => $baseQuery->count(),
                'active' => (clone $query)->where('status', 'active')->count(),
                'inactive' => (clone $query)->where('status', 'inactive')->count(),
                'suspended' => (clone $query)->where('status', 'suspended')->count(),
                'license_expired' => (clone $query)->where('license_expiry', '<', now())->count(),
                'license_expiring_soon' => (clone $query)
                    ->whereBetween('license_expiry', [now(), now()->addDays(30)])
                    ->count(),
                'recent_hires' => (clone $query)
                    ->where('hire_date', '>=', now()->subDays(30))
                    ->count()
            ];
        } catch (Exception $e) {
            Log::error('Error al obtener estadísticas de conductores: ' . $e->getMessage());
            throw new Exception('Error al obtener las estadísticas');
        }
    }

    /**
     * Obtener conductores con licencias próximas a vencer
     */
    public function getDriversWithExpiringLicenses(int $days = 30): Collection
    {
        try {
            return UserDriverDetail::with([
                'user:id,name,email',
                'carrier:id,name'
            ])
            ->where('status', 'active')
            ->whereBetween('license_expiry', [now(), now()->addDays($days)])
            ->orderBy('license_expiry', 'asc')
            ->get();
        } catch (Exception $e) {
            Log::error('Error al obtener conductores con licencias por vencer: ' . $e->getMessage());
            throw new Exception('Error al obtener los conductores con licencias por vencer');
        }
    }

    /**
     * Validar datos del conductor
     */
    private function validateDriverData(array $data, ?int $driverId = null): void
    {
        // Validar número de licencia único
        if (!empty($data['license_number'])) {
            $query = UserDriverDetail::where('license_number', $data['license_number']);
            if ($driverId) {
                $query->where('id', '!=', $driverId);
            }
            if ($query->exists()) {
                throw new Exception('El número de licencia ya está registrado');
            }
        }

        // Validar que el carrier existe
        if (!empty($data['carrier_id'])) {
            if (!Carrier::where('id', $data['carrier_id'])->where('status', 'active')->exists()) {
                throw new Exception('El transportista especificado no existe o no está activo');
            }
        }
    }

    /**
     * Crear usuario para el conductor
     */
    private function createDriverUser(array $userData): User
    {
        return User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password'] ?? 'driver123'),
            'access_type' => 'driver',
            'status' => 'active'
        ]);
    }

    /**
     * Verificar si el conductor tiene viajes activos
     */
    private function hasActiveTrips(int $driverId): bool
    {
        // Verificar en tabla de viajes si existe
        return DB::table('trips')
            ->where('driver_id', $driverId)
            ->whereIn('status', ['in_progress', 'assigned', 'started'])
            ->exists();
    }
}