<?php

namespace Motor\Core\Filter\Renderers;

use Laravel\Scout\Builder;
use Motor\Core\Filter\Base;

class SortRenderer extends Base
{
    protected ?array $options = null;

    public function render()
    {
        return '';
    }

    public function query(\Illuminate\Database\Eloquent\Builder|Builder $query): object
    {
        if ($this->getValue()) {
            $value = explode(':', $this->getValue());
            if (count($value) > 1) {
                return $query->orderBy($value[0], $value[1]);
            }

            return $query->orderBy($value[0]);
        }

        return $query;
    }
}
