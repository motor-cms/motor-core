<?php

namespace Motor\Core\Test\Fixtures\Search;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchableFixtureModel extends Model
{
    use Searchable;

    protected $table = 'searchable_fixtures';

    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'searchable_fixtures_index';
    }
}
