<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Core\Filter\Filter;

trait Filterable
{
    public function scopeFilteredBy(Builder $scope, Filter $filter, string $column): Builder
    {
        $currentFilter = $filter->get($column);
        if (! is_null($currentFilter) && ! is_null($currentFilter->getValue())) {
            return $scope->where($scope->getModel()
                ->getTable().'.'.$column, '=', $currentFilter->getValue());
        }

        return $scope;
    }

    public function scopeFilteredByMultiple(Builder|ScoutBuilder $scope, Filter $filter): Builder|ScoutBuilder
    {
        foreach ($filter->filters() as $name => $filterItem) {

            if ($name === 'per_page') {
                continue;
            }

            if ($name === 'search' && is_null($filterItem->getValue())) {
                continue;
            }

            if (! is_null($filterItem->getValue()) || $filterItem->getAllowNull() === true) {
                $scope = $filterItem->query($scope);
            }
        }

        return $scope;
    }
}
