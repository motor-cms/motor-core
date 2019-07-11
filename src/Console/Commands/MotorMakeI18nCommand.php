<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Filesystem\Filesystem;

/**
 * Class MotorMakeI18nCommand
 * @package Motor\Core\Console\Commands
 */
class MotorMakeI18nCommand extends MotorAbstractCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:i18n {name} {locale=en} {--path=} {--namespace=} {--prefix=} {--stub_path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new i18n file';


    /**
     * @return string
     */
    protected function getTargetPath(): string
    {
        $prefix   = $this->option('prefix') ? $this->option('prefix') : 'backend';
        $basePath = (! is_null($this->option('path')) ? $this->option('path') . '/../resources/lang' : resource_path('lang'));

        return $basePath . '/' . strtolower($this->argument('locale')) . '/' . $prefix . '/';
    }


    /**
     * @return string
     */
    protected function getTargetFile(): string
    {
        $values = $this->getTemplateVars();

        return $values['pluralKebab'] . '.php';
    }


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

        return __DIR__ . '/stubs/i18n.stub';
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
            $this->error('I18n target file exists');

            return;
        }

        $filesystem = new Filesystem();
        if ( ! $filesystem->isDirectory($this->getTargetPath())) {
            $filesystem->makeDirectory($this->getTargetPath(), 0755, true);
        }

        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceTemplateVars($stub);
        file_put_contents($this->getTargetPath() . $this->getTargetFile(), $stub);

        $this->info('I18n file generated');
    }
}
