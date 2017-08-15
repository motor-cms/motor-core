<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MotorMakeRequestCommand extends MotorMakeControllerCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:request';

    protected $signature = 'motor:make:request {name} {--path=} {--namespace=} {--model=} {--parent=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor request';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/request.stub';
    }


    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Requests';
    }
}