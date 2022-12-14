<?php

namespace Motor\Core\Console\Commands;

/**
 * Class MotorMakeModelCommand
 */
class MotorMakeModelCommand extends MotorMakeControllerCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:model';

    /**
     * @var string
     */
    protected $signature = 'motor:make:model {name} {--path=} {--namespace=} {--model=} {--parent=} {--stub_path=} {--creatable=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new motor model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

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

        return __DIR__.'/stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Models';
    }
}
