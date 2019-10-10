<?php

namespace Motor\Core\Test;

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Motor\Core\Providers\MotorServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Class TestCase
 * @package Motor\Core\Test
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders(Application $app)
    {
        return [MotorServiceProvider::class];
    }
    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
        ];
    }
}
