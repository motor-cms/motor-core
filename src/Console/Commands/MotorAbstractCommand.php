<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Motor\Core\Helpers\GeneratorHelper;

abstract class MotorAbstractCommand extends Command
{

    protected function replaceTemplateVars($stub)
    {
        foreach ($this->getTemplateVars() as $key => $value) {
            $stub = str_replace('{{' . $key . '}}', $value, $stub);
        }

        return $stub;
    }


    protected function getTemplateVars()
    {
        $singularSnake = Str::snake(Str::singular($this->argument('name')));
        $pluralSnake   = Str::snake(Str::plural($this->argument('name')));

        $namespace = ( ! is_null($this->option('namespace')) ? $this->option('namespace') . '\\' : $this->laravel->getNamespace() );
        $namespace = str_replace('/', '\\', $namespace);

        // Guess package name to prefix the views and i18n location
        $packageName = '';
        if (!is_null($this->option('namespace'))) {
            $packageName = str_replace('/', '-', strtolower($this->option('namespace'))).'::';
        }

        return [
            'singularSnake'     => $singularSnake,
            'pluralSnake'       => $pluralSnake,
            'singularTitle'     => Str::ucfirst(str_replace('_', ' ', $singularSnake)),
            'pluralTitle'       => Str::ucfirst(str_replace('_', ' ', $pluralSnake)),
            'singularLowercase' => Str::lower(str_replace('_', ' ', $singularSnake)),
            'pluralLowercase'   => Str::lower(str_replace('_', ' ', $pluralSnake)),
            'singularStudly'    => Str::studly($singularSnake),
            'pluralStudly'      => Str::studly($pluralSnake),
            'namespace'         => $namespace,
            'packageName'       => $packageName,
        ];
    }


    abstract protected function getTargetPath();


    abstract protected function getTargetFile();


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/i18n.stub';
    }
}
