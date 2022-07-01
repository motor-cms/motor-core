<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

/**
 * Class PerPageRenderer
 */
class PerPageRenderer extends Base
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * Set up filter with initial values
     *
     * @param  int[]  $options
     * @param  int  $defaultValue
     */
    public function setup($options = [25 => 25, 50 => 50, 100 => 100, 200 => 200, 500 => 500], $defaultValue = 25): void
    {
        $this->options = $options;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Render the filter
     *
     * @return string
     */
    public function render(): string
    {
        return view(
            'motor-backend::filters.select',
            ['name' => $this->name, 'options' => $this->options, 'value' => $this->getValue()]
        );
    }
}
