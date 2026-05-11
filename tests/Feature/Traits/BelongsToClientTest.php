<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Admin\Models\Client;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Test\Fixtures\Traits\TenantedTraitFixture;
use Motor\Core\Test\Fixtures\Traits\TenantedTraitFixtureCustomColumn;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::dropIfExists('tenanted_trait_fixtures');
    Schema::create('tenanted_trait_fixtures', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('client_id')->nullable();
        $table->string('name')->nullable();
    });

    Schema::dropIfExists('custom_column_fixtures');
    Schema::create('custom_column_fixtures', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('approved_by_client_id')->nullable();
        $table->string('name')->nullable();
    });

    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('BelongsToClient', function () {

    describe('global scope', function () {
        it('boots the ClientScope automatically', function () {
            $scopes = (new TenantedTraitFixture)->getGlobalScopes();

            expect($scopes)->toHaveKey(ClientScope::class);
            expect($scopes[ClientScope::class])->toBeInstanceOf(ClientScope::class);
        });

        it('filters queries when a resolver is bound', function () {
            TenantedTraitFixture::insert([
                ['client_id' => 1, 'name' => 'one'],
                ['client_id' => 2, 'name' => 'two'],
                ['client_id' => 3, 'name' => 'three'],
            ]);

            app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 3]);

            expect(TenantedTraitFixture::pluck('name')->all())
                ->toEqualCanonicalizing(['one', 'three']);
        });
    });

    describe('client() relation', function () {
        it('returns a BelongsTo to the configured client model', function () {
            $relation = (new TenantedTraitFixture)->client();

            expect($relation)->toBeInstanceOf(BelongsTo::class);
            expect($relation->getRelated())->toBeInstanceOf(Client::class);
            expect($relation->getForeignKeyName())->toBe('client_id');
        });

        it('honors a custom foreign key name', function () {
            $relation = (new TenantedTraitFixtureCustomColumn)->client();

            expect($relation->getForeignKeyName())->toBe('approved_by_client_id');
        });
    });

    describe('creating auto-fill', function () {
        it('auto-fills client_id when resolver returns a single id', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

            $row = TenantedTraitFixture::create(['name' => 'auto']);

            expect($row->client_id)->toBe(42);
        });

        it('does not overwrite an explicitly-set client_id', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

            // Use raw insert to bypass the global scope filter on subsequent reads.
            TenantedTraitFixture::withoutGlobalScope(ClientScope::class)
                ->create(['name' => 'explicit', 'client_id' => 99]);

            $row = TenantedTraitFixture::withoutGlobalScope(ClientScope::class)
                ->where('name', 'explicit')
                ->first();

            expect($row->client_id)->toBe(99);
        });

        it('does not auto-fill when resolver returns multiple ids', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2]);

            $row = TenantedTraitFixture::withoutGlobalScope(ClientScope::class)
                ->create(['name' => 'multi']);

            expect($row->client_id)->toBeNull();
        });

        it('does not auto-fill when resolver returns null (SuperAdmin)', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

            $row = TenantedTraitFixture::create(['name' => 'admin']);

            expect($row->client_id)->toBeNull();
        });

        it('does not auto-fill when no resolver is bound', function () {
            $row = TenantedTraitFixture::create(['name' => 'unbound']);

            expect($row->client_id)->toBeNull();
        });

        it('uses the custom foreign key column when auto-filling', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [7]);

            $row = TenantedTraitFixtureCustomColumn::create(['name' => 'custom']);

            expect($row->approved_by_client_id)->toBe(7);
        });
    });

    describe('withoutClientAutoFill', function () {
        // Escape hatch for HTTP-time SuperAdmin tooling that needs to create a
        // row outside the caller's tenant. The flag is per-class, so suspending
        // the auto-fill on one tenanted model does not affect the others.
        it('suspends auto-fill inside the callback', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

            $row = TenantedTraitFixture::withoutClientAutoFill(function () {
                return TenantedTraitFixture::withoutGlobalScope(ClientScope::class)
                    ->create(['name' => 'bypassed']);
            });

            expect($row->client_id)->toBeNull();
        });

        it('restores the auto-fill after the callback returns', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

            TenantedTraitFixture::withoutClientAutoFill(fn () => null);

            $row = TenantedTraitFixture::create(['name' => 'restored']);

            expect($row->client_id)->toBe(42);
        });

        it('restores the auto-fill even when the callback throws', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

            try {
                TenantedTraitFixture::withoutClientAutoFill(function () {
                    throw new RuntimeException('boom');
                });
            } catch (RuntimeException) {
                // expected
            }

            $row = TenantedTraitFixture::create(['name' => 'after-throw']);

            expect($row->client_id)->toBe(42);
        });

        it('returns the callback\'s return value', function () {
            $result = TenantedTraitFixture::withoutClientAutoFill(fn () => 'payload');

            expect($result)->toBe('payload');
        });
    });

    describe('searchScopedToClient', function () {
        it('returns a Scout builder with the client filter applied', function () {
            app()->instance(ClientScope::RESOLVER_KEY, fn () => [5]);

            $builder = TenantedTraitFixture::searchScopedToClient('hello');

            expect($builder)->toBeInstanceOf(ScoutBuilder::class);
            expect($builder->wheres)->toBe([
                ['field' => 'client_id', 'operator' => '=', 'value' => 5],
            ]);
        });

        it('passes the search query through', function () {
            $builder = TenantedTraitFixture::searchScopedToClient('hello world');

            expect($builder->query)->toBe('hello world');
        });
    });
});
