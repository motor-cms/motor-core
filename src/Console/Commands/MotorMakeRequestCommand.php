<?php

namespace Motor\Core\Console\Commands;

/**
 * Class MotorMakeRequestCommand
 */
class MotorMakeRequestCommand extends MotorMakeControllerCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:request';

    /**
     * @var string
     */
    protected $signature = 'motor:make:request {name} {--path=} {--namespace=} {--model=} {--parent=} {--creatable=}';

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
    protected function getStub(): string
    {
        return __DIR__.'/stubs/request.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Requests';
    }
}
