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
            // Temporarily disable COEP to allow external resources
            // 'Cross-Origin-Embedder-Policy' => 'require-corp',
            'Cross-Origin-Opener-Policy' => 'same-origin-allow-popups',
            'Cross-Origin-Resource-Policy' => 'cross-origin'
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
            $isProduction = config('app.env') === 'production';
            
            // Base CSP directives
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com", // Allow CDN scripts
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com fonts.bunny.net cdn.jsdelivr.net", // Allow CDN styles
                "font-src 'self' fonts.gstatic.com fonts.bunny.net data:",
                "img-src 'self' data: https: via.placeholder.com", // Allow placeholder images
                "connect-src 'self' fonts.bunny.net cdn.jsdelivr.net",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'"
            ];
            
            // Frame-src configuration - more permissive for video embedding
            if ($isProduction) {
                // Production: More explicit frame-src to ensure video loading
                $cspDirectives[] = "frame-src 'self' https://*.youtube.com https://*.youtube-nocookie.com https://*.vimeo.com https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com";
                $cspDirectives[] = "frame-ancestors 'self'";
            } else {
                // Local: Standard frame-src
                $cspDirectives[] = "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com";
                $cspDirectives[] = "frame-ancestors 'none'";
            }
            
            $adminCSP = implode('; ', $cspDirectives);
            
            
            return $adminCSP;
        }
        
        // General CSP for public pages (including driver registration)
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net", // Allow CDN scripts for signature_pad and pikaday
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