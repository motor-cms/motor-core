<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;

class MotorMakeServiceCommand extends MotorMakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:service';

    protected $signature = 'motor:make:service {name} {--path=} {--namespace=} {--model=} {--parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor service class';

    protected $type = 'Service';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/service.stub';
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

        $stub = str_replace(
            'DummyRootNamespace', $this->getRootNamespace(), $stub
        );

        $stub = str_replace(
            'DummyService', $class, $stub
        );

        $stub = str_replace(
            'DummyModel', str_replace('Service', '', $class), $stub
        );

        return $this;
    }
}