<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Motor\Core\Database\Migrations\MigrationCreator;
use Motor\Core\Support\Composer;

/**
 * Class MotorMakeMigrationCommand
 */
class MotorMakeMigrationCommand extends MigrateMakeCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'motor:make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';

    /**
     * MotorMakeMigrationCommand constructor.
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        $composer = app()['Motor\Core\Support\Composer'];
        parent::__construct($creator, $composer);
    }
}
