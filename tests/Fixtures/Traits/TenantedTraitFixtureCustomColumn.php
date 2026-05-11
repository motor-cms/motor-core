<?php

namespace Motor\Core\Test\Fixtures\Traits;

use Illuminate\Database\Eloquent\Model;
use Motor\Core\Traits\BelongsToClient;

class TenantedTraitFixtureCustomColumn extends Model
{
    use BelongsToClient;

    protected $table = 'custom_column_fixtures';

    protected $guarded = [];

    public $timestamps = false;

    public static function clientForeignKeyName(): string
    {
        return 'approved_by_client_id';
    }
}
