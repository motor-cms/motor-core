<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Support\Str;

/**
 * Class MotorMakeGridCommand
 */
class MotorMakeGridCommand extends MotorMakeControllerCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:grid';

    /**
     * @var string
     */
    protected $signature = 'motor:make:grid {name} {--path=} {--namespace=} {--model=} {--parent=} {--creatable=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor grid class';

    /**
     * @var string
     */
    protected $type = 'Grid';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Grids';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/grid.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return object
     */
    protected function replaceNamespace(&$stub, $name): object
    {
        $class = last(explode('/', $this->getNameInput()));
        $classBase = Str::singular(str_replace('Grid', '', $class));

        $stub = str_replace('DummyRootNamespace', $this->getRootNamespace(), $stub);

        $stub = str_replace('DummyGrid', $class, $stub);

        $stub = str_replace('DummyView', Str::plural(Str::snake($classBase)), $stub);

        return $this;
    }
}
