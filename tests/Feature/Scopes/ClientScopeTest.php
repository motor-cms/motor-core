<?php

use Illuminate\Database\Eloquent\Model;
use Motor\Core\Scopes\ClientScope;

/**
 * Plain Eloquent fixture model used purely to obtain a query builder.
 * No DB interaction — assertions are made against the generated SQL/bindings,
 * never executing the query.
 */
class TenantedFixtureModel extends Model
{
    protected $table = 'tenanted_fixtures';

    public $timestamps = false;
}

class CustomColumnFixtureModel extends Model
{
    protected $table = 'approval_fixtures';

    public $timestamps = false;
}

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('ClientScope', function () {

    it('does not modify the query when the resolver is unbound', function () {
        $model = new TenantedFixtureModel;
        $builder = $model->newQuery();

        (new ClientScope)->apply($builder, $model);

        expect($builder->toSql())->toBe('select * from "tenanted_fixtures"');
        expect($builder->getBindings())->toBe([]);
    });

    it('does not modify the query when the resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $model = new TenantedFixtureModel;
        $builder = $model->newQuery();

        (new ClientScope)->apply($builder, $model);

        expect($builder->toSql())->toBe('select * from "tenanted_fixtures"');
        expect($builder->getBindings())->toBe([]);
    });

    it('adds whereRaw 1=0 when the resolver returns an empty array', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => []);

        $model = new TenantedFixtureModel;
        $builder = $model->newQuery();

        (new ClientScope)->apply($builder, $model);

        expect($builder->toSql())->toContain('1=0');
        expect($builder->getBindings())->toBe([]);
    });

    it('adds a table-qualified whereIn when the resolver returns ids', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2, 3]);

        $model = new TenantedFixtureModel;
        $builder = $model->newQuery();

        (new ClientScope)->apply($builder, $model);

        expect($builder->toSql())
            ->toBe('select * from "tenanted_fixtures" where "tenanted_fixtures"."client_id" in (?, ?, ?)');
        expect($builder->getBindings())->toBe([1, 2, 3]);
    });

    it('honors a custom column name', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

        $model = new CustomColumnFixtureModel;
        $builder = $model->newQuery();

        (new ClientScope('approved_by_client_id'))->apply($builder, $model);

        expect($builder->toSql())
            ->toBe('select * from "approval_fixtures" where "approval_fixtures"."approved_by_client_id" in (?)');
        expect($builder->getBindings())->toBe([42]);
    });

    it('exposes a public RESOLVER_KEY constant', function () {
        expect(ClientScope::RESOLVER_KEY)->toBe('client.scope.resolver');
    });
});
