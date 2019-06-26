<?php

namespace Motor\Core\Test;

require __DIR__ . '/../vendor/autoload.php';

use Motor\Core\Providers\MotorServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return Motor\Core\Providers\MotorServiceProvider
     */
    protected function getPackageProviders($app)
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
    }}