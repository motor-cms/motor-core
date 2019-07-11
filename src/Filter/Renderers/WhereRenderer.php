<?php

namespace Motor\Core\Filter\Renderers;

/**
 * Class WhereRenderer
 * @package Motor\Core\Filter\Renderers
 */
class WhereRenderer extends SelectRenderer
{

    protected $options = null;


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function render()
    {
        return '';
    }


    /**
     * @param $query
     *
     * @return object
     */
    public function query($query): object
    {
        if ($this->operator === 'IN') {
            return $query->whereIn($query->getModel()->getTable() . '.' . $this->field, $this->getValue());
        } else {
            return $query->where($query->getModel()->getTable() . '.' . $this->field, $this->operator,
                $this->getValue());
        }
    }
}