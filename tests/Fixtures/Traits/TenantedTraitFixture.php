<?php

namespace Motor\Core\Test\Fixtures\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Motor\Core\Traits\BelongsToClient;

class TenantedTraitFixture extends Model
{
    use BelongsToClient;
    use Searchable;

    protected $table = 'tenanted_trait_fixtures';

    protected $guarded = [];

    public $timestamps = false;

    public function searchableAs(): string
    {
        return 'tenanted_trait_fixtures_index';
    }
}
