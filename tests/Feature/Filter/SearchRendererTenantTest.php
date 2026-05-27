<?php

use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Core\Filter\Renderers\SearchRenderer;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Test\Fixtures\Filter\NonTenantedSearchableForRenderer;
use Motor\Core\Test\Fixtures\Filter\TenantedSearchableForRenderer;

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

describe('SearchRenderer tenant scoping', function () {

    it('routes a tenanted model through ClientScopedSearch when resolver is bound', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

        $renderer = new SearchRenderer('search');
        $renderer->setValue('hello');

        $query = (new TenantedSearchableForRenderer)->newQuery();
        $result = $renderer->query($query);

        expect($result)->toBeInstanceOf(ScoutBuilder::class);
        expect($result->wheres)->toBe([
            ['field' => 'client_id', 'operator' => '=', 'value' => 42],
        ]);
    });

    it('does not filter a tenanted model when resolver is unbound (V1 path)', function () {
        $renderer = new SearchRenderer('search');
        $renderer->setValue('hello');

        $query = (new TenantedSearchableForRenderer)->newQuery();
        $result = $renderer->query($query);

        expect($result)->toBeInstanceOf(ScoutBuilder::class);
        expect($result->wheres)->toBe([]);
        expect($result->whereIns)->toBe([]);
    });

    it('does not filter a tenanted model when resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $renderer = new SearchRenderer('search');
        $renderer->setValue('hello');

        $query = (new TenantedSearchableForRenderer)->newQuery();
        $result = $renderer->query($query);

        expect($result->wheres)->toBe([]);
    });

    it('does not modify a non-tenanted Scout model even when resolver is bound', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

        $renderer = new SearchRenderer('search');
        $renderer->setValue('hello');

        $query = (new NonTenantedSearchableForRenderer)->newQuery();
        $result = $renderer->query($query);

        expect($result)->toBeInstanceOf(ScoutBuilder::class);
        expect($result->wheres)->toBe([]);
        expect($result->whereIns)->toBe([]);
    });
});
