<?php

namespace Motor\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Motor\Core\Console\Commands\MotorAbstractCommand;
use Motor\Core\Console\Commands\MotorMakeControllerCommand;
use Motor\Core\Console\Commands\MotorMakeGridCommand;
use Motor\Core\Console\Commands\MotorMakeI18nCommand;
use Motor\Core\Console\Commands\MotorMakeInfoCommand;
use Motor\Core\Console\Commands\MotorMakeMigrationCommand;
use Motor\Core\Console\Commands\MotorMakeModelCommand;
use Motor\Core\Console\Commands\MotorMakeModuleCommand;
use Motor\Core\Console\Commands\MotorMakeRequestCommand;
use Motor\Core\Console\Commands\MotorMakeViewCommand;

class MotorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MotorMakeModuleCommand::class,
                MotorMakeModelCommand::class,
                MotorMakeMigrationCommand::class,
                MotorMakeControllerCommand::class,
                MotorMakeRequestCommand::class,
                MotorMakeGridCommand::class,
                MotorMakeI18nCommand::class,
                MotorMakeViewCommand::class,
                MotorMakeInfoCommand::class,
            ]);
        }
    }
}
