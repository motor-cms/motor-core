<?php

namespace Motor\Core\Database\Migrations;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;

class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /**
     * Get the migration stub file.
     *
     * @param  string  $table
     * @param  bool    $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            return $this->files->get(__DIR__ . '/stubs/migration_blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        else {
            $stub = $create ? 'migration_create.stub' : 'migration_update.stub';
            return $this->files->get(__DIR__ ."/stubs/{$stub}");
        }
    }
}
