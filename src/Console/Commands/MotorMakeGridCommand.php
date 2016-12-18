<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;

class MotorMakeGridCommand extends MotorMakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:grid';

    protected $signature = 'motor:make:grid {name} {--path=} {--namespace=} {--model=}';

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
        $class = last(explode('/', $this->getNameInput()));
        $classBase = Str::singular(str_replace('Grid', '', $class));

        $stub = str_replace(
            'DummyRootNamespace', $this->getRootNamespace(), $stub
        );

        $stub = str_replace(
            'DummyGrid', $class, $stub
        );

        $stub = str_replace(
            'DummyView', Str::plural(Str::snake($classBase)), $stub
        );

        return $this;
    }
}