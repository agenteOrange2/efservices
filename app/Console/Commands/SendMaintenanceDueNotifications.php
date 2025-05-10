<?php

namespace App\Console\Commands;

use App\Models\Admin\Vehicle\VehicleMaintenance;
use App\Models\User;
use App\Notifications\Admin\Vehicle\MaintenanceDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendMaintenanceDueNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:send-notifications {--days=14 : Días de anticipación para notificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía notificaciones para mantenimientos próximos a vencer';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Buscando mantenimientos que vencen en los próximos {$days} días...");
        
        // Obtener mantenimientos próximos a vencer
        $maintenanceItems = VehicleMaintenance::upcoming($days)
            ->where('status', false)
            ->get();
            
        $this->info("Se encontraron {$maintenanceItems->count()} mantenimientos próximos a vencer.");
        
        if ($maintenanceItems->isEmpty()) {
            return 0;
        }
        
        // Obtener usuarios administradores y supervisores para notificar
        $admins = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'supervisor']);
        })->get();
        
        $this->info("Encontrados {$admins->count()} administradores/supervisores para notificar.");
        
        $notificationCount = 0;
        
        foreach ($maintenanceItems as $maintenance) {
            $daysRemaining = now()->diffInDays($maintenance->next_service_date);
            $vehicle = $maintenance->vehicle;
            
            // Notificar solo si quedan exactamente 14 o 7 días
            if ($daysRemaining == 14 || $daysRemaining == 7) {
                $usersToNotify = collect();
                
                // 1. Notificar a administradores y supervisores
                $usersToNotify = $usersToNotify->merge($admins);
                
                // 2. Notificar al carrier (si existe)
                if ($vehicle->carrier) {
                    $carrierUsers = User::whereHas('carrierDetails', function($query) use ($vehicle) {
                        $query->where('carrier_id', $vehicle->carrier_id);
                    })->get();
                    
                    $this->info("Encontrados {$carrierUsers->count()} usuarios del carrier para el vehículo {$vehicle->license_plate}");
                    $usersToNotify = $usersToNotify->merge($carrierUsers);
                }
                
                // 3. Notificar al conductor (si existe)
                if ($vehicle->driver && $vehicle->driver->user) {
                    $this->info("Encontrado conductor {$vehicle->driver->user->name} para el vehículo {$vehicle->license_plate}");
                    $usersToNotify->push($vehicle->driver->user);
                }
                
                // Eliminar duplicados
                $usersToNotify = $usersToNotify->unique('id');
                
                // Enviar notificaciones
                foreach ($usersToNotify as $user) {
                    Notification::send($user, new MaintenanceDueNotification($maintenance, $daysRemaining));
                    $notificationCount++;
                }
                
                $this->info("Notificación enviada para el mantenimiento ID: {$maintenance->id}, Vehículo: {$vehicle->license_plate}, Días restantes: {$daysRemaining}, Usuarios notificados: {$usersToNotify->count()}");
            }
        }
        
        $this->info("Se enviaron {$notificationCount} notificaciones en total.");
        Log::info("Comando maintenance:send-notifications ejecutado. Se enviaron {$notificationCount} notificaciones.");
        
        return 0;
    }
}
