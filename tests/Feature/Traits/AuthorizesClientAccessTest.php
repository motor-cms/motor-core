<?php

use Illuminate\Database\Eloquent\Model;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Traits\AuthorizesClientAccess;

class FixturePolicyForClientAccess
{
    use AuthorizesClientAccess;

    public function deny(?Model $model, string $column = 'client_id'): bool
    {
        return $this->denyForeignClient($model, $column);
    }
}

class TenantedClientAccessFixture extends Model
{
    protected $table = 'tenanted_client_access_fixtures';

    public $timestamps = false;

    protected $guarded = [];
}

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('AuthorizesClientAccess', function () {

    it('does not deny when the resolver is unbound (V1 path)', function () {
        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => 99]);

        expect($policy->deny($model))->toBeFalse();
    });

    it('does not deny when the resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => 99]);

        expect($policy->deny($model))->toBeFalse();
    });

    it('allows when model client_id is in the resolver list', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2, 3]);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => 2]);

        expect($policy->deny($model))->toBeFalse();
    });

    it('denies when model client_id is not in the resolver list', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2, 3]);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => 99]);

        expect($policy->deny($model))->toBeTrue();
    });

    it('always denies when the resolver returns an empty array', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => []);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => 1]);

        expect($policy->deny($model))->toBeTrue();
    });

    it('honors a custom column name', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [7]);

        $policy = new FixturePolicyForClientAccess;
        $allowed = new TenantedClientAccessFixture(['approved_by_client_id' => 7]);
        $denied = new TenantedClientAccessFixture(['approved_by_client_id' => 8]);

        expect($policy->deny($allowed, 'approved_by_client_id'))->toBeFalse();
        expect($policy->deny($denied, 'approved_by_client_id'))->toBeTrue();
    });

    it('treats string and int client ids as equivalent (loose comparison)', function () {
        // Eloquent may surface client_id as string or int depending on PDO settings;
        // the resolver pluck()s ints. Loose comparison ensures we don't deny based
        // on type mismatch alone.
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [2]);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => '2']);

        expect($policy->deny($model))->toBeFalse();
    });

    it('denies a null client_id when the resolver requires a specific id', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1]);

        $policy = new FixturePolicyForClientAccess;
        $model = new TenantedClientAccessFixture(['client_id' => null]);

        expect($policy->deny($model))->toBeTrue();
    });

    describe('null model (parent-traversal cases)', function () {
        // A null $model arrives when a policy traverses through a relation that
        // returned null — either because the parent record was filtered out by
        // the global ClientScope (foreign tenant) or because the foreign key is
        // unset (orphan). In both cases, V2 callers must be denied; V1 / SuperAdmin
        // must still allow (the trait short-circuits before reaching the null check).
        it('does not deny null when the resolver is unbound (V1 path)', function () {
            $policy = new FixturePolicyForClientAccess;

            expect($policy->deny(null))->toBeFalse();
        });

        it('does not deny null when the resolver returns null (SuperAdmin)', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

            $policy = new FixturePolicyForClientAccess;

            expect($policy->deny(null))->toBeFalse();
        });

        it('denies null when the resolver returns a specific client list', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2]);

            $policy = new FixturePolicyForClientAccess;

            expect($policy->deny(null))->toBeTrue();
        });

        it('denies null when the resolver returns an empty array', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => []);

            $policy = new FixturePolicyForClientAccess;

            expect($policy->deny(null))->toBeTrue();
        });
    });
});
