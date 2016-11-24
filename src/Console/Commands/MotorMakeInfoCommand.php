<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MotorMakeInfoCommand extends MotorAbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:info {name} {--path=} {--namespace=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display config information according to the given name';

    protected function getTargetPath() {}

    protected function getTargetFile() {}

    protected function getNavigationStub()
    {
        return __DIR__ . '/stubs/info/navigation.stub';
    }

    protected function getRouteStub()
    {
        return __DIR__ . '/stubs/info/route.stub';
    }

    protected function getRouteModelBindingStub()
    {
        return __DIR__ . '/stubs/info/routemodelbinding.stub';
    }

    protected function getModelFactoryStub()
    {
        return __DIR__ . '/stubs/info/modelfactory.stub';
    }

    protected function getTestHelperStub()
    {
        return __DIR__ . '/stubs/info/testhelper.stub';
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $navigation = file_get_contents($this->getNavigationStub());
        $navigation = $this->replaceTemplateVars($navigation);

        $route = file_get_contents($this->getRouteStub());
        $route = $this->replaceTemplateVars($route);

        $routeModelBinding = file_get_contents($this->getRouteModelBindingStub());
        $routeModelBinding = $this->replaceTemplateVars($routeModelBinding);

        $modelFactory = file_get_contents($this->getModelFactoryStub());
        $modelFactory = $this->replaceTemplateVars($modelFactory);

        $testHelper = file_get_contents($this->getTestHelperStub());
        $testHelper = $this->replaceTemplateVars($testHelper);

        $this->info('Add this to an items array in your app/config/motor-backend-navigation.php');
        echo $navigation."\n";

        $this->info('Add this to the backend and api route groups in your routes/web.php and routes/api.php');
        echo $route."\n";

        $this->info('Add this to the boot method in your app/Providers/RouteServiceProvider.php (or your own service provider)');
        echo $routeModelBinding."\n";

        $this->info('Add this to your database/factories/ModelFactory.php');
        echo $modelFactory."\n";

        $this->info('Add this to your tests/helpers/test_helper.php (you might need to create the file first)');
        echo $testHelper."\n";
    }
}
