<?php

namespace Motor\Core\Test\Fixtures\Filter;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
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
