<?php

namespace Motor\Core\Filter\Renderers;

class WhereRenderer extends SelectRenderer
{

    public function render()
    {
        return '';
    }


    public function query($query)
    {
        return $query->where($this->field, $this->operator, $this->getValue());
    }
}