<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Trait Searchable
 *
 * @package Motor\Core\Searchable
 */
trait Searchable
{

    /**
     * @var array
     */
    protected $joins = [];


    /**
     * full search base on table field and relation fields
     *
     * @param Builder $builder
     * @param         $query
     * @param bool    $full_text
     * @return Builder|null
     */
    public function scopeSearch(Builder $builder, $query, $full_text = false): ?Builder
    {
        $result = null;

        if (strlen($query) === 0) {
            return $builder;
        }

        // Remove sql injection possibilities
        $query = Str::replace('%', $query);

        $searchType = 'LIKE';
        $search     = $full_text ? trim($query) : '%' . trim($query) . '%';

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
                $words[] = '*' . trim($term) . '*';
            }
        }

        if (count($terms) > 1) {
            $searchType = 'REGEXP';
            $search     = implode('|', $terms);
        }

        $columns = $this->searchableColumns;

        if (isset($columns) && count($columns) > 0) {
            $cases = $bindings = [];
            foreach ($columns as $column) {
                list($cases[], $binding) = $this->buildCase($column, $words);
                $bindings = array_merge_recursive($bindings, $binding);
            }

            $builder->select($builder->getModel()->getTable() . '.*');
            $builder->selectRaw("max(" . implode(' + ', $cases) . ") as relevance");
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
            $result->orderBy('relevance', 'DESC')->groupBy($builder->getModel()->getTable() . '.id');
        }

        return $result;
    }


    /**
     * check if field is for its table or related table and generate the search query
     *
     * @param Builder $builder
     * @param         $searchType
     * @param         $query
     * @param         $field
     * @param bool    $first
     * @return Builder
     */
    public function performSearch(Builder $builder, $searchType, $query, $field, $first = false): Builder
    {
        $where = $first ? 'where' : 'orWhere';
        if (strpos($field, '.') === false) {
            return $builder->$where($field, $searchType, $query);
        //return $result->orWhere($field, $searchType, $q);
        } else {
            [$table, $field] = explode('.', $field);
            if ($table === $builder->getModel()->getTable()) {
                return $builder->$where($table . '.' . $field, $searchType, $query);
            }

            $where .= 'Has';

            if (! in_array($table, $this->joins)) {
                $builder->join(Str::plural($table) . ' as ' . $table, $table . '_id', $table . '.id');
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
     *
     * @param       $column
     * @param array $words
     * @return array
     */
    protected function buildCase($column, array $words): array
    {
        // THIS IS BAD
        // @todo refactor
        $operator           = 'LIKE';
        $bindings           = [];
        $bindings['select'] = $bindings['where'] = array_map(static function ($word) {
            return str_replace('*', '', $word);
        }, $words);
        $case               = $this->buildEqualsCase($column, $words);
        if (strpos(implode('', $words), '*') !== false) {
            $leftMatching = [];
            foreach ($words as $key => $word) {
                if ($this->isLeftMatching($word)) {
                    $columns = explode('.', $column);
                    foreach ($columns as $columnKey => $col) {
                        $columns[$columnKey] = '`' . $col . '`';
                    }
                    $escapedColumn        = implode('.', $columns);
                    $leftMatching[]       = sprintf('%s %s ?', $escapedColumn, $operator);
                    $bindings['select'][] = $bindings['where'][$columnKey] = $this->caseBinding($word) . '%';
                }
            }
            if (count($leftMatching)) {
                $leftMatching = implode(' or ', $leftMatching);
                $score        = 5;
                $case         .= " + case when {$leftMatching} then {$score} else 0 end";
            }
            $wildcards = [];
            foreach ($words as $key => $word) {
                if ($this->isWildcard($word)) {
                    $columns = explode('.', $column);
                    foreach ($columns as $columnKey => $col) {
                        $columns[$columnKey] = '`' . $col . '`';
                    }
                    $escapedColumn        = implode('.', $columns);
                    $wildcards[]          = sprintf('%s %s ?', $escapedColumn, $operator);
                    $bindings['select'][] = $bindings['where'][$columnKey] = '%' . $this->caseBinding($word) . '%';
                }
            }
            if (count($wildcards)) {
                $wildcards = implode(' or ', $wildcards);
                $score     = 1;
                $case      .= " + case when {$wildcards} then {$score} else 0 end";
            }
        }

        return [$case, $bindings];
    }


    /**
     * Determine whether word starts and ends with wildcards.
     *
     * @param string $word
     * @return bool
     */
    protected function isWildcard($word): bool
    {
        return Str::endsWith($word, '*') && Str::startsWith($word, '*');
    }


    /**
     * Build basic search case for 'equals' comparison.
     *
     * @param       $column
     * @param array $words
     * @return string
     */
    protected function buildEqualsCase($column, array $words): string
    {
        $columns = explode('.', $column);
        foreach ($columns as $key => $col) {
            $columns[$key] = '`' . $col . '`';
        }
        $escapedColumn = implode('.', $columns);

        $equals = implode(' or ', array_fill(0, count($words), sprintf('%s = ?', $escapedColumn)));
        $score  = 15;

        return "case when {$equals} then {$score} else 0 end";
    }


    /**
     * Determine whether word ends with wildcard.
     *
     * @param string $word
     * @return bool
     */
    protected function isLeftMatching($word): bool
    {
        return Str::endsWith($word, '*');
    }


    /**
     * Replace '?' with single character SQL wildcards.
     *
     * @param string $word
     * @return string
     */
    protected function caseBinding($word): string
    {
        return str_replace('?', '_', str_replace('*', '', $word));
    }
}
