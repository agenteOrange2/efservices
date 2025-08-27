<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidateUploadSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('ValidateUploadSession: Validando sesión de carga', [
            'url' => $request->url(),
            'method' => $request->method(),
            'session_id' => $request->input('session_id') ?? $request->header('X-Session-ID')
        ]);

        // Obtener session_id desde parámetros o headers
        $sessionId = $request->input('session_id') ?? $request->header('X-Session-ID');

        // Validar que session_id esté presente
        if (empty($sessionId)) {
            Log::warning('ValidateUploadSession: Session ID faltante', [
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Session ID is required',
                'error_code' => 'MISSING_SESSION_ID'
            ], 400);
        }

        // Validar formato del session_id
        $validator = Validator::make(['session_id' => $sessionId], [
            'session_id' => [
                'required',
                'string',
                'min:10',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/' // Solo caracteres alfanuméricos, guiones y guiones bajos
            ]
        ]);

        if ($validator->fails()) {
            Log::warning('ValidateUploadSession: Session ID inválido', [
                'session_id' => $sessionId,
                'errors' => $validator->errors()->toArray(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid session ID format',
                'error_code' => 'INVALID_SESSION_FORMAT',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validar longitud y caracteres especiales adicionales
        if (strlen($sessionId) > 255) {
            Log::warning('ValidateUploadSession: Session ID demasiado largo', [
                'session_id' => substr($sessionId, 0, 50) . '...',
                'length' => strlen($sessionId)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Session ID too long',
                'error_code' => 'SESSION_ID_TOO_LONG'
            ], 422);
        }

        // Validar que no contenga caracteres peligrosos
        if (preg_match('/[<>"\'\/\\]/', $sessionId)) {
            Log::warning('ValidateUploadSession: Session ID contiene caracteres peligrosos', [
                'session_id' => $sessionId,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Session ID contains invalid characters',
                'error_code' => 'INVALID_SESSION_CHARACTERS'
            ], 422);
        }

        // Validar rate limiting por session_id (máximo 10 requests por minuto por sesión)
        $rateLimitKey = 'upload_session_' . $sessionId;
        $rateLimitAttempts = cache()->get($rateLimitKey, 0);
        
        if ($rateLimitAttempts >= 10) {
            Log::warning('ValidateUploadSession: Rate limit excedido', [
                'session_id' => $sessionId,
                'attempts' => $rateLimitAttempts,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests for this session',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => 60
            ], 429);
        }

        // Incrementar contador de rate limiting
        cache()->put($rateLimitKey, $rateLimitAttempts + 1, now()->addMinutes(1));

        // Validar que la sesión no esté siendo usada desde múltiples IPs simultáneamente
        $sessionIpKey = 'session_ip_' . $sessionId;
        $sessionIp = cache()->get($sessionIpKey);
        $currentIp = $request->ip();

        if ($sessionIp && $sessionIp !== $currentIp) {
            Log::warning('ValidateUploadSession: Sesión usada desde múltiples IPs', [
                'session_id' => $sessionId,
                'original_ip' => $sessionIp,
                'current_ip' => $currentIp
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Session is being used from another location',
                'error_code' => 'SESSION_IP_MISMATCH'
            ], 403);
        }

        // Registrar IP para esta sesión (válida por 1 hora)
        if (!$sessionIp) {
            cache()->put($sessionIpKey, $currentIp, now()->addHour());
        }

        // Validar User-Agent para detectar posibles bots
        $userAgent = $request->userAgent();
        if (empty($userAgent) || strlen($userAgent) < 10) {
            Log::warning('ValidateUploadSession: User-Agent sospechoso', [
                'session_id' => $sessionId,
                'user_agent' => $userAgent,
                'ip' => $currentIp
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing User-Agent',
                'error_code' => 'INVALID_USER_AGENT'
            ], 400);
        }

        // Validar que el Content-Type sea apropiado para uploads
        if ($request->isMethod('POST') && $request->hasFile('file')) {
            $contentType = $request->header('Content-Type');
            if (!str_contains($contentType, 'multipart/form-data')) {
                Log::warning('ValidateUploadSession: Content-Type inválido para upload', [
                    'session_id' => $sessionId,
                    'content_type' => $contentType
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Content-Type for file upload',
                    'error_code' => 'INVALID_CONTENT_TYPE'
                ], 400);
            }
        }

        // Agregar session_id al request para uso posterior
        $request->merge(['validated_session_id' => $sessionId]);

        // Logging de sesión válida
        Log::info('ValidateUploadSession: Sesión validada exitosamente', [
            'session_id' => $sessionId,
            'ip' => $currentIp,
            'rate_limit_attempts' => $rateLimitAttempts + 1
        ]);

        return $next($request);
    }

    /**
     * Handle session cleanup after request
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        $sessionId = $request->input('validated_session_id');
        
        if ($sessionId) {
            // Log de finalización de request
            Log::info('ValidateUploadSession: Request completado', [
                'session_id' => $sessionId,
                'status_code' => $response->getStatusCode(),
                'response_size' => strlen($response->getContent())
            ]);

            // Limpiar cache si la respuesta fue un error crítico
            if ($response->getStatusCode() >= 500) {
                $rateLimitKey = 'upload_session_' . $sessionId;
                cache()->forget($rateLimitKey);
                
                Log::info('ValidateUploadSession: Cache limpiado por error del servidor', [
                    'session_id' => $sessionId,
                    'status_code' => $response->getStatusCode()
                ]);
            }
        }
    }

    /**
     * Generate a secure session ID for frontend use
     *
     * @return string
     */
    public static function generateSessionId(): string
    {
        // Generar un ID único y seguro
        $timestamp = now()->timestamp;
        $randomBytes = bin2hex(random_bytes(16));
        $hash = hash('sha256', $timestamp . $randomBytes . config('app.key'));
        
        return 'upload_' . $timestamp . '_' . substr($hash, 0, 16);
    }

    /**
     * Validate session ID format (static method for use in other classes)
     *
     * @param string $sessionId
     * @return bool
     */
    public static function isValidSessionId(string $sessionId): bool
    {
        if (empty($sessionId) || strlen($sessionId) < 10 || strlen($sessionId) > 255) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $sessionId)) {
            return false;
        }

        if (preg_match('/[<>"\'\/\\]/', $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * Clean up expired session data
     *
     * @return int Number of cleaned sessions
     */
    public static function cleanupExpiredSessions(): int
    {
        $cleaned = 0;
        
        // Esta función sería llamada por un comando programado
        // Por ahora, solo registramos que debería implementarse
        Log::info('ValidateUploadSession: Limpieza de sesiones expiradas solicitada');
        
        return $cleaned;
    }
}