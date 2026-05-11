<?php

namespace Motor\Core\Test\Fixtures\Services;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class NonTenantedSearchableForGlobalSearch extends Model
{
    use Searchable;

    protected $table = 'non_tenanted_searchable_for_global_search';

    public $timestamps = false;
}
