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
                if (is_null($this->getValue() || is_numeric($this->getValue()))) {
                    $value = (int) $this->getValue();
                } else if ($this->getValue() === true) {
                    $value = 1;
                } else {
                    $value = $this->getValue();
                }

                if ($this->operator === '!=') {
                    return $query->whereNotIn($field, [addslashes($value), addslashes($this->getValue())]);
                }

                // Fixme: this should not be necessary but somehow it is...
                if (is_null($value)) {
                    $value = (int)$value;
                }

                return $query->where($field, $value);
            }
        }
    }
}
