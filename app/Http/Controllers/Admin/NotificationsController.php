<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\SendsCustomNotifications;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsController extends Controller
{
    use SendsCustomNotifications;

    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtros básicos
        $filter = $request->input('filter', 'all');
        $type = $request->input('type');
        
        // Query inicial - usando el sistema de notificaciones de Laravel
        if ($filter === 'read') {
            $notifications = $user->readNotifications();
        } elseif ($filter === 'unread') {
            $notifications = $user->unreadNotifications();
        } else {
            $notifications = $user->notifications();
        }
        
        // Filtrar por tipo si es necesario
        if ($type) {
            $notifications = $notifications->where('type', 'like', "%{$type}%");
        }
        
        // Paginar los resultados
        $notifications = $notifications->paginate(15);
        
        // Recopilar tipos únicos de notificaciones para el filtro
        $notificationTypes = $user->notifications()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->map(function($type) {
                // Extraer el nombre de la clase de la ruta completa
                $parts = explode('\\', $type);
                return [
                    'id' => $type,
                    'name' => end($parts)
                ];
            });
        
        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'notificationTypes' => $notificationTypes,
            'currentFilter' => $filter,
            'currentType' => $type
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = DatabaseNotification::where('notifiable_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->markAsRead();
        
        return redirect()->back()
            ->with($this->sendNotification(
                'success',
                'Notification marked as read'
            ));
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread($id)
    {
        $notification = DatabaseNotification::where('notifiable_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->markAsUnread();
        
        return redirect()->back()
            ->with($this->sendNotification(
                'success',
                'Notification marked as unread'
            ));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        
        return redirect()->back()
            ->with($this->sendNotification(
                'success',
                'All notifications marked as read'
            ));
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = DatabaseNotification::where('notifiable_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        $notification->delete();
        
        return redirect()->back()
            ->with($this->sendNotification(
                'success',
                'Notification deleted successfully'
            ));
    }

    /**
     * Delete all notifications matching the current filter.
     */
    public function deleteAll(Request $request)
    {
        $user = Auth::user();
        $filter = $request->input('filter', 'all');
        
        if ($filter === 'read') {
            $user->readNotifications()->delete();
        } elseif ($filter === 'unread') {
            $user->unreadNotifications()->delete();
        } else {
            $user->notifications()->delete();
        }
        
        return redirect()->route('admin.notifications.index')
            ->with($this->sendNotification(
                'success',
                'Notifications deleted successfully'
            ));
    }
}