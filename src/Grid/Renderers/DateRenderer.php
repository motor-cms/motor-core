<?php

namespace Motor\Core\Grid\Renderers;

class DateRenderer
{

    protected $value = '';

    protected $options = [ ];

    protected $defaultFormat = 'Y-m-d H:i:s';


    public function __construct($value, $options = [])
    {
        $this->value   = $value;
        $this->options = $options;
    }


    public function render()
    {
        if ($this->value == '' || $this->value == null) {
            return '';
        }
        if (isset($this->options['format'])) {
            return date($this->options['format'], strtotime($this->value));
        }
        return date($this->defaultFormat, strtotime($this->value));
    }
}