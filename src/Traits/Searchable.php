<?php

namespace Motor\Core\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait Searchable
{
    protected array $joins = [];

    public function scopeSearch(Builder $builder, string $query, bool $full_text = false): ?Builder
    {
        $result = null;

        if (strlen($query) === 0) {
            return $builder;
        }

        $searchType = 'LIKE';
        $search = $full_text ? trim($query) : '%'.trim($query).'%';

        $terms = explode(' ', $query);

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

    public function performSearch(Builder $builder, string $searchType, string $query, string $field, bool $first = false): Builder
    {
        $where = $first ? 'where' : 'orWhere';
        if (strpos($field, '.') === false) {
            return $builder->$where($field, $searchType, $query);
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

    protected function buildCase(string $column, array $words): array
    {
        $operator = 'LIKE';
        $bindings = [];
        $bindings['select'] = $bindings['where'] = array_map(static function (string $word): string {
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

    protected function isWildcard(string $word): bool
    {
        return Str::endsWith($word, '*') && Str::startsWith($word, '*');
    }

    protected function buildEqualsCase(string $column, array $words): string
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

    protected function isLeftMatching(string $word): bool
    {
        return Str::endsWith($word, '*');
    }

    protected function caseBinding(string $word): string
    {
        return str_replace('?', '_', str_replace('*', '', $word));
    }

    private function isFieldSearchable(string $field): bool
    {
        static $columns;
        if (! isset($columns)) {
            $columns = [];
        }
        $id = sprintf('%s-%s', $this->getTable(), $this->getConnectionName());

        return in_array($field, $columns[$id] ?? []);
    }

    public static function applySearchQuery(Builder $query, array $searchQuery): Builder
    {
        $instance = new self;
        $dates = $instance->getDates();

        $applyGroup = function (Builder $query, array $group) use ($dates): Builder {
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

        if (isset($searchQuery['search']) && ! is_null($searchQuery['search'])) {
            $query = $applyGroup($query, $searchQuery['search']);
        } elseif (isset($searchQuery['queries']) && ! is_null($searchQuery['queries'])) {
            foreach ($searchQuery['queries'] as $group) {
                $query = $query->orWhere(function ($q) use ($group, $applyGroup) {
                    $applyGroup($q, $group);
                });
            }
        }

        return $query;
    }

    public static function validateSearchQuery(Request $request): array
    {
        $instance = new self;

        $fieldSearchable = function (string $field, mixed $value, \Closure $fail) use ($instance): void {
            if (! $instance->isFieldSearchable($value)) {
                $fail(sprintf('%s is not a searchable field', $value));
            }
        };

        return $request->validate([
            'per_page' => 'numeric',
            'page'     => 'numeric',

            'search'         => 'required_without:queries|array',
            'search.*.field' => [
                'required',
                $fieldSearchable,
            ],
            'search.*.operation' => 'required|in:=,<,>,<=,>=,!=,like',
            'search.*.value'     => 'present',

            'queries'           => 'required_without:search|array',
            'queries.*'         => 'array',
            'queries.*.*.field' => [
                'required',
                $fieldSearchable,
            ],
            'queries.*.*.operation' => 'required|in:=,<,>,<=,>=,!=,like',
            'queries.*.*.value'     => 'present',
        ]);
    }
}
