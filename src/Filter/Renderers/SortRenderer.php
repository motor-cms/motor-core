<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;

class SortRenderer extends Base
{
    protected $options = null;

    public function render()
    {
        return '';
    }

    public function query(\Illuminate\Database\Eloquent\Builder | \Laravel\Scout\Builder $query): object
    {
        if ($this->getValue()) {
            $value = explode(":",$this->getValue());
            $test = ["tset"];
            if(count($value) > 1) {
                return $query->orderBy($value[0], $value[1]);
            } else {
                return $query->orderBy($value[0]);
            }
        }
    }
}
