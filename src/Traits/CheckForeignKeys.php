<?php

namespace Motor\Core\Traits;

use Illuminate\Support\Facades\Schema;

trait CheckForeignKeys
{
    protected array $foreignKeyCache = [];

    protected function getForeignKeyByColumns(string $table, array $columns): ?array
    {
        $delimiter = '|';
        $glue = fn ($columns) => collect($columns)->join($delimiter);
        if (! isset($this->foreignKeyCache[$table])) {
            $this->foreignKeyCache[$table] = collect(Schema::getForeignKeys($table))->keyBy(
                fn (array $foreignKeyItemRecord) => $glue($foreignKeyItemRecord['columns'])
            )->toArray();
        }

        return $this->foreignKeyCache[$table][$glue($columns)] ?? null;
    }
}
