<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;

class MotorMakeControllerCommand extends ControllerMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor controller class';


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.stub';
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
        $classBase = Str::singular(str_replace('Controller', '', $class));
        $grid = $classBase.'Grid';
        $request = $classBase.'Request';
        $form = $classBase.'Form';

        $stub = str_replace(
            'DummyNamespace', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            'DummyRootNamespace', $this->laravel->getNamespace(), $stub
        );

        $stub = str_replace(
            'DummyRequest', $request, $stub
        );

        $stub = str_replace(
            'DummyForm', $form, $stub
        );

        $stub = str_replace(
            'DummyModel', $classBase, $stub
        );

        $stub = str_replace(
            'DummyGrid', $grid, $stub
        );

        $stub = str_replace(
            'DummyView', Str::plural(Str::snake($classBase)), $stub
        );
        return $this;
    }
}