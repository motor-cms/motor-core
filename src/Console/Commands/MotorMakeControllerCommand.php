<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use Motor\Core\Helpers\GeneratorHelper;

class MotorMakeControllerCommand extends ControllerMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:controller';

    protected $signature = 'motor:make:controller {name} {--type=default} {--path=} {--namespace=} {--model=} {--parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor controller class';

    protected function getPath($name)
    {
        return GeneratorHelper::getPath($name, $this->option('path'), $this->laravel);
    }

    protected function getNamespace($name)
    {
        return GeneratorHelper::getNamespace($name, $this->option('namespace'), $this->laravel);
    }

    protected function getRootNamespace()
    {
        return GeneratorHelper::getRootNamespace($this->option('namespace'), $this->laravel);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        switch ($this->option('type')) {
            case 'default':
                return __DIR__ . '/stubs/controller_backend.stub';
                break;
            case 'api':
                return __DIR__ . '/stubs/controller_api.stub';
                break;
        }
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
        $classBase = Str::singular(str_replace('Controller', '', $class));
        $grid = $classBase.'Grid';
        $request = $classBase.'Request';
        $form = $classBase.'Form';
        $service = $classBase.'Service';
        $transformer = $classBase.'Transformer';

        // Guess package name to prefix the views and i18n location
        $packageName = '';
        if (!is_null($this->option('namespace'))) {
            $packageName = str_replace('/', '-', strtolower($this->option('namespace'))).'::';
        }

        $stub = str_replace(
            'DummyClass', $class, $stub
        );

        $stub = str_replace(
            'DummyNamespace', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            'DummyRootNamespace', $this->getRootNamespace(), $stub
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
            'DummyService', $service, $stub
        );

        $stub = str_replace(
            'DummyTransformer', $transformer, $stub
        );
        $stub = str_replace(
            'DummyView', Str::plural(Str::snake($classBase)), $stub
        );

        $stub = str_replace(
            'DummyPluralTitle', Str::ucfirst(str_replace('_', ' ', $classBase)), $stub
        );

        $stub = str_replace(
            'DummySingularTitle', Str::ucfirst(str_replace('_', ' ', $classBase)), $stub
        );

        $stub = str_replace(
            'DummyPackageName', $packageName, $stub
        );

        return $this;
    }
}