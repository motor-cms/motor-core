<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Support\Str;
use Laravel\Scout\Builder;

class RelationRenderer extends SelectRenderer
{
    protected ?array $options = null;

    protected ?string $relationField = null;

    public function __construct(string $name, ?string $relationField = null)
    {
        $this->relationField = $relationField;
        parent::__construct($name);
    }

    /**
     * Run query for the filter
     */
    public function query(\Illuminate\Database\Eloquent\Builder|Builder $query): object
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
