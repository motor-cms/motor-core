<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Filter;

trait Filterable
{

    public function scopeFilteredBy(Builder $scope, Filter $filter, $column)
    {
        // Get current filter value
        $currentFilter = $filter->get($column);
        if (!is_null($currentFilter) && !is_null($currentFilter->getValue())) {
            return $scope->where($column, '=', $currentFilter->getValue());
        }

        return $scope;
    }

    public function scopeFilteredByMultiple(Builder $scope, Filter $filter)
    {
        foreach ($filter->filters() as $name => $filter) {
            if ($name == 'per_page') {
                continue;
            }
            if ( ! is_null($filter->getValue()) || $filter->getAllowNull() == true) {
                $scope = $filter->query($scope);
            }
        }

        return $scope;
    }

}