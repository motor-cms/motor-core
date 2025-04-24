<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;

/**
 * Class SearchRenderer
 */
class SearchRenderer extends Base
{
    /**
     * @var array
     */
    protected $searchableColumns = [];

    /**
     * Render the filter
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('motor-backend::filters.search', ['value' => $this->getValue()]);
    }

    /**
     * Set searchable columns for filter
     */
    public function setSearchableColumns($columns): void
    {
        $this->searchableColumns = $columns;
    }

    /**
     * Run query for the filter
     */
    public function query(Builder $query): object
    {
        if (method_exists($query->getModel(), 'scopeSearch')) {
            return $query->search($this->getValue());
        } else {
            // Fallback solution in case the searchable trait is not available but we still want to search through the basic columns (see Navigation model in motor-cms for an example)
            if (count($this->searchableColumns) === 0) {
                return $query;
            }
            $searchableColumns = $this->searchableColumns;
            $value = $this->getValue();

            return $query->orWhere(static function ($query) use ($searchableColumns, $value) {
                foreach ($searchableColumns as $column) {
                    $query->where($column, 'LIKE', '%'.$value.'%');
                }
            });
        }
    }
}
