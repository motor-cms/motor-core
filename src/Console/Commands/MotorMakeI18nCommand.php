<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Filesystem\Filesystem;

class MotorMakeI18nCommand extends MotorAbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:i18n {name} {locale=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new i18n file';
    
    protected function getTargetPath()
    {
        return resource_path('lang').'/'.strtolower($this->argument('locale')).'/backend/';
    }

    protected function getTargetFile()
    {
        $values = $this->getTemplateVars();
        return $values['pluralSnake'].'.php';
    }
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/i18n.stub';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Check target file
        if (file_exists($this->getTargetPath().$this->getTargetFile())) {
            $this->error('I18n target file exists');
            return;
        }

        $filesystem = new Filesystem();
        if (!$filesystem->isDirectory($this->getTargetPath())) {
            $filesystem->makeDirectory($this->getTargetPath(), 0755, true);
        }

        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceTemplateVars($stub);
        file_put_contents($this->getTargetPath().$this->getTargetFile(), $stub);

        $this->info('I18n file generated');
    }
}
