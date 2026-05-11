<?php

namespace Motor\Core\Test\Fixtures\Traits;

use Illuminate\Database\Eloquent\Model;

class TenantedClientAccessFixture extends Model
{
    protected $table = 'tenanted_client_access_fixtures';

    public $timestamps = false;

    protected $guarded = [];
}
