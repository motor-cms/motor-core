<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

class PerPageRenderer extends Base
{

    protected $options = [];


    public function setup($options = [25 => 25, 50 => 50, 100 => 100, 200 => 200], $defaultValue = 25)
    {
        $this->options      = $options;
        $this->defaultValue = $defaultValue;
    }


    public function render()
    {
        return view('motor-backend::filters.select',
            [ 'name' => $this->name, 'options' => $this->options, 'value' => $this->getValue() ]);

    }
}