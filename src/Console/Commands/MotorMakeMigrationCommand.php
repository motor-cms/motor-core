<?php

namespace Motor\Core\Console\Commands;

use Laracasts\Generators\Commands\MigrationMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MotorMakeMigrationCommand extends MigrationMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:migration';

    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileMigrationStub()
    {
        $stub = $this->files->get(__DIR__ . '/stubs/migration.stub');

        $this->replaceClassName($stub)->replaceSchema($stub)->replaceTableName($stub);

        return $stub;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['create', 'c', InputOption::VALUE_OPTIONAL, 'Create tables', null],
            ['schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null],
            ['model', null, InputOption::VALUE_OPTIONAL, 'Want a model for this table?', true],
        ];
    }
}