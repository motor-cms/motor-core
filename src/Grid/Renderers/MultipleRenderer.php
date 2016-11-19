<?php

namespace Motor\Core\Grid\Renderers;

class MultipleRenderer
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
        if (!is_array($this->value)) {
            return '';
        }
        return implode(', ', $this->value);
    }
}