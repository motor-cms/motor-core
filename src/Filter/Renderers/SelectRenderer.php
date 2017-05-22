<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

class SelectRenderer extends Base
{

    protected $options = [];

    protected $operator = '=';


    public function render()
    {
        if ($this->visible) {
            return view('motor-backend::filters.select',
                [ 'name' => $this->name, 'options' => $this->options, 'value' => $this->getValue() ]);
        }

    }


    public function query($query)
    {
        return $query->where($query->getModel()->getTable().'.'.$this->field, $this->operator, $this->getValue());
    }
}