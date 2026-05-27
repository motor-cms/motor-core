<?php

namespace Motor\Core\Test\Fixtures\Traits;

use Illuminate\Database\Eloquent\Model;
use Motor\Core\Traits\AuthorizesClientAccess;

class FixturePolicyForClientAccess
{
    use AuthorizesClientAccess;

    public function deny(?Model $model, string $column = 'client_id'): bool
    {
        return $this->denyForeignClient($model, $column);
    }
}
