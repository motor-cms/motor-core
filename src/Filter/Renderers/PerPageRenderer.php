<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

class PerPageRenderer extends Base
{

    /**
     * @var array
     */
    protected $options = [];


    /**
     * @param array $options
     * @param int   $defaultValue
     */
    public function setup($options = [25 => 25, 50 => 50, 100 => 100, 200 => 200], $defaultValue = 25): void
    {
        $this->options      = $options;
        $this->defaultValue = $defaultValue;
    }


    /**
     * @return string
     */
    public function render(): string
    {
        return view('motor-backend::filters.select', ['name' => $this->name, 'options' => $this->options, 'value' => $this->getValue()]);
    }
}