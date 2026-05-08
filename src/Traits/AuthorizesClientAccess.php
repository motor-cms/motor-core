<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Motor\Core\Scopes\ClientScope;

/**
 * Policy-side counterpart to ClientScope.
 *
 * Adds {@see static::denyForeignClient()} for use in Laravel policies. Returns
 * true (deny) when the per-request resolver is bound and the model's tenant
 * column does not match an allowed client. Returns false (do not deny) when
 * the resolver is unbound (V1 / console) or when it returns null (SuperAdmin).
 *
 * Accepts a nullable model so policies can chain through a parent relation
 * (e.g. `$score->topic`) that may return null when the parent record was
 * filtered by the global ClientScope or the foreign key is unset. In that
 * case the call denies under V2 and allows under V1 / SuperAdmin — matching
 * the same short-circuits applied to a populated model.
 *
 * Use loose comparison so a model surfacing client_id as a string does not
 * deny against an int resolver list, and vice versa.
 *
 * Typical usage (direct):
 *
 *   public function update(User $user, BuilderPage $page): bool
 *   {
 *       if ($this->denyForeignClient($page)) return false;
 *       return $user->hasPermissionTo('builder-pages.write');
 *   }
 *
 * Typical usage (parent traversal):
 *
 *   public function update(User $user, Score $score): bool
 *   {
 *       if ($this->denyForeignClient($score->topic)) return false;
 *       return $user->hasPermissionTo('topics.write');
 *   }
 */
trait AuthorizesClientAccess
{
    protected function denyForeignClient(?Model $model, string $column = 'client_id'): bool
    {
        if (! app()->bound(ClientScope::RESOLVER_KEY)) {
            return false;
        }

        $ids = (app(ClientScope::RESOLVER_KEY))();

        if ($ids === null) {
            return false;
        }

        if ($model === null) {
            return true;
        }

        return ! in_array($model->{$column}, $ids);
    }
}
