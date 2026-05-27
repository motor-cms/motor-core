<?php

use Laravel\Scout\Builder;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Search\ClientScopedSearch;
use Motor\Core\Test\Fixtures\Search\SearchableFixtureModel;

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('ClientScopedSearch', function () {

    it('returns a Scout builder with no client filter when the resolver is unbound', function () {
        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo');

        expect($builder)->toBeInstanceOf(Builder::class);
        expect($builder->wheres)->toBe([]);
        expect($builder->whereIns)->toBe([]);
    });

    it('does not add a filter when the resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo');

        expect($builder->wheres)->toBe([]);
        expect($builder->whereIns)->toBe([]);
    });

    it('adds an impossible filter when the resolver returns an empty array', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => []);

        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo');

        expect($builder->wheres)->toBe([
            ['field' => 'client_id', 'operator' => '=', 'value' => -1],
        ]);
    });

    it('adds where(client_id, X) when the resolver returns a single id', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo');

        expect($builder->wheres)->toBe([
            ['field' => 'client_id', 'operator' => '=', 'value' => 42],
        ]);
        expect($builder->whereIns)->toBe([]);
    });

    it('adds whereIn(client_id, [...]) when the resolver returns multiple ids', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2, 3]);

        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo');

        expect($builder->whereIns)->toBe(['client_id' => [1, 2, 3]]);
        expect($builder->wheres)->toBe([]);
    });

    it('honors a custom column name', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [7]);

        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'foo', 'approved_by_client_id');

        expect($builder->wheres)->toBe([
            ['field' => 'approved_by_client_id', 'operator' => '=', 'value' => 7],
        ]);
    });

    it('passes the search query through to the Scout builder', function () {
        $builder = ClientScopedSearch::for(SearchableFixtureModel::class, 'hello world');

        expect($builder->query)->toBe('hello world');
    });
});
