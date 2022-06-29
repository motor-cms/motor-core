<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;

/**
 * Class SelectRenderer
 */
class SelectRenderer extends Base
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $operator = '=';

    /**
     * Render the filter
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        if (! is_null($this->optionPrefix)) {
            foreach ($this->options as $key => $value) {
                $this->options[$key] = $this->optionPrefix.': '.$value;
            }
        }

        if ($this->visible) {
            return view('motor-backend::filters.select', [
                'name'              => $this->name,
                'options'           => $this->options,
                'value'             => $this->getValue(),
                'emptyOptionString' => $this->emptyOptionString,
            ]);
        }
    }

    /**
     * Run query for the filter
     *
     * @param $query
     * @return object
     */
    public function query(Builder $query): object
    {
        return $query->where($query->getModel()->getTable().'.'.$this->field, $this->operator, $this->getValue());
    }
}
