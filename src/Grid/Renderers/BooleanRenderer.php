<?php

namespace Motor\Core\Grid\Renderers;

class BooleanRenderer
{

    protected $value = '';

    protected $options = [ ];


    public function __construct($value, $options)
    {
        $this->value   = $value;
        $this->options = $options;
    }


    public function render()
    {
        if ($this->value == true) {
            return trans('backend/global.yes');
        }
        return trans('backend/global.no');
    }
}