<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class WhereRenderer
 * @package Motor\Core\Filter\Renderers
 */
class WhereRenderer extends SelectRenderer
{
    protected $options = null;


    /**
     * Render the filter
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function render()
    {
        return '';
    }


    /**
     * Run query for the filter
     *
     * @param $query
     * @return object
     */
    public function query(Builder $query): object
    {
        if ($this->operator === 'IN') {
            return $query->whereIn($query->getModel()->getTable() . '.' . $this->field, $this->getValue());
        } else {
            return $query->where(
                $query->getModel()->getTable() . '.' . $this->field,
                $this->operator,
                $this->getValue()
            );
        }
    }
}
