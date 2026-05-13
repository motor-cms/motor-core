<?php

namespace Motor\Core\Filter\Renderers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Motor\Core\Filter\Base;
use Motor\Core\Search\ClientScopedSearch;
use Motor\Core\Traits\BelongsToClient;

class SearchRenderer extends Base
{
    protected array $searchableColumns = [];

    protected array $searchOptions = [];

    /**
     * Render the filter
     *
     * @return Application|Factory|View
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

    public function setSearchOptions($options): void
    {
        $this->searchOptions = $options;
    }

    /**
     * Run query for the filter
     */
    public function query(Builder $query): object
    {
        // If we're using scout
        // FIXME: try to find a better method of finding out if we're using scout or not
        if (method_exists($query->getModel(), 'getScoutModelsByIds')) {
            $modelClass = $query->getModel()::class;

            // Tenanted models route through ClientScopedSearch so V2 requests
            // get the same client filter that the Eloquent global scope applies.
            // V1 / public / console paths leave the resolver unbound, so the
            // helper transparently falls back to bare ::search().
            if (in_array(BelongsToClient::class, class_uses_recursive($modelClass), true)) {
                return ClientScopedSearch::for($modelClass, $this->getValue(), $modelClass::clientForeignKeyName())
                    ->options($this->searchOptions);
            }

            return $modelClass::search($this->getValue())->options($this->searchOptions);
        }

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
