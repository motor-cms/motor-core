<?php

namespace Motor\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Searchable
 * @package Motor\Core\Searchable
 */
trait Searchable
{

    /**
     * full search base on table field and relation fields
     *
     * @param Builder $builder
     * @param $q
     * @param bool $full_text
     * @return Builder|null
     */
    public function scopeSearch(Builder $builder, $q, $full_text = false)
    {
        $result = null;

        if (strlen($q) == 0)
            return $builder;

        $columns = $this->searchableColumns;
        $search = $full_text ?  trim($q) : '%' . trim($q) . '%';
        if (isset($columns) && count($columns) > 0) {
            $result = $this->performSearch($builder, $search, head($columns), true);
            if(count($columns) > 1)
                foreach ($columns as $column) {
                    $result = $this->performSearch($builder, $search, $column);
                }
        }

        return $result;
    }

    /**
     * check if field is for its table or related table and generate the search query
     *
     * @param Builder $builder
     * @param $q
     * @param $field
     * @param bool $first
     * @return mixed
     */
    public function performSearch(Builder $builder, $q, $field ,$first = false)
    {
        $where = $first ? 'where' : 'orWhere';
        if (strpos($field, '.') == false) {
            $result = $builder->$where($field, 'LIKE', $q);
            return $result->orWhere($field, 'LIKE', $q);
        }
        else
        {
            list($table,$field) = explode('.', $field);
            $where = $where.'Has';
            return $builder->$where($table, function($query) use($field, $q){
                $query->where($field, 'LIKE', $q);
                $query->orWhere($field, 'LIKE', $q);
            });
        }
    }
    
}