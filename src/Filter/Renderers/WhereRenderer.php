<?php

namespace Motor\Core\Filter\Renderers;

class WhereRenderer extends SelectRenderer
{

    protected $options = null;


    /**
     * @return object|null
     */
    public function render(): ?object
    {
        return '';
    }


    /**
     * @param $query
     * @return object
     */
    public function query($query): object
    {
        if ($this->operator === 'IN') {
            return $query->whereIn($query->getModel()->getTable() . '.' . $this->field, $this->getValue());
        } else {
            return $query->where($query->getModel()->getTable() . '.' . $this->field, $this->operator, $this->getValue());
        }
    }
}