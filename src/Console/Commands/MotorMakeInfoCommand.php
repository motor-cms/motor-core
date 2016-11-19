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
    protected $signature = 'motor:make:info {name}';

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

        $this->info('Add this to an items array in your app/config/backend/navigation.php');
        echo $navigation."\n";

        $this->info('Add this to the backend route group in your app/Http/routes.php');
        echo $route."\n";

        $this->info('Add this to the boot method in your app/Providers/RouteServiceProvider.php');
        echo $routeModelBinding."\n";
    }
}
