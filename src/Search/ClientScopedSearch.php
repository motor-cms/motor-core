<?php

namespace Motor\Core\Search;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Traits\BelongsToClient;

/**
 * Scout-side counterpart to ClientScope.
 *
 * Eloquent global scopes do not apply to Scout's `Model::search()` because
 * Scout queries the engine (Meilisearch) directly, not the database. This
 * helper wraps `Model::search($query)` and adds the equivalent client-id
 * filter based on the same per-request resolver.
 *
 * V2 search call sites should call this (or {@see BelongsToClient::searchScopedToClient()})
 * instead of bare `Model::search()`. V1, public, and console call sites
 * should keep using `Model::search()` directly — this helper is a no-op
 * when the resolver is unbound, but skipping the indirection there keeps
 * V1 byte-for-byte unchanged.
 *
 * Requires the search index to expose the filter column (default: `client_id`)
 * via Meilisearch's `filterableAttributes` setting.
 */
class ClientScopedSearch
{
    /**
     * @param  class-string<Model>  $modelClass  Must use the Searchable trait.
     */
    public static function for(string $modelClass, string $query, string $column = 'client_id'): ScoutBuilder
    {
        /** @var ScoutBuilder $builder */
        $builder = $modelClass::search($query);

        if (! app()->bound(ClientScope::RESOLVER_KEY)) {
            return $builder;
        }

        $ids = (app(ClientScope::RESOLVER_KEY))();

        if ($ids === null) {
            return $builder;
        }

        if ($ids === []) {
            return $builder->where($column, -1);
        }

        if (count($ids) === 1) {
            return $builder->where($column, $ids[0]);
        }

        return $builder->whereIn($column, $ids);
    }
}
