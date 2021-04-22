<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MotorMakeResourceCommand extends ResourceMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motor:make:resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

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
        return $this->collection()
            ? $this->resolveStubPath('/stubs/resource-collection.stub')
            : __DIR__ . '/stubs/resource.stub';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['collection', 'c', InputOption::VALUE_NONE, 'Create a resource collection'],
            ['namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Define the correct namespace'],
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'Define the correct path'],
        ];
    }
}
