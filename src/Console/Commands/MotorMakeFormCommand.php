<?php

namespace Motor\Core\Console\Commands;

use Kris\LaravelFormBuilder\Console\FormMakeCommand;
use Motor\Core\Helpers\GeneratorHelper;

/**
 * Class MotorMakeFormCommand
 */
class MotorMakeFormCommand extends FormMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor form';

    /**
     * Get the desired class name from the input.
     */
    protected function getNameInput(): ?string
    {
        return $this->argument('name');
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     */
    protected function replaceNamespace(&$stub, $name): object
    {
        $namespace = GeneratorHelper::getNamespace($name, $this->option('namespace'), $this->laravel);

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        return $this;
    }
}
