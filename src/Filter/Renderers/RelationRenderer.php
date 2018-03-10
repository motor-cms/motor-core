<?php

namespace Motor\Core\Filter\Renderers;

class RelationRenderer extends SelectRenderer
{

    protected $options = null;


    public function query($query)
    {
        return $query->join($this->join . ' as ' . $this->join, str_singular($query->getModel()->getTable()) . '_id',
            $query->getModel()->getTable() . '.id')->where($this->join . '.' . $this->field, $this->getValue());

    }
}