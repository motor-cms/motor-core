<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;

class MotorMakeTransformerCommand extends MotorMakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:transformer';

    protected $signature = 'motor:make:transformer {name} {--path=} {--namespace=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor transformer class';

    protected $type = 'Transformer';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Transformers';
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/transformer.stub';
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

        $class = Str::singular($class);

        $stub = str_replace(
            'DummyRootNamespace', $this->getRootNamespace(), $stub
        );

        $stub = str_replace(
            'DummyTransformer', $class, $stub
        );

        $stub = str_replace(
            'DummyModel', str_replace('Transformer', '', $class), $stub
        );

        return $this;
    }
}