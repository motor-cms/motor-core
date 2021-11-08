<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;

/**
 * Class SelectRenderer
 *
 * @package Motor\Core\Filter\Renderers
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|void
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
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return object
     */
    public function query(Builder $query): object
    {
        return $query->where($query->getModel()
                                   ->getTable().'.'.$this->field, $this->operator, $this->getValue());
    }
}
