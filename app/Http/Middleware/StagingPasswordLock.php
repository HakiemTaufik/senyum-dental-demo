<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locks the whole TEST copy (staging site) behind one shared username/password,
 * so only the team can see unfinished changes. Turned on only where STAGING_AUTH_USER
 * and STAGING_AUTH_PASSWORD are set in the server's .env (never on the live site).
 */
class StagingPasswordLock
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = (string) config('app.staging_auth.user');
        $password = (string) config('app.staging_auth.password');

        // Not configured, or the health check used by the deploy pipeline: let it through.
        if ($user === '' || $password === '' || $request->is('up')) {
            return $next($request);
        }

        $givenUser = (string) $request->getUser();
        $givenPassword = (string) $request->getPassword();

        if (hash_equals($user, $givenUser) && hash_equals($password, $givenPassword)) {
            return $next($request);
        }

        return response('This is a private test copy. Please sign in.', 401, [
            'WWW-Authenticate' => 'Basic realm="Senyum test copy", charset="UTF-8"',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
