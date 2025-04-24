<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Filesystem\Filesystem;

/**
 * Class MotorMakeViewCommand
 */
class MotorMakeViewCommand extends MotorAbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:view {name} {type} {--path=} {--namespace=} {--stub_path=} {--directory=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view file';

    protected function getTargetPath(): string
    {
        $values = $this->getTemplateVars();
        $basePath = (! is_null($this->option('path')) ? $this->option('path').'/../resources/views' : resource_path('views'));

        $directory = $this->option('directory') ? $this->option('directory') : 'backend';

        return $basePath.'/'.$directory.'/'.$values['pluralSnake'].'/';
    }

    protected function getTargetFile(): string
    {
        return $this->argument('type').'.blade.php';
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        if ($this->option('stub_path')) {
            return $this->option('stub_path');
        }

        return __DIR__.'/stubs/views/'.$this->argument('type').'.blade.stub';
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Check target file
        if (file_exists($this->getTargetPath().$this->getTargetFile())) {
            $this->error('View target '.$this->argument('type').' file exists');

            return;
        }

        $filesystem = new Filesystem;
        if (! $filesystem->isDirectory($this->getTargetPath())) {
            $filesystem->makeDirectory($this->getTargetPath(), 0755, true);
        }

        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceTemplateVars($stub);
        file_put_contents($this->getTargetPath().$this->getTargetFile(), $stub);

        $this->info('View file '.$this->argument('type').' generated');
    }
}
