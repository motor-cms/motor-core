<?php

namespace Motor\Core\Test\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Model;

class CustomColumnFixtureModel extends Model
{
    protected $table = 'approval_fixtures';

    public $timestamps = false;
}
