<?php

namespace Motor\Core\Grid\Renderers;

class TranslateRenderer
{

    protected $value = '';

    protected $options = [ ];

    protected $defaultFile = 'backend/global';


    public function __construct($value, $options)
    {
        $this->value   = $value;
        $this->options = $options;
    }


    public function render()
    {
        if (isset($this->options['file'])) {
            return trans($this->options['file'].'.'.$this->value);
        }
        return trans($this->defaultFile.'.'.$this->value);
    }
}