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

    /**
     * @var string
     */
    protected $signature = 'motor:make:controller {name} {--type=default} {--path=} {--namespace=} {--model=} {--parent=} {--stub_path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor controller class';


    /**
     * @param string $name
     * @return string
     */
    protected function getPath($name): string
    {
        return GeneratorHelper::getPath($name, $this->option('path'), $this->laravel);
    }


    /**
     * @param string $name
     * @return string
     */
    protected function getNamespace($name): string
    {
        return GeneratorHelper::getNamespace($name, $this->option('namespace'), $this->laravel);
    }


    /**
     * @return string
     */
    protected function getRootNamespace(): string
    {
        return GeneratorHelper::getRootNamespace($this->option('namespace'), $this->laravel);
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        if ($this->option('stub_path')) {
            return $this->option('stub_path');
        }

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
     * @param string $stub
     * @param string $name
     * @return object
     */
    protected function replaceNamespace(&$stub, $name): object
    {
        $class              = last(explode('/', $this->getNameInput()));
        $classBase          = Str::singular(str_replace('Controller', '', $class));
        $componentClassBase = Str::singular(str_replace('Component', '', str_replace('Controller', '', $class)));
        $grid               = $classBase . 'Grid';
        $request            = $classBase . 'Request';
        $form               = $classBase . 'Form';
        $service            = $classBase . 'Service';
        $transformer        = $classBase . 'Transformer';

        // Guess package name to prefix the views and i18n location
        $packageName = '';
        if ( ! is_null($this->option('namespace'))) {
            $packageName = str_replace('/', '-', strtolower($this->option('namespace'))) . '::';
        }

        $stub = str_replace('DummyClass', $class, $stub);

        $stub = str_replace('DummyNamespace', $this->getNamespace($name), $stub);

        $stub = str_replace('DummyRootNamespace', $this->getRootNamespace(), $stub);

        $stub = str_replace('DummyRequest', $request, $stub);

        $stub = str_replace('DummyForm', $form, $stub);

        $stub = str_replace('DummyModel', $classBase, $stub);

        $stub = str_replace('DummyGrid', $grid, $stub);

        $stub = str_replace('DummyService', $service, $stub);

        $stub = str_replace('DummyTransformer', $transformer, $stub);
        $stub = str_replace('DummyView', Str::plural(Str::snake($classBase)), $stub);

        $stub = str_replace('DummyComponentViewKebab', Str::plural(Str::kebab($componentClassBase)), $stub);

        $stub = str_replace('DummyComponentView', Str::plural(Str::snake($componentClassBase)), $stub);

        $stub = str_replace('DummyPluralTitle', Str::ucfirst(str_replace('_', ' ', $classBase)), $stub);

        $stub = str_replace('DummySingularTitle', Str::ucfirst(str_replace('_', ' ', $classBase)), $stub);

        $stub = str_replace('DummyPackageName', $packageName, $stub);

        return $this;
    }
}