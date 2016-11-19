<?php

namespace Motor\Core\Grid\Renderers;

class TextRenderer
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
        return $this->value;
    }
}