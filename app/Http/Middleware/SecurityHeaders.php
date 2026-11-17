<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers on every response, plus a Content Security Policy on the public
 * website (a list of where scripts/styles may load from, which blocks most injected code).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $isPublicPage = ! $request->is('admin', 'admin/*', 'livewire*', 'filament/*', 'dev/*');

        // A fresh random "nonce" lets our own small inline scripts run, but nobody else's.
        if ($isPublicPage) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if (app()->isProduction()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        // Search engines: only the real, live site may be listed.
        if (! config('seo.indexable')) {
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }

        if (! Vite::isRunningHot() && str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $headers['Content-Security-Policy'] = $isPublicPage ? $this->policy(Vite::cspNonce()) : $this->adminPolicy();
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value, replace: false);
        }

        // Don't tell visitors which PHP version runs the site (a free hint for attackers).
        $response->headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        return $response;
    }

    /**
     * The admin panel (Filament) builds pages with inline scripts and Alpine.js expressions,
     * and swaps pages without a reload, so a per-page nonce can't work there. This policy
     * still blocks scripts, frames and plugins from other websites, stops data being sent
     * to other servers, and stops the panel being framed by another site.
     * Filament's default staff avatars come from ui-avatars.com.
     */
    private function adminPolicy(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: https://ui-avatars.com",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            'upgrade-insecure-requests',
        ]);
    }

    private function policy(string $nonce): string
    {
        $google = 'https://www.googletagmanager.com https://*.google-analytics.com https://*.analytics.google.com';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: {$google}",
            "font-src 'self'",
            "connect-src 'self' {$google}",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            'upgrade-insecure-requests',
        ]);
    }
}
