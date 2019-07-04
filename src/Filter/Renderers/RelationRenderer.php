<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Support\Str;

class RelationRenderer extends SelectRenderer
{

    /**
     * @var null
     */
    protected $options = null;


    /**
     * @param $query
     * @return object
     */
    public function query($query): object
    {
        return $query->join($this->join . ' as ' . $this->join, Str::singular($query->getModel()->getTable()) . '_id', $query->getModel()->getTable() . '.id')->where($this->join . '.' . $this->field,
            $this->getValue());
    }
}