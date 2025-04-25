<?php

namespace Motor\Core\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable as ScoutSearch;

/**
 * Trait Searchable
 */
trait Searchable
{
    // use ScoutSearch;
    /**
     * @var array
     */
    protected $joins = [];

    /**
     * full search base on table field and relation fields
     *
     * @param  bool  $full_text
     */
    public function scopeSearch(Builder $builder, $query, $full_text = false): ?Builder
    {
        $result = null;

        if (strlen($query) === 0) {
            return $builder;
        }

        // Remove sql injection possibilities
        $query = Str::replace('%', '', $query);

        $searchType = 'LIKE';
        $search = $full_text ? trim($query) : '%'.trim($query).'%';

        $terms = explode(' ', $query);

        // Filter empty terms
        foreach ($terms as $termKey => $term) {
            if (trim($term) === '') {
                unset($terms[$termKey]);
            }
        }

        $words = [];
        foreach ($terms as $term) {
            if (trim($term) !== '') {
                $words[] = '*'.trim($term).'*';
            }
        }

        if (count($terms) > 1) {
            $searchType = 'REGEXP';
            $search = implode('|', $terms);
        }

        $columns = $this->searchableColumns;

        if (isset($columns) && count($columns) > 0) {
            $cases = $bindings = [];
            foreach ($columns as $column) {
                [$cases[], $binding] = $this->buildCase($column, $words);
                $bindings = array_merge_recursive($bindings, $binding);
            }

            $builder->select($builder->getModel()
                ->getTable().'.*');
            $builder->selectRaw('max('.implode(' + ', $cases).') as relevance');
            $builder->addBinding($bindings['select'], 'select');

            foreach ($columns as $key => $column) {
                if ($key === 0) {
                    $temporaryResult = $this->performSearch($builder, $searchType, $search, $column, true);
                    if (count($columns) === 1) {
                        $result = $temporaryResult;
                    }
                } else {
                    $result = $this->performSearch($builder, $searchType, $search, $column);
                }
            }
        }

        if (! is_null($result)) {
            $result->orderByDesc('relevance')
                ->groupBy($builder->getModel()
                    ->getTable().'.id');
        }

        return $result;
    }

    /**
     * check if field is for its table or related table and generate the search query
     *
     * @param  bool  $first
     */
    public function performSearch(Builder $builder, $searchType, $query, $field, $first = false): Builder
    {
        $where = $first ? 'where' : 'orWhere';
        if (strpos($field, '.') === false) {
            return $builder->$where($field, $searchType, $query);
            // return $result->orWhere($field, $searchType, $q);
        } else {
            [$table, $field] = explode('.', $field);
            if ($table === $builder->getModel()
                ->getTable()) {
                return $builder->$where($table.'.'.$field, $searchType, $query);
            }

            $where .= 'Has';

            if (! in_array($table, $this->joins)) {
                $builder->join(Str::plural($table).' as '.$table, $table.'_id', $table.'.id');
                $this->joins[] = $table;
            }

            return $builder->$where($table, static function ($builder) use ($field, $query, $searchType): void {
                $builder->where($field, $searchType, $query);
                $builder->orWhere($field, $searchType, $query);
            });
        }
    }

    /**
     * Build case clause from all words for a single column.
     */
    protected function buildCase($column, array $words): array
    {
        // THIS IS BAD
        // @todo refactor
        $operator = 'LIKE';
        $bindings = [];
        $bindings['select'] = $bindings['where'] = array_map(static function ($word) {
            return str_replace('*', '', $word);
        }, $words);
        $case = $this->buildEqualsCase($column, $words);
        if (strpos(implode('', $words), '*') !== false) {
            $leftMatching = [];
            foreach ($words as $key => $word) {
                if ($this->isLeftMatching($word)) {
                    $columns = explode('.', $column);
                    foreach ($columns as $columnKey => $col) {
                        $columns[$columnKey] = '`'.$col.'`';
                    }
                    $escapedColumn = implode('.', $columns);
                    $leftMatching[] = sprintf('%s %s ?', $escapedColumn, $operator);
                    $bindings['select'][] = $bindings['where'][$columnKey] = $this->caseBinding($word).'%';
                }
            }
            if (count($leftMatching)) {
                $leftMatching = implode(' or ', $leftMatching);
                $score = 5;
                $case .= " + case when {$leftMatching} then {$score} else 0 end";
            }
            $wildcards = [];
            foreach ($words as $key => $word) {
                if ($this->isWildcard($word)) {
                    $columns = explode('.', $column);
                    foreach ($columns as $columnKey => $col) {
                        $columns[$columnKey] = '`'.$col.'`';
                    }
                    $escapedColumn = implode('.', $columns);
                    $wildcards[] = sprintf('%s %s ?', $escapedColumn, $operator);
                    $bindings['select'][] = $bindings['where'][$columnKey] = '%'.$this->caseBinding($word).'%';
                }
            }
            if (count($wildcards)) {
                $wildcards = implode(' or ', $wildcards);
                $score = 1;
                $case .= " + case when {$wildcards} then {$score} else 0 end";
            }
        }

        return [$case, $bindings];
    }

    /**
     * Determine whether word starts and ends with wildcards.
     *
     * @param  string  $word
     */
    protected function isWildcard($word): bool
    {
        return Str::endsWith($word, '*') && Str::startsWith($word, '*');
    }

    /**
     * Build basic search case for 'equals' comparison.
     */
    protected function buildEqualsCase($column, array $words): string
    {
        $columns = explode('.', $column);
        foreach ($columns as $key => $col) {
            $columns[$key] = '`'.$col.'`';
        }
        $escapedColumn = implode('.', $columns);

        $equals = implode(' or ', array_fill(0, count($words), sprintf('%s = ?', $escapedColumn)));
        $score = 15;

        return "case when {$equals} then {$score} else 0 end";
    }

    /**
     * Determine whether word ends with wildcard.
     *
     * @param  string  $word
     */
    protected function isLeftMatching($word): bool
    {
        return Str::endsWith($word, '*');
    }

    /**
     * Replace '?' with single character SQL wildcards.
     *
     * @param  string  $word
     */
    protected function caseBinding($word): string
    {
        return str_replace('?', '_', str_replace('*', '', $word));
    }

    /**
     * Checks if the given field name is searchable
     */
    private function isFieldSearchable(string $field): bool
    {
        static $columns;
        if (! isset($columns)) {
            $columns = [];
        }
        $id = sprintf('%s-%s', $this->getTable(), $this->getConnectionName());

        // No longer necessary as we do not depend on Doctrine in Laravel 11 anymore
        // if (! isset($columns[$id])) {
        //    $columns[$id] = array_keys($this->getConnection()->getDoctrineSchemaManager()->listTableColumns($this->getTable()));
        // }

        return in_array($field, $columns[$id]);
    }

    /**
     * Applies the relevant where calls
     * from the given search query
     */
    public static function applySearchQuery(Builder $query, array $searchQuery): Builder
    {
        $instance = new self;
        $dates = $instance->getDates();

        /**
         * Helper function to apply a group of
         * AND-WHERE queries to the given builder
         *
         * @param  $query
         * @param  $group
         * @return mixed
         */
        $applyGroup = function ($query, $group) use ($dates) {
            foreach ($group as $search) {
                $value = $search['value'];
                if (in_array($search['field'], $dates)) {
                    $value = new Carbon($value);
                    $query = $query->whereDate($search['field'], strtoupper($search['operation']), $value);
                } else {
                    $query = $query->where($search['field'], strtoupper($search['operation']), $value);
                }
            }

            return $query;
        };

        // Basic Search
        if (isset($searchQuery['search']) && ! is_null($searchQuery['search'])) {
            $query = $applyGroup($query, $searchQuery['search']);

        } // OR Search Fields
        elseif (isset($searchQuery['queries']) && ! is_null($searchQuery['queries'])) {
            foreach ($searchQuery['queries'] as $group) {
                $query = $query->orWhere(function ($q) use ($group, $applyGroup) {
                    $applyGroup($q, $group);
                });
            }
        }

        return $query;
    }

    /**
     * Validates the given search data and returns
     * the validated fields
     */
    public static function validateSearchQuery(Request $request): array
    {
        $instance = new self;

        $fieldSearchable = function ($field, $value, $fail) use ($instance) {
            if (! $instance->isFieldSearchable($value)) {
                $fail(sprintf('%s is not a searchable field', $value));
            }
        };

        return $request->validate([
            'per_page' => 'numeric',
            'page' => 'numeric',

            // Basic Search
            'search' => 'required_without:queries|array',
            'search.*.field' => [
                'required',
                $fieldSearchable,
            ],
            'search.*.operation' => 'required|in:=,<,>,<=,>=,!=,like',
            'search.*.value' => 'present',

            // OR Search Fields
            'queries' => 'required_without:search|array',
            'queries.*' => 'array',
            'queries.*.*.field' => [
                'required',
                $fieldSearchable,
            ],
            'queries.*.*.operation' => 'required|in:=,<,>,<=,>=,!=,like',
            'queries.*.*.value' => 'present',
        ]);
    }
}
