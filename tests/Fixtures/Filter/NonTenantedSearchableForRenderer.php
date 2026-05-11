<?php

namespace Motor\Core\Test\Fixtures\Filter;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

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
