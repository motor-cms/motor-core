<?php

namespace Motor\Core\Helpers;

use Illuminate\Support\Str;

class GeneratorHelper
{

    /**
     * Get the destination class path.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    public static function getPath($name, $path, $laravel): string
    {
        $userPath = (! is_null($path) ? realpath($path) : $laravel['path']);
        $name     = Str::replaceFirst($laravel->getNamespace(), '', $name);

        return $userPath . '/' . str_replace('\\', '/', $name) . '.php';
    }


    /**
     * Get the full namespace name for a given class.
     *
     * @param string $name
     * @return string
     */
    public static function getNamespace($name, $namespace, $laravel): string
    {
        $namespace = (! is_null($namespace) ? $namespace . '\\' : $laravel->getNamespace());
        $namespace = str_replace('/', '\\', $namespace);

        if ($namespace !== $laravel->getNamespace()) {
            $name = str_replace($laravel->getNamespace(), $namespace, $name);
        }

        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }


    /**
     * @param $namespace
     * @param $laravel
     * @return string
     */
    public static function getRootNamespace($namespace, $laravel): string
    {
        return (! is_null($namespace) ? str_replace('/', '\\', $namespace) . '\\' : $laravel->getNamespace());
    }

}
