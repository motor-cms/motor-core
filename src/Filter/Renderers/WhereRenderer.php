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
    public function query(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $query): object
    {
        if ($query instanceof Builder) {
            $field = $query->getModel()->getTable().'.'.$this->field;
        } else {
            $field = $this->field;
        }

        if ($this->operator === 'IN') {
            return $query->whereIn($field, $this->getValue());
        } else {
            if ($query instanceof Builder) {
                return $query->where($field, $this->operator, $this->getValue());
            } else {
                // Scout cannot use operators other than '=' and needs to use integers for booleans
                return $query->where($field, (int)$this->getValue());
            }
        }
    }
}
