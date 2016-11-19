<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;

class MotorMakeGridCommand extends ControllerMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:grid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor grid class';

    protected $type = 'Grid';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Grids';
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/grid.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);
        $classBase = Str::singular(str_replace('Grid', '', $class));
        $controller = Str::plural($classBase).'Controller';

        $stub = str_replace(
            'DummyRootNamespace', $this->laravel->getNamespace(), $stub
        );

        $stub = str_replace(
            'DummyGrid', $class, $stub
        );

        $stub = str_replace(
            'DummyController', $controller, $stub
        );
        return $this;
    }
}