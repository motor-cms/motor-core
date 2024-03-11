<?php

namespace Motor\Core\Traits;

use Motor\Core\Filter\Filter;

/**
 * Trait Filterable
 */
trait Filterable
{
    /**
     * Set up scope
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
     */
    public function scopeFilteredByMultiple(\Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder $scope, Filter $filter): \Illuminate\Database\Eloquent\Builder|\Laravel\Scout\Builder
    {
        foreach ($filter->filters() as $name => $filter) {

            if ($name === 'per_page') {
                continue;
            }

            // Force using scout
            if ($name === 'search' && is_null($filter->getValue())) {
                // If we're using scout
                // FIXME: try to find a better method of finding out if we're using scout or not
                if (method_exists($scope->getModel(), 'getScoutModelsByIds')) {
                    $scope = $scope->getModel()::search($filter->getValue());
                }
            }

            if (! is_null($filter->getValue()) || $filter->getAllowNull() === true) {
                $scope = $filter->query($scope);
            }
        }

        return $scope;
    }
}
