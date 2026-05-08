<?php

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;
use Motor\Core\Filter\Renderers\SearchRenderer;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Traits\BelongsToClient;

class TenantedSearchableForRenderer extends Model
{
    use BelongsToClient;
    use Searchable;

    protected $table = 'tenanted_searchable_for_renderer';

    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'tenanted_searchable_for_renderer_index';
    }
}

class NonTenantedSearchableForRenderer extends Model
{
    use Searchable;

    protected $table = 'non_tenanted_searchable_for_renderer';

    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'non_tenanted_searchable_for_renderer_index';
    }
}

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
