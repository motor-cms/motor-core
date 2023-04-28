<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class WhereRenderer
 */
class WhereRenderer extends SelectRenderer
{
    protected $options = null;

    /**
     * Render the filter
     *
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * Run query for the filter
     */
    public function query(Builder $query): object
    {
        if ($this->operator === 'IN') {
            return $query->whereIn($query->getModel()
                ->getTable().'.'.$this->field, $this->getValue());
        } else {
            return $query->where($query->getModel()
                ->getTable().'.'.$this->field, $this->operator, $this->getValue());
        }
    }
}
