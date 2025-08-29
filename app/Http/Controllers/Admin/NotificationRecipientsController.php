<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationRecipientsController extends Controller
{
    public function index()
    {
        $recipients = NotificationRecipient::with('user')
            ->orderBy('notification_type')
            ->orderBy('created_at', 'desc')
            ->get();

        $users = User::select('id', 'name', 'email')
            ->whereDoesntHave('roles', function($query) {
                $query->where('name', 'superadmin');
            })
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ];
            });
        
        return view('admin.notification-recipients.index', compact('recipients', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_type' => 'required|in:user_carrier,carrier_registered',
            'recipient_type' => 'required|in:user,email',
            'user_id' => 'required_if:recipient_type,user|exists:users,id',
            'email' => 'required_if:recipient_type,email|email',
            'name' => 'required_if:recipient_type,email|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar si ya existe el destinatario
        $exists = NotificationRecipient::where('notification_type', $request->notification_type)
            ->where(function($query) use ($request) {
                if ($request->recipient_type === 'user') {
                    $query->where('user_id', $request->user_id);
                } else {
                    $query->where('email', $request->email);
                }
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Este destinatario ya existe para este tipo de notificación'
            ], 422);
        }

        $recipient = NotificationRecipient::create($request->all());
        $recipient->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Destinatario agregado correctamente',
            'recipient' => $recipient
        ]);
    }

    public function destroy(NotificationRecipient $recipient)
    {
        $recipient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Destinatario eliminado correctamente'
        ]);
    }

    public function toggle(NotificationRecipient $recipient)
    {
        $recipient->update([
            'is_active' => !$recipient->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $recipient->is_active ? 'Destinatario activado' : 'Destinatario desactivado',
            'is_active' => $recipient->is_active
        ]);
    }

    public function getUsers(Request $request)
    {
        $search = $request->get('search', '');
        
        $users = User::whereDoesntHave('roles', function($query) {
                $query->where('name', 'superadmin');
            })
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
