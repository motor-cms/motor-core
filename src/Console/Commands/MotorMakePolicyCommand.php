<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\PolicyMakeCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MotorMakePolicyCommand extends PolicyMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:policy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new policy';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Policy';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->option('namespace') ? $this->option('namespace') : $this->laravel->getNamespace();
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);
        $path = $this->option('path') ? $this->option('path') : $this->laravel['path'];

        return $path.'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/policy.stub';
    }

    protected function replaceModel($stub, $model) {

        $data = parent::replaceModel($stub, $model);

        $permission = Str::snake(Str::plural(class_basename(trim($model, '\\'))));

        $data = str_replace('{{ permission }}', $permission, $data);

        return $data;
    }
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the policy applies to'],
            ['guard', 'g', InputOption::VALUE_OPTIONAL, 'The guard that the policy relies on'],
            ['namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Define the correct namespace'],
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'Define the correct path'],
        ];
    }
}
