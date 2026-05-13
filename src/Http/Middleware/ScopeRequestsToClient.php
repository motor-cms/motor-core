<?php

namespace Motor\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Motor\Core\Scopes\ClientScope;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per-request tenant resolver binding for V2 routes.
 *
 * On handle(): if the request is authenticated, computes the user's allowed
 * client ids and binds a closure under {@see ClientScope::RESOLVER_KEY}. The
 * resolver returns:
 *   - null   for SuperAdmin (unscoped)
 *   - []     when the user has no clients (no access)
 *   - int[]  the user's pivot client ids
 *
 * On terminate(): clears the binding so it does not leak into subsequent
 * requests served by the same worker process (sync queue, Octane, future
 * long-lived processes).
 *
 * Apply only to the V2 route group. V1, public, and console paths must NOT
 * receive this middleware — without the binding, ClientScope and the
 * AuthorizesClientAccess trait are no-ops, which is the V1 / unauthenticated
 * contract.
 */
class ScopeRequestsToClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $resolved = null;
            } else {
                $resolved = $user->clients()->pluck('clients.id')->all();
            }

            app()->instance(ClientScope::RESOLVER_KEY, fn () => $resolved);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        app()->forgetInstance(ClientScope::RESOLVER_KEY);
    }
}
