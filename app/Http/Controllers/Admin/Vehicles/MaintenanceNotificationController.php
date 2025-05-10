<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\VehicleMaintenance;
use App\Models\User;
use App\Notifications\Admin\Vehicle\MaintenanceDueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MaintenanceNotificationController extends Controller
{
    /**
     * Envía notificaciones de prueba para mantenimientos próximos a vencer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendTestNotification(Request $request)
    {
        $user = Auth::user();
        $maintenanceId = $request->input('maintenance_id');
        $days = $request->input('days', 14);
        
        $maintenance = VehicleMaintenance::findOrFail($maintenanceId);
        
        // Enviar notificación de prueba
        Notification::send($user, new MaintenanceDueNotification($maintenance, $days));
        
        return redirect()->back()->with('success', 'Notificación de prueba enviada correctamente.');
    }
    
    /**
     * Envía notificaciones de mantenimiento a todos los usuarios administradores.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendNotificationsToAll(Request $request)
    {
        $maintenanceId = $request->input('maintenance_id');
        $days = $request->input('days', 14);
        
        $maintenance = VehicleMaintenance::findOrFail($maintenanceId);
        
        // Obtener usuarios administradores y supervisores
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['admin', 'supervisor']);
        })->get();
        
        // Enviar notificaciones
        Notification::send($users, new MaintenanceDueNotification($maintenance, $days));
        
        return redirect()->back()->with('success', 'Notificaciones enviadas a todos los administradores.');
    }
    
    /**
     * Marca una notificación específica como leída.
     *
     * @param  string  $notificationId
     * @return \Illuminate\Http\Response
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'notifications')) {
            $notification = $user->notifications()->where('id', $notificationId)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        }
        
        return redirect()->back();
    }
    
    /**
     * Marca todas las notificaciones como leídas.
     *
     * @return \Illuminate\Http\Response
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'unreadNotifications')) {
            $user->unreadNotifications->markAsRead();
        }
        
        return redirect()->back();
    }
}
