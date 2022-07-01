<?php

namespace Motor\Core\Database\Migrations;

use Illuminate\Filesystem\Filesystem;

/**
 * Class MigrationCreator
 */
class MigrationCreator extends \Illuminate\Database\Migrations\MigrationCreator
{
    /**
     * Create a new migration creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $customStubPath
     * @return void
     */
    public function __construct(Filesystem $files, $customStubPath = null)
    {
        parent::__construct($files, $customStubPath);
    }

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getStub($table, $create): string
    {
        if (is_null($table)) {
            return $this->files->get(__DIR__.'/stubs/migration_blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        else {
            $stub = $create ? 'migration_create.stub' : 'migration_update.stub';

            return $this->files->get(__DIR__."/stubs/{$stub}");
        }
    }
}
