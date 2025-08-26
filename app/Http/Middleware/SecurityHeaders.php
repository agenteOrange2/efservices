<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers configuration
        $securityHeaders = [
            // Prevent clickjacking attacks
            'X-Frame-Options' => 'DENY',
            
            // Prevent MIME type sniffing
            'X-Content-Type-Options' => 'nosniff',
            
            // Enable XSS protection
            'X-XSS-Protection' => '1; mode=block',
            
            // Referrer policy
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            
            // Permissions policy (formerly Feature Policy)
            'Permissions-Policy' => implode(', ', [
                'camera=()',
                'microphone=()',
                'geolocation=(self)',
                'payment=(self)',
                'usb=()'
            ]),
            
            // Content Security Policy (CSP)
            'Content-Security-Policy' => $this->getContentSecurityPolicy($request),
            
            // HTTP Strict Transport Security (HSTS)
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            
            // Prevent caching of sensitive pages
            'Cache-Control' => $this->getCacheControl($request),
            
            // Additional security headers
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin'
        ];

        // Apply headers to response
        foreach ($securityHeaders as $header => $value) {
            if ($value !== null) {
                $response->headers->set($header, $value);
            }
        }

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Get Content Security Policy based on request context
     */
    private function getContentSecurityPolicy(Request $request): string
    {
        $isAdmin = str_contains($request->path(), 'admin');
        $isApi = str_contains($request->path(), 'api');
        
        if ($isApi) {
            // Stricter CSP for API endpoints
            return "default-src 'none'; frame-ancestors 'none'; base-uri 'none';";
        }
        
        if ($isAdmin) {
            // Admin panel CSP - more restrictive
            return implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Allow inline scripts for admin functionality
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.bunny.net",
                "font-src 'self' fonts.gstatic.com fonts.bunny.net data:",
                "img-src 'self' data: https:",
                "connect-src 'self'",
                "frame-src 'none'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'"
            ]);
        }
        
        // General CSP for public pages (including driver registration)
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Allow unsafe-eval for Livewire/Alpine.js
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.bunny.net",
            "font-src 'self' fonts.gstatic.com fonts.bunny.net data:",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
    }

    /**
     * Get cache control headers based on request context
     */
    private function getCacheControl(Request $request): string
    {
        $isAdmin = str_contains($request->path(), 'admin');
        $isApi = str_contains($request->path(), 'api');
        $isAuth = str_contains($request->path(), 'login') || 
                  str_contains($request->path(), 'register') ||
                  str_contains($request->path(), 'password');
        
        if ($isAdmin || $isAuth || $isApi) {
            // Prevent caching of sensitive pages
            return 'no-cache, no-store, must-revalidate, private';
        }
        
        // Allow caching for public static content
        if (str_contains($request->path(), 'assets') || 
            str_contains($request->path(), 'css') ||
            str_contains($request->path(), 'js') ||
            str_contains($request->path(), 'images')) {
            return 'public, max-age=31536000';
        }
        
        // Default cache control
        return 'no-cache, must-revalidate';
    }
}