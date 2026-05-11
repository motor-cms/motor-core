<?php

namespace Motor\Core\Test\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Model;

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
