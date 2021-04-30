<?php

namespace Motor\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Motor\Core\Console\Commands\MotorMakeControllerCommand;
use Motor\Core\Console\Commands\MotorMakeFactoryCommand;
use Motor\Core\Console\Commands\MotorMakeFormCommand;
use Motor\Core\Console\Commands\MotorMakeGridCommand;
use Motor\Core\Console\Commands\MotorMakeI18nCommand;
use Motor\Core\Console\Commands\MotorMakeInfoCommand;
use Motor\Core\Console\Commands\MotorMakeMigrationCommand;
use Motor\Core\Console\Commands\MotorMakeModelCommand;
use Motor\Core\Console\Commands\MotorMakeModuleCommand;
use Motor\Core\Console\Commands\MotorMakePolicyCommand;
use Motor\Core\Console\Commands\MotorMakeRequestCommand;
use Motor\Core\Console\Commands\MotorMakeResourceCommand;
use Motor\Core\Console\Commands\MotorMakeSeederCommand;
use Motor\Core\Console\Commands\MotorMakeServiceCommand;
use Motor\Core\Console\Commands\MotorMakeTestCommand;
use Motor\Core\Console\Commands\MotorMakeViewCommand;
use Motor\Core\Console\Commands\MotorSetpackagedevCommand;

/**
 * Class MotorServiceProvider
 * @package Motor\Core\Providers
 */
class MotorServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->documentation();
        merge_local_config_with_db_configuration_variables('motor-core');
    }


    /**
     * Merge documentation items from configuration file
     */
    public function documentation()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'motor-core');

        $config = $this->app['config']->get('motor-docs', []);
        $this->app['config']->set('motor-docs',
            array_replace_recursive(require __DIR__.'/../../config/motor-docs.php', $config));
    }


    /**
     * Register artisan commands
     */
    public function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MotorMakeModuleCommand::class,
                MotorMakeModelCommand::class,
                MotorMakeMigrationCommand::class,
                MotorMakeControllerCommand::class,
                MotorMakeRequestCommand::class,
                MotorMakeSeederCommand::class,
                MotorMakeGridCommand::class,
                MotorMakeI18nCommand::class,
                MotorMakeViewCommand::class,
                MotorMakeInfoCommand::class,
                MotorMakeFormCommand::class,
                MotorMakePolicyCommand::class,
                MotorMakeResourceCommand::class,
                MotorMakeFactoryCommand::class,
                MotorMakeServiceCommand::class,
                MotorMakeTestCommand::class,
                MotorSetpackagedevCommand::class,
            ]);
        }
    }
}
