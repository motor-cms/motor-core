<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Class RelationRenderer
 */
class RelationRenderer extends SelectRenderer
{
    /**
     * @var null
     */
    protected $options = null;

    /**
     * Run query for the filter
     */
    public function query(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $query): object
    {
        if ($query instanceof Builder) {
            return $query->join($this->join.' as '.$this->join, Str::singular($query->getModel()->getTable()).'_id', $query->getModel()->getTable().'.id')->where(
                $this->join.'.'.$this->field,
                $this->getValue()
            );
        }
        return $query->where('categories', $this->getValue());
    }
}
