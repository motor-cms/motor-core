<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Motor\Core\Database\Migrations\MigrationCreator;
use Motor\Core\Support\Composer;

class MotorMakeMigrationCommand extends MigrateMakeCommand
{

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'motor:make:migration {name : The name of the migration.}
        {--create= : The table to be created.}
        {--table= : The table to migrate.}
        {--path= : The location where the migration file should be created.}
        {--namespace= : The namespace for the migration}';


    /**
     * MotorMakeMigrationCommand constructor.
     *
     * @param MigrationCreator $creator
     * @param Composer         $composer
     */
    public function __construct(MigrationCreator $creator, Composer $composer)
    {
        $composer = app()['Motor\Core\Support\Composer'];
        parent::__construct($creator, $composer);
    }
}