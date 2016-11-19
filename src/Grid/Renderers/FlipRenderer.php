<?php

namespace Motor\Core\Grid\Renderers;

class FlipRenderer
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
            return '<button class="btn btn-xs btn-default">'.trans('backend/global.yes').'</button>';
        }
        return '<button class="btn btn-xs btn-default">'.trans('backend/global.no').'</button>';
    }
}