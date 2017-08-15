<?php

namespace Motor\Core\Filter\Renderers;

class WhereRenderer extends SelectRenderer
{
    protected $options = null;

    public function render()
    {
        return '';
    }


    public function query($query)
    {
        if ($this->operator == 'IN') {
            return $query->whereIn($query->getModel()->getTable().'.'.$this->field, $this->getValue());
        } else {
            return $query->where($query->getModel()->getTable().'.'.$this->field, $this->operator, $this->getValue());
        }
    }
}