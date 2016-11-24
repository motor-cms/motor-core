<?php

namespace Motor\Core\Helpers;

class GeneratorHelper {

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    public static function getPath($name, $path, $laravel)
    {
        $userPath = (!is_null($path) ? realpath($path) : $laravel['path']);
        $name = str_replace_first($laravel->getNamespace(), '', $name);

        $path = $userPath.'/'.str_replace('\\', '/', $name).'.php';

        return $path;
    }

    /**
     * Get the full namespace name for a given class.
     *
     * @param  string  $name
     * @return string
     */
    public static function getNamespace($name, $namespace, $laravel)
    {
        $namespace = (!is_null($namespace) ? $namespace.'\\' : $laravel->getNamespace());
        $namespace = str_replace('/', '\\', $namespace);

        if ($namespace != $laravel->getNamespace())
        {
            $name = str_replace($laravel->getNamespace(), $namespace, $name);
        }

        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }


    /**
     * @param $namespace
     * @param $laravel
     *
     * @return mixed
     */
    public static function getRootNamespace($namespace, $laravel)
    {
        return (!is_null($namespace) ? str_replace('/', '\\', $namespace).'\\' : $laravel->getNamespace());
    }

}
