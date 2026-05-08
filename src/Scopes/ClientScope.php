<?php

namespace Motor\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Tenant isolation scope.
 *
 * Reads a per-request resolver bound under {@see self::RESOLVER_KEY} and
 * filters queries to the authenticated user's clients. The resolver is a
 * Closure returning one of:
 *  - null      → SuperAdmin / unscoped (no filter applied).
 *  - []        → user has no clients (zero-row filter applied).
 *  - int[]     → ids the user is allowed to see.
 *
 * When the binding is absent, the scope is a no-op. This is the V1 / console
 * / queue / public path: the scope cannot affect requests where no resolver
 * has been bound by middleware.
 */
class ClientScope implements Scope
{
    public const RESOLVER_KEY = 'client.scope.resolver';

    public function __construct(private readonly string $column = 'client_id') {}

    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound(self::RESOLVER_KEY)) {
            return;
        }

        $ids = (app(self::RESOLVER_KEY))();

        if ($ids === null) {
            return;
        }

        if ($ids === []) {
            $builder->whereRaw('1=0');

            return;
        }

        $builder->whereIn($model->getTable().'.'.$this->column, $ids);
    }
}
