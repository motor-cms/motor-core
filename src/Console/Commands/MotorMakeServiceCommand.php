<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Support\Str;

/**
 * Class MotorMakeServiceCommand
 */
class MotorMakeServiceCommand extends MotorMakeControllerCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:service';

    /**
     * @var string
     */
    protected $signature = 'motor:make:service {name} {--path=} {--namespace=} {--model=} {--parent=} {--stub_path=} {--creatable=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor service class';

    /**
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        if ($this->option('stub_path')) {
            return $this->option('stub_path');
        }

        return __DIR__.'/stubs/service.stub';
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

        $stub = str_replace('DummyRootNamespace', $this->getRootNamespace(), $stub);

        $stub = str_replace('DummyService', $class, $stub);

        $stub = str_replace('DummyModelPlural', Str::plural(str_replace('Service', '', $class)), $stub);

        $stub = str_replace('DummyModel', Str::singular(str_replace('Service', '', $class)), $stub);

        $stub = str_replace('ComponentNameSingularLowercase', strtolower(Str::singular(str_replace('Component', '', str_replace('Service', '', $class)))), $stub);

        $stub = str_replace('ComponentNamePluralLowercase', strtolower(Str::plural(str_replace('Component', '', str_replace('Service', '', $class)))), $stub);

        $stub = str_replace('ComponentNameSingularKebab', Str::kebab(Str::singular(str_replace('Component', '', str_replace('Service', '', $class)))), $stub);

        $stub = str_replace('ComponentNamePluralKebab', Str::kebab(Str::plural(str_replace('Component', '', str_replace('Service', '', $class)))), $stub);

        return $this;
    }
}
