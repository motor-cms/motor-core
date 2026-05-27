<?php

use Motor\Core\Scopes\ClientScope;
use Motor\Core\Test\Fixtures\Scopes\DeniesFixture;

beforeEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));
afterEach(fn () => app()->forgetInstance(ClientScope::RESOLVER_KEY));

// Smoke coverage for the public static counterpart of the trait method.
// The trait delegates here, so the behavioral matrix is exercised by
// AuthorizesClientAccessTest -- this file just guards the public surface
// itself so callers (ApprovalService, jobs, ad-hoc tooling) can rely on it.

describe('ClientScope::deniesForModel', function () {

    it('does not deny when the resolver is unbound (V1 path)', function () {
        $model = new DeniesFixture(['client_id' => 99]);

        expect(ClientScope::deniesForModel($model))->toBeFalse();
    });

    it('does not deny when the resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $model = new DeniesFixture(['client_id' => 99]);

        expect(ClientScope::deniesForModel($model))->toBeFalse();
    });

    it('denies when the model belongs to a foreign client', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2]);

        expect(ClientScope::deniesForModel(new DeniesFixture(['client_id' => 99])))->toBeTrue();
    });

    it('denies a null model when the resolver is bound and not SuperAdmin', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1]);

        expect(ClientScope::deniesForModel(null))->toBeTrue();
    });

    it('honors a custom column override', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [7]);

        expect(ClientScope::deniesForModel(new DeniesFixture(['approved_by_client_id' => 7]), 'approved_by_client_id'))
            ->toBeFalse();
        expect(ClientScope::deniesForModel(new DeniesFixture(['approved_by_client_id' => 8]), 'approved_by_client_id'))
            ->toBeTrue();
    });
});
