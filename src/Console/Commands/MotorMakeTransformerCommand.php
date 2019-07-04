<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Support\Str;

class MotorMakeTransformerCommand extends MotorMakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:transformer';

    /**
     * @var string
     */
    protected $signature = 'motor:make:transformer {name} {--path=} {--namespace=} {--model=} {--parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor transformer class';

    /**
     * @var string
     */
    protected $type = 'Transformer';


    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Transformers';
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/transformer.stub';
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
        $class = last(explode('/', $this->getNameInput()));

        $class = Str::singular($class);

        $stub = str_replace('DummyRootNamespace', $this->getRootNamespace(), $stub);

        $stub = str_replace('DummyTransformer', $class, $stub);

        $stub = str_replace('DummyModel', str_replace('Transformer', '', $class), $stub);

        return $this;
    }
}