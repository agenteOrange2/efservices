<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Models\NotificationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationSettingsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    /**
     * Mostrar dashboard de notificaciones
     */
    public function dashboard(Request $request): View
    {
        $filters = $request->only(['event_type', 'status', 'date_from', 'date_to']);
        
        // Obtener estadísticas
        $stats = $this->notificationService->getNotificationStats();
        
        // Obtener logs con filtros
        $logs = $this->notificationService->getNotificationLogs($filters, 15);
        
        return view('admin.notifications.dashboard', compact('stats', 'logs', 'filters'));
    }

    /**
     * Mostrar la página de configuración de notificaciones
     */
    public function index(): View
    {
        $settings = $this->notificationService->getNotificationSettings();
        
        return view('admin.notification-settings.index', compact('settings'));
    }

    /**
     * Actualizar una configuración de notificación específica
     */
    public function update(Request $request, string $eventType): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'recipients' => 'array',
            'recipients.*' => 'email'
        ]);

        try {
            $setting = $this->notificationService->updateNotificationSetting(
                $eventType,
                $request->only(['enabled', 'email_enabled', 'sms_enabled', 'push_enabled', 'recipients'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada correctamente',
                'setting' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración específica via AJAX
     */
    public function show(string $eventType): JsonResponse
    {
        try {
            $setting = NotificationSetting::where('event_type', $eventType)->first();
            
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuración no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'setting' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar logs de notificaciones
     */
    public function logs(Request $request): View
    {
        $filters = $request->only(['event_type', 'status', 'date_from', 'date_to']);
        $logs = $this->notificationService->getNotificationLogs($filters, 20);
        
        return view('admin.notification-settings.logs', compact('logs', 'filters'));
    }

    /**
     * Obtener logs via AJAX para datatables
     */
    public function getLogsData(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['event_type', 'status', 'date_from', 'date_to']);
            $logs = $this->notificationService->getNotificationLogs($filters, $request->get('length', 10));
            
            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $logs->total(),
                'recordsFiltered' => $logs->total(),
                'data' => $logs->items()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al cargar los logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probar envío de notificación
     */
    public function testNotification(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string',
            'test_email' => 'required|email'
        ]);

        try {
            // Simular datos de prueba según el tipo de evento
            $testData = $this->getTestDataForEvent($request->event_type);
            
            // Enviar notificación de prueba
            $result = $this->notificationService->sendCarrierNotification(
                $request->event_type,
                $testData['user'],
                $testData['carrier'],
                $testData['data'],
                [$request->test_email] // Override recipients for test
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificación de prueba enviada correctamente',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación de prueba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar datos de prueba según el tipo de evento
     */
    private function getTestDataForEvent(string $eventType): array
    {
        // Crear datos simulados para testing
        $testUser = (object) [
            'id' => 999,
            'name' => 'Usuario de Prueba',
            'email' => 'test@example.com'
        ];

        $testCarrier = (object) [
            'id' => 999,
            'company_name' => 'Empresa de Prueba',
            'dot_number' => 'TEST123',
            'mc_number' => 'MC999'
        ];

        $testData = [
            'step' => 'test',
            'registration_method' => 'wizard',
            'test_mode' => true
        ];

        return [
            'user' => $testUser,
            'carrier' => $testCarrier,
            'data' => $testData
        ];
    }

    /**
     * Obtener detalles de un log específico
     */
    public function getLogDetails(int $logId): JsonResponse
    {
        try {
            $log = $this->notificationService->getNotificationLogDetails($logId);
            
            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log no encontrado'
                ], 404);
            }

            return response()->json($log);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reintentar una notificación fallida
     */
    public function retryNotification(int $logId): JsonResponse
    {
        try {
            $result = $this->notificationService->retryFailedNotification($logId);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notificación reenviada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo reenviar la notificación'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar la notificación: ' . $e->getMessage()
            ], 500);
        }
    }
}