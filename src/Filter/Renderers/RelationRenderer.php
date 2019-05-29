<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Support\Str;

class RelationRenderer extends SelectRenderer
{

    protected $options = null;


    public function query($query)
    {
        return $query->join($this->join . ' as ' . $this->join, Str::singular($query->getModel()->getTable()) . '_id',
            $query->getModel()->getTable() . '.id')->where($this->join . '.' . $this->field, $this->getValue());

    }
}