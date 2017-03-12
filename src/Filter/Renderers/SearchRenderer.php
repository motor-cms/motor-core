<?php

namespace Motor\Core\Filter\Renderers;

use Motor\Core\Filter\Base;

class SearchRenderer extends Base
{

    protected $searchableColumns = [];


    public function render()
    {
        return view('motor-backend::filters.search', [ 'value' => $this->getValue() ]);
    }


    public function setSearchableColumns($columns)
    {
        $this->searchableColumns = $columns;
    }


    public function query($query)
    {
        if (method_exists($query->getModel(), 'scopeSearch')) {
            return $query->search($this->getValue());
        } else {
            // Fallback solution in case the searchable trait is not available but we still want to search through the basic columns (see Navigation model in motor-cms for an example)
            if (count($this->searchableColumns) == 0) {
                return $query;
            }
            $searchableColumns = $this->searchableColumns;
            $value             = $this->getValue();

            return $query->orWhere(function ($query) use ($searchableColumns, $value) {
                foreach ($searchableColumns as $column) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            });
        }
    }
}