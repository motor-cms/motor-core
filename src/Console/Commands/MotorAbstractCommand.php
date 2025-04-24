<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class MotorAbstractCommand
 */
abstract class MotorAbstractCommand extends Command
{
    /**
     * Replace inline template variables for the stub files
     */
    protected function replaceTemplateVars(string $stub): string
    {
        foreach ($this->getTemplateVars() as $key => $value) {
            $stub = str_replace('{{'.$key.'}}', $value, $stub);
        }

        return $stub;
    }

    /**
     * Do necessary replacement of the stub variables
     */
    protected function getTemplateVars(): array
    {
        $singularSnake = Str::snake(Str::singular($this->argument('name')));
        $pluralSnake = Str::snake(Str::plural($this->argument('name')));

        $namespace = (! is_null($this->option('namespace')) ? $this->option('namespace').'\\' : $this->laravel->getNamespace());
        $namespace = str_replace('/', '\\', $namespace);

        // Guess package name to prefix the views and i18n location
        $packageName = '';
        if (! is_null($this->option('namespace'))) {
            $packageName = str_replace('\\', '/', strtolower($this->option('namespace')));
            if (strrpos($packageName, '/') === strlen($packageName)) {
                $packageName = substr($packageName, 0, -1);
            }
            $packageName = str_replace('/', '-', $packageName).'::';
        }

        return [
            'singularKebabWithoutPrefix' => Str::kebab(Str::singular(str_replace('Component', '', $this->argument('name')))),
            'pluralKebabWithoutPrefix' => Str::kebab(Str::plural(str_replace('Component', '', $this->argument('name')))),
            'singularSnakeWithoutPrefix' => Str::snake(Str::singular(str_replace('Component', '', $this->argument('name')))),
            'pluralSnakeWithoutPrefix' => Str::snake(Str::plural(str_replace('Component', '', $this->argument('name')))),
            'singularTitleWithoutPrefix' => Str::ucfirst(str_replace('_', ' ', str_replace('Component', '', Str::singular($this->argument('name'))))),

            'singularSnake' => $singularSnake,
            'pluralSnake' => $pluralSnake,
            'singularKebab' => Str::kebab(Str::singular($this->argument('name'))),
            'pluralKebab' => Str::kebab(Str::plural($this->argument('name'))),
            'singularTitle' => Str::ucfirst(str_replace('_', ' ', $singularSnake)),
            'pluralTitle' => Str::ucfirst(str_replace('_', ' ', $pluralSnake)),
            'singularLowercase' => Str::lower(str_replace('_', ' ', $singularSnake)),
            'pluralLowercase' => Str::lower(str_replace('_', ' ', $pluralSnake)),
            'singularStudly' => Str::studly($singularSnake),
            'pluralStudly' => Str::studly($pluralSnake),
            'namespace' => $namespace,
            'namespaceNoSlash' => str_replace('\\', '', $namespace),
            'packageName' => $packageName,
            'randomInteger' => rand(101, 999),
        ];
    }

    /**
     * Get path for the newly generated file
     */
    abstract protected function getTargetPath(): string;

    /**
     * Get the file name for the newly generated file
     */
    abstract protected function getTargetFile(): string;

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/i18n.stub';
    }
}
