<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use Motor\Core\Helpers\GeneratorHelper;

/**
 * Class MotorMakeControllerCommand
 */
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
    protected $signature = 'motor:make:controller {name} {--type=default} {--path=} {--namespace=} {--model=} {--parent=} {--stub_path=} {--creatable=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor controller class';

    /**
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        return GeneratorHelper::getPath($name, $this->option('path'), $this->laravel);
    }

    /**
     * @param  string  $name
     */
    protected function getNamespace($name): string
    {
        $namespace = $this->option('namespace');
        if (strrpos($namespace, '\\') === strlen($namespace)) {
            $namespace = substr($namespace, 0, -1);
        }

        return GeneratorHelper::getNamespace($name, $namespace, $this->laravel);
    }

    protected function getRootNamespace(): string
    {
        $namespace = GeneratorHelper::getRootNamespace($this->option('namespace'), $this->laravel);
        if (strrpos($namespace, '\\') === strlen($namespace)) {
            $namespace = substr($namespace, 0, -1);
        }

        return $namespace;
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): ?string
    {
        if ($this->option('stub_path')) {
            return $this->option('stub_path');
        }

        switch ($this->option('type')) {
            case 'default':
                return __DIR__.'/stubs/controller_backend.stub';
                break;
            case 'api':
                return __DIR__.'/stubs/controller_api.stub';
                break;
        }

        return '';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     */
    protected function replaceNamespace(&$stub, $name): object
    {
        $class = last(explode('/', $this->getNameInput()));
        $classBase = Str::singular(str_replace('Controller', '', $class));
        $postRequest = $classBase.'PostRequest';
        $patchRequest = $classBase.'PatchRequest';
        $service = $classBase.'Service';
        $resource = $classBase.'Resource';
        $collection = $classBase.'Collection';

        // Guess package name to prefix the views and i18n location
        $packageName = '';
        if (! is_null($this->option('namespace'))) {
            $packageName = str_replace('\\', '/', strtolower($this->option('namespace')));
            if (strrpos($packageName, '/') === strlen($packageName)) {
                $packageName = substr($packageName, 0, -1);
            }
            $packageName = str_replace('/', '-', $packageName).'::';
        }

        $stub = str_replace('DummyClass', $class, $stub);

        $stub = str_replace('DummyNamespace', $this->getNamespace($name), $stub);

        $stub = str_replace('DummyRootNamespace', $this->getRootNamespace(), $stub);

        $stub = str_replace('DummyPostRequest', $postRequest, $stub);

        $stub = str_replace('DummyPatchRequest', $patchRequest, $stub);

        $stub = str_replace('DummyModel', $classBase, $stub);

        $stub = str_replace('DummyService', $service, $stub);

        $stub = str_replace('DummyCollection', $collection, $stub);

        $stub = str_replace('DummyResource', $resource, $stub);

        $stub = str_replace('DummyPluralTitle', Str::ucfirst(Str::plural(str_replace('_', ' ', $classBase))), $stub);

        $stub = str_replace('DummyPluralLowercase', Str::snake(Str::plural(str_replace('_', ' ', $classBase))), $stub);

        $stub = str_replace('DummySingularTitle', Str::ucfirst(str_replace('_', ' ', $classBase)), $stub);

        $stub = str_replace('DummySingularLowercase', Str::snake(str_replace('_', ' ', $classBase)), $stub);

        $stub = str_replace('DummySingularCamelCase', Str::camel($classBase), $stub);

        $stub = str_replace('RootNamespaceSnakeCase', Str::snake(str_replace('\\', '_', $this->getRootNamespace())), $stub);

        $stub = str_replace('DummyPackageName', $packageName, $stub);

        return $this;
    }
}
