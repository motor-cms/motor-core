<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Searchable
 * @package Motor\Core\Searchable
 */
trait Searchable
{

    protected $joins = [];

    /**
     * full search base on table field and relation fields
     *
     * @param Builder $builder
     * @param         $q
     * @param bool    $full_text
     *
     * @return Builder|null
     */
    public function scopeSearch(Builder $builder, $q, $full_text = false)
    {
        $result = null;

        if (strlen($q) == 0) {
            return $builder;
        }

        $searchType = 'LIKE';
        $search     = $full_text ? trim($q) : '%' . trim($q) . '%';

        $terms = explode(' ', $q);

        // Filter empty terms
        foreach ($terms as $termKey => $term) {
            if (trim($term) == '') {
                unset($terms[$termKey]);
            }
        }


        $words = [];
        foreach ($terms as $term) {
            if (trim($term) != '') {
                $words[] = '*'.trim($term).'*';
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

            $builder->select($builder->getModel()->getTable().'.*');
            $builder->selectRaw("max(" . implode(' + ', $cases) . ") as relevance");
            $builder->addBinding($bindings['select'], 'select');

            foreach ($columns as $key => $column) {
                if ($key == 0) {
                    $this->performSearch($builder, $searchType, $search, $column, true);
                } else {
                    $result = $this->performSearch($builder, $searchType, $search, $column);
                }
            }
        }

        if (!is_null($result)) {
            $result->orderBy('relevance', 'DESC')->groupBy($builder->getModel()->getTable().'.id');
        }

        return $result;
    }


    /**
     * check if field is for its table or related table and generate the search query
     *
     * @param Builder $builder
     * @param         $q
     * @param         $field
     * @param bool    $first
     *
     * @return mixed
     */
    public function performSearch(Builder $builder, $searchType, $q, $field, $first = false)
    {
        $where = $first ? 'where' : 'orWhere';
        if (strpos($field, '.') == false) {
            return $builder->$where($field, $searchType, $q);
            //return $result->orWhere($field, $searchType, $q);
        } else {
            list($table, $field) = explode('.', $field);
            if ($table  == $builder->getModel()->getTable()) {
                return $builder->$where($table.'.'.$field, $searchType, $q);
            }


            $where = $where . 'Has';

            if (!in_array($table, $this->joins)) {
                $builder->join(str_plural($table).' as '.$table, $table.'_id', $table.'.id');
                $this->joins[] = $table;
            }
            //$table = str_plural($table);

            //$builder->with($table);

            return $builder->$where($table, function ($query) use ($field, $q, $searchType) {
                $query->where($field, $searchType, $q);
                $query->orWhere($field, $searchType, $q);
            });
        }
    }


    /**
     * Build case clause from all words for a single column.
     *
     * @param  array  $words
     * @return array
     */
    protected function buildCase($column, array $words)
    {
        // THIS IS BAD
        // @todo refactor
        $operator = 'LIKE';
        $bindings['select'] = $bindings['where'] = array_map(function ($word) {
            return str_replace('*', '', $word);
        }, $words);
        $case = $this->buildEqualsCase($column, $words);
        if (strpos(implode('', $words), '*') !== false) {
            $leftMatching = [];
            foreach ($words as $key => $word) {
                if ($this->isLeftMatching($word)) {
                    $leftMatching[] = sprintf('%s %s ?', $column, $operator);
                    $bindings['select'][] = $bindings['where'][$key] = $this->caseBinding($word) . '%';
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
                    $wildcards[] = sprintf('%s %s ?', $column, $operator);
                    $bindings['select'][] = $bindings['where'][$key] = '%'.$this->caseBinding($word) . '%';
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
     * @return boolean
     */
    protected function isWildcard($word)
    {
        return ends_with($word, '*') && starts_with($word, '*');
    }

    /**
     * Build basic search case for 'equals' comparison.
     *
     * @param  $column
     * @param  array  $words
     * @return string
     */
    protected function buildEqualsCase($column, array $words)
    {
        $equals = implode(' or ', array_fill(0, count($words), sprintf('%s = ?', $column)));
        $score = 15;
        return "case when {$equals} then {$score} else 0 end";
    }

    /**
     * Determine whether word ends with wildcard.
     *
     * @param  string $word
     *
     * @return boolean
     */
    protected function isLeftMatching($word)
    {
        return ends_with($word, '*');
    }

    /**
     * Replace '?' with single character SQL wildcards.
     *
     * @param  string $word
     * @return string
     */
    protected function caseBinding($word)
    {
        return str_replace('?', '_', str_replace('*', '', $word));
    }

}