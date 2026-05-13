<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Motor\Admin\Models\Client;
use Motor\Admin\Models\User;
use Motor\Core\Http\Middleware\ScopeRequestsToClient;
use Motor\Core\Scopes\ClientScope;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

/**
 * Run the middleware against a synthetic request and return the response.
 * Returns the value the resolver produced inside `handle()`, or false if
 * the resolver was never bound during the request.
 */
function runScopeMiddleware(): array|null|false
{
    $middleware = new ScopeRequestsToClient;

    $captured = false;
    $middleware->handle(Request::create('/v2/test'), function () use (&$captured) {
        if (app()->bound(ClientScope::RESOLVER_KEY)) {
            $captured = (app(ClientScope::RESOLVER_KEY))();
        }

        return response('ok');
    });

    return $captured;
}

describe('ScopeRequestsToClient', function () {

    it('does not bind the resolver when the request is unauthenticated', function () {
        $captured = runScopeMiddleware();

        // $captured stays at its sentinel "false" because the resolver is
        // never bound during an unauthenticated request — neither inside
        // the closure nor after.
        expect($captured)->toBeFalse();
        expect(app()->bound(ClientScope::RESOLVER_KEY))->toBeFalse();
    });

    it('binds a null resolver for SuperAdmin', function () {
        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');
        $this->actingAs($admin);

        $captured = runScopeMiddleware();

        expect($captured)->toBeNull();
    });

    it('binds an empty array when the user has no clients', function () {
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $this->actingAs($user);

        $captured = runScopeMiddleware();

        expect($captured)->toBe([]);
    });

    it('binds the user pivot client ids', function () {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach([$clientA->id, $clientB->id]);
        $this->actingAs($user);

        $captured = runScopeMiddleware();

        expect($captured)->toEqualCanonicalizing([$clientA->id, $clientB->id]);
    });

    it('memoizes the resolver result so repeated calls do not re-query', function () {
        $clientA = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($clientA->id);
        $this->actingAs($user);

        $middleware = new ScopeRequestsToClient;

        $first = null;
        $second = null;
        $middleware->handle(Request::create('/v2/test'), function () use (&$first, &$second) {
            $resolver = app(ClientScope::RESOLVER_KEY);
            $first = $resolver();
            // Simulate the user gaining additional clients between calls — the
            // resolver must still return the precomputed array.
            request()->user()?->clients()->detach();
            $second = $resolver();

            return response('ok');
        });

        expect($first)->toBe([$clientA->id]);
        expect($second)->toBe($first);
    });

    it('forgets the resolver instance on terminate', function () {
        $clientA = Client::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $user->clients()->attach($clientA->id);
        $this->actingAs($user);

        $middleware = new ScopeRequestsToClient;
        $request = Request::create('/v2/test');
        $response = $middleware->handle($request, fn () => response('ok'));

        expect(app()->bound(ClientScope::RESOLVER_KEY))->toBeTrue();

        $middleware->terminate($request, $response);

        expect(app()->bound(ClientScope::RESOLVER_KEY))->toBeFalse();
    });
});
