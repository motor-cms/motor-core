<?php

namespace Motor\Core\Helpers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

/**
 * Class GeneratorHelper
 *
 * @package Motor\Core\Helpers
 */
class GeneratorHelper
{
    /**
     * Get the destination class path.
     *
     * @param $name
     * @param $path
     * @param \Illuminate\Contracts\Foundation\Application $laravel
     * @return string
     */
    public static function getPath($name, $path, Application $laravel): string
    {
        $userPath = (! is_null($path) ? realpath($path) : $laravel['path']);
        $name = Str::replaceFirst($laravel->getNamespace(), '', $name);

        return $userPath.'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param $name
     * @param $namespace
     * @param \Illuminate\Contracts\Foundation\Application $laravel
     * @return string
     */
    public static function getNamespace($name, $namespace, Application $laravel): string
    {
        $namespace = (! is_null($namespace) ? $namespace.'\\' : $laravel->getNamespace());
        $namespace = str_replace('/', '\\', $namespace);

        if ($namespace !== $laravel->getNamespace()) {
            $name = str_replace($laravel->getNamespace(), $namespace, $name);
        }

        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Get root namespace of the application
     *
     * @param $namespace
     * @param \Illuminate\Contracts\Foundation\Application $laravel
     * @return string
     */
    public static function getRootNamespace($namespace, Application $laravel): string
    {
        return (! is_null($namespace) ? str_replace('/', '\\', $namespace).'\\' : $laravel->getNamespace());
    }
}
