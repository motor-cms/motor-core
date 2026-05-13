<?php

namespace Motor\Core\Test\Fixtures\Services;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Motor\Core\Traits\BelongsToClient;

class TenantedSearchableForGlobalSearch extends Model
{
    use BelongsToClient;
    use Searchable;

    protected $table = 'tenanted_searchable_for_global_search';

    public $timestamps = false;
}
