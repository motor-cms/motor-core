<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Filesystem\Filesystem;

class MotorMakeTestCommand extends MotorAbstractCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:test {name} {type} {--path=} {--namespace=} {--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test stub';


    /**
     * @return string
     */
    protected function getTargetPath(): string
    {
        $basePath = (! is_null($this->option('path')) ? $this->option('path') . '/../tests' : resource_path() . '/../tests');

        return $basePath . '/integration/controller/' . $this->argument('type') . '/';
    }


    /**
     * @return string
     */
    protected function getTargetFile(): string
    {
        $values = $this->getTemplateVars();

        return $values['namespaceNoSlash'] . ucfirst($this->argument('type')) . $values['singularStudly'] . 'Test.php';
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/controller_' . $this->argument('type') . '_test.stub';
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Check target file
        if (file_exists($this->getTargetPath() . $this->getTargetFile())) {
            $this->error('Test target file exists');

            return;
        }

        $filesystem = new Filesystem();
        if ( ! $filesystem->isDirectory($this->getTargetPath())) {
            $filesystem->makeDirectory($this->getTargetPath(), 0755, true);
        }

        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceTemplateVars($stub);
        file_put_contents($this->getTargetPath() . $this->getTargetFile(), $stub);

        $this->info('Test file for ' . $this->argument('type') . ' generated');
    }
}
