<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Core\Scopes\ClientScope;
use Motor\Core\Search\ClientScopedSearch;

/**
 * Marks an Eloquent model as belonging to a Client (tenant).
 *
 * Boots {@see ClientScope} so every query is automatically filtered to the
 * authenticated user's clients, defines the {@see static::client()} BelongsTo,
 * exposes a Scout-side filtered-search shortcut, and auto-fills `client_id`
 * on `creating` when the request resolver points at a single client.
 *
 * The auto-fill is gated on a bound resolver (V2 middleware path). Console,
 * seeders, factories, and the queue worker do not bind the resolver, so they
 * never trigger auto-fill — explicit `client_id` values always win.
 *
 * Models with a non-default tenant column (e.g. Approval's `approved_by_client_id`)
 * may override {@see static::clientForeignKeyName()} to redirect both the scope
 * and the auto-fill to the alternate column.
 */
trait BelongsToClient
{
    protected static function bootBelongsToClient(): void
    {
        static::addGlobalScope(new ClientScope(static::clientForeignKeyName()));

        static::creating(function ($model) {
            $key = static::clientForeignKeyName();

            if (! empty($model->{$key})) {
                return;
            }

            if (! app()->bound(ClientScope::RESOLVER_KEY)) {
                return;
            }

            $ids = (app(ClientScope::RESOLVER_KEY))();

            if (is_array($ids) && count($ids) === 1) {
                $model->{$key} = $ids[0];
            }
        });
    }

    public static function clientForeignKeyName(): string
    {
        return 'client_id';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(config('motor-admin.models.client'), static::clientForeignKeyName());
    }

    public static function searchScopedToClient(string $query): ScoutBuilder
    {
        return ClientScopedSearch::for(static::class, $query, static::clientForeignKeyName());
    }
}
