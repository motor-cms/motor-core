<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

/**
 * Class SearchRenderer
 * @package Motor\Core\Filter\Renderers
 */
class SearchRenderer extends Base
{

    /**
     * @var array
     */
    protected $searchableColumns = [];


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('motor-backend::filters.search', ['value' => $this->getValue()]);
    }


    /**
     * @param $columns
     */
    public function setSearchableColumns($columns): void
    {
        $this->searchableColumns = $columns;
    }


    /**
     * @param $query
     * @return object
     */
    public function query($query): object
    {
        if (method_exists($query->getModel(), 'scopeSearch')) {
            return $query->search($this->getValue());
        } else {
            // Fallback solution in case the searchable trait is not available but we still want to search through the basic columns (see Navigation model in motor-cms for an example)
            if (count($this->searchableColumns) === 0) {
                return $query;
            }
            $searchableColumns = $this->searchableColumns;
            $value             = $this->getValue();

            return $query->orWhere(static function ($query) use ($searchableColumns, $value) {
                foreach ($searchableColumns as $column) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            });
        }
    }
}