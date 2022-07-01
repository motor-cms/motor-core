<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Filter;

/**
 * Trait Filterable
 */
trait Filterable
{
    /**
     * Set up scope
     *
     * @param  Builder  $scope
     * @param  Filter  $filter
     * @param    $column
     * @return Builder
     */
    public function scopeFilteredBy(Builder $scope, Filter $filter, $column): Builder
    {
        // Get current filter value
        $currentFilter = $filter->get($column);
        if (! is_null($currentFilter) && ! is_null($currentFilter->getValue())) {
            return $scope->where($scope->getModel()
                                       ->getTable().'.'.$column, '=', $currentFilter->getValue());
        }

        return $scope;
    }

    /**
     * Set up scope for filtering multiple fields in the same query
     *
     * @param  Builder  $scope
     * @param  Filter  $filter
     * @return Builder
     */
    public function scopeFilteredByMultiple(Builder $scope, Filter $filter): Builder
    {
        foreach ($filter->filters() as $name => $filter) {
            if ($name === 'per_page') {
                continue;
            }
            if (! is_null($filter->getValue()) || $filter->getAllowNull() === true) {
                $scope = $filter->query($scope);
            }
        }

        return $scope;
    }
}
