<?php

namespace Motor\Core\Test\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Model;

class DeniesFixture extends Model
{
    protected $table = 'denies_fixtures';

    public $timestamps = false;

    protected $guarded = [];
}
