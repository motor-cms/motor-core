<?php

namespace Motor\Core\Filter\Renderers;

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

    protected $relationField = null;

    /**
     * Base constructor.
     */
    public function __construct($name, $relationField = null)
    {
        $this->relationField = $relationField;
        parent::__construct($name);
    }

    /**
     * Run query for the filter
     */
    public function query(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $query): object
    {
        if ($query instanceof \Illuminate\Database\Eloquent\Builder) {
            $relationField = $this->relationField ?? Str::singular($query->getModel()->getTable()).'_id';

            return $query->join($this->join.' as '.$this->join, $relationField, $query->getModel()->getTable().'.id')->where(
                $this->join.'.'.$this->field,
                $this->getValue()
            );
        }

        return $query->where('categories', $this->getValue());
    }
}
