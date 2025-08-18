<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventMassAssignment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define protected fields that should never be mass assigned
        $protectedFields = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'email_verified_at',
            'remember_token',
            'password_reset_token',
            'api_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'current_team_id',
            'profile_photo_path',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at'
        ];

        // Define role-based protected fields
        $roleProtectedFields = [
            'admin' => [],
            'carrier' => [
                'user_id',
                'verification_date',
                'admin_notes',
                'system_flags'
            ],
            'driver' => [
                'user_id',
                'carrier_id',
                'verification_date',
                'admin_notes',
                'system_flags',
                'salary',
                'hire_date'
            ]
        ];

        // Get user role
        $userRole = $request->user()?->getRoleNames()->first() ?? 'guest';
        
        // Merge protected fields based on role
        $allProtectedFields = array_merge(
            $protectedFields,
            $roleProtectedFields[$userRole] ?? $roleProtectedFields['driver']
        );

        // Check for suspicious mass assignment attempts
        $suspiciousFields = array_intersect(
            array_keys($request->all()),
            $allProtectedFields
        );

        if (!empty($suspiciousFields)) {
            // Log the suspicious attempt
            Log::warning('Mass assignment attempt detected', [
                'user_id' => $request->user()?->id,
                'user_role' => $userRole,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'suspicious_fields' => $suspiciousFields,
                'all_input' => array_keys($request->all())
            ]);

            // Remove protected fields from request
            $request->request->remove($suspiciousFields);
            
            // For admin users, just log but don't block
            if ($userRole !== 'admin') {
                // Add warning header for non-admin users
                $response = $next($request);
                $response->headers->set('X-Security-Warning', 'Protected fields filtered');
                return $response;
            }
        }

        // Additional validation for specific routes
        $this->validateSpecificRoutes($request);

        return $next($request);
    }

    /**
     * Validate specific routes for additional security
     */
    private function validateSpecificRoutes(Request $request): void
    {
        $routeName = $request->route()?->getName();
        $method = $request->method();

        // Protect user creation/update routes
        if (in_array($routeName, ['users.store', 'users.update', 'carriers.store', 'carriers.update'])) {
            $dangerousFields = ['access_type', 'status', 'email_verified_at'];
            $foundDangerous = array_intersect(array_keys($request->all()), $dangerousFields);
            
            if (!empty($foundDangerous) && !$request->user()?->hasRole('admin')) {
                Log::alert('Attempt to modify critical user fields', [
                    'user_id' => $request->user()?->id,
                    'route' => $routeName,
                    'dangerous_fields' => $foundDangerous,
                    'ip' => $request->ip()
                ]);
                
                // Remove dangerous fields
                foreach ($foundDangerous as $field) {
                    $request->request->remove($field);
                }
            }
        }

        // Protect financial/sensitive data routes
        if (str_contains($routeName ?? '', 'payment') || str_contains($routeName ?? '', 'billing')) {
            $financialFields = ['amount', 'balance', 'credit', 'payment_method'];
            $foundFinancial = array_intersect(array_keys($request->all()), $financialFields);
            
            if (!empty($foundFinancial)) {
                Log::warning('Financial data modification attempt', [
                    'user_id' => $request->user()?->id,
                    'route' => $routeName,
                    'financial_fields' => $foundFinancial,
                    'ip' => $request->ip()
                ]);
            }
        }

        // Monitor bulk operations
        if ($method === 'POST' && (str_contains($routeName ?? '', 'bulk') || $request->has('bulk_action'))) {
            Log::info('Bulk operation detected', [
                'user_id' => $request->user()?->id,
                'route' => $routeName,
                'bulk_data_count' => count($request->all()),
                'ip' => $request->ip()
            ]);
        }
    }
}