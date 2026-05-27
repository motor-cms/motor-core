<?php

use Motor\Core\Scopes\ClientScope;
use Motor\Core\Services\GlobalSearchService;
use Motor\Core\Test\Fixtures\Services\NonTenantedSearchableForGlobalSearch;
use Motor\Core\Test\Fixtures\Services\TenantedSearchableForGlobalSearch;

beforeEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);

    config([
        'global-search.modules' => [
            'tenanted' => [
                'module' => 'test',
                'entity' => 'tenanted',
                'index' => 'tenanted_idx',
                'model' => TenantedSearchableForGlobalSearch::class,
                'title_field' => 'name',
                'excerpt_field' => null,
                'meta_fields' => [],
                'default_filter' => null,
            ],
            'tenanted_with_default' => [
                'module' => 'test',
                'entity' => 'tenanted_with_default',
                'index' => 'tenanted_with_default_idx',
                'model' => TenantedSearchableForGlobalSearch::class,
                'title_field' => 'name',
                'excerpt_field' => null,
                'meta_fields' => [],
                'default_filter' => 'is_active = true',
            ],
            'non_tenanted' => [
                'module' => 'test',
                'entity' => 'non_tenanted',
                'index' => 'non_tenanted_idx',
                'model' => NonTenantedSearchableForGlobalSearch::class,
                'title_field' => 'name',
                'excerpt_field' => null,
                'meta_fields' => [],
                'default_filter' => null,
            ],
        ],
    ]);
});

afterEach(function () {
    app()->forgetInstance(ClientScope::RESOLVER_KEY);
});

/**
 * Invoke the protected buildSearchQueries via reflection and return its
 * payload as a list of arrays (one per module config).
 */
function buildGlobalSearchQueries(string $term = 'foo'): array
{
    $service = new GlobalSearchService;
    $modules = config('global-search.modules');

    $method = (new ReflectionClass(GlobalSearchService::class))->getMethod('buildSearchQueries');
    $method->setAccessible(true);
    $queries = $method->invoke($service, $modules, $term, 0, 25);

    return array_map(fn ($q) => $q->toArray(), $queries);
}

describe('GlobalSearchService tenant scoping', function () {

    it('applies no client filter when the resolver is unbound (V1 / public)', function () {
        $payloads = buildGlobalSearchQueries();

        // First module: tenanted, no default_filter → no filter at all
        expect($payloads[0])->not->toHaveKey('filter');

        // Second module: tenanted, has default_filter → only default_filter
        expect($payloads[1]['filter'] ?? null)->toBe(['is_active = true']);

        // Third module: non-tenanted → no filter
        expect($payloads[2])->not->toHaveKey('filter');
    });

    it('applies no client filter when the resolver returns null (SuperAdmin)', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => null);

        $payloads = buildGlobalSearchQueries();

        expect($payloads[0])->not->toHaveKey('filter');
        expect($payloads[1]['filter'] ?? null)->toBe(['is_active = true']);
        expect($payloads[2])->not->toHaveKey('filter');
    });

    it('adds client_id filter to tenanted modules when resolver returns a single id', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [42]);

        $payloads = buildGlobalSearchQueries();

        expect($payloads[0]['filter'] ?? null)->toBe(['client_id = 42']);
        expect($payloads[1]['filter'] ?? null)->toBe(['is_active = true', 'client_id = 42']);
        expect($payloads[2])->not->toHaveKey('filter');
    });

    it('adds an IN filter when the resolver returns multiple ids', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => [1, 2, 3]);

        $payloads = buildGlobalSearchQueries();

        expect($payloads[0]['filter'] ?? null)->toBe(['client_id IN [1, 2, 3]']);
        expect($payloads[1]['filter'] ?? null)->toBe(['is_active = true', 'client_id IN [1, 2, 3]']);
        expect($payloads[2])->not->toHaveKey('filter');
    });

    it('adds an impossible filter for tenanted modules when resolver returns empty array', function () {
        app()->instance(ClientScope::RESOLVER_KEY, fn () => []);

        $payloads = buildGlobalSearchQueries();

        expect($payloads[0]['filter'] ?? null)->toBe(['client_id = -1']);
        expect($payloads[1]['filter'] ?? null)->toBe(['is_active = true', 'client_id = -1']);
        expect($payloads[2])->not->toHaveKey('filter');
    });
});
