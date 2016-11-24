<?php

namespace Motor\Core\Console\Commands;

use Laracasts\Generators\Commands\MigrationMakeCommand;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Filesystem\Filesystem;

class MotorMakeMigrationCommand extends MigrationMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:migration';

    private $composer;


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
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer   $composer
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);

        $this->composer = app()['Motor\Core\Support\Composer'];
    }


    /**
     * Get the path to where we should store the migration.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $basePath = (!is_null($this->option('path')) ? realpath($this->option('path')) : resource_path());

        return $basePath . '/../database/migrations/' . date('Y_m_d_His') . '_' . $name . '.php';
    }


    /**
     * Generate the desired migration.
     *
     * IMPORTANT INFO: We have to copy this because $this->composer is private and for local package development, we
     * need to do the dumpautoload with a different composer.json file
     */
    protected function makeMigration()
    {
        $name = $this->argument('name');

        if ($this->files->exists($path = $this->getPath($name))) {
            return $this->error($this->type . ' already exists!');
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->compileMigrationStub());

        $this->info('Migration created successfully.');

        $this->composer->dumpAutoloads();
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [ 'create', 'c', InputOption::VALUE_OPTIONAL, 'Create tables', null ],
            [ 'schema', 's', InputOption::VALUE_OPTIONAL, 'Optional schema to be attached to the migration', null ],
            [ 'model', null, InputOption::VALUE_OPTIONAL, 'Want a model for this table?', false ],
            [ 'path', null, InputOption::VALUE_OPTIONAL, 'Want a model for this table?', null ],
            [ 'namespace', null, InputOption::VALUE_OPTIONAL, 'Want a namespace?', null ],
        ];
    }
}