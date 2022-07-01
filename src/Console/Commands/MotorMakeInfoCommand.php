<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Filesystem\Filesystem;

/**
 * Class MotorMakeInfoCommand
 */
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

    /**
     * @return string
     */
    protected function getTargetPath(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getTargetFile(): string
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getNavigationStub(): string
    {
        return __DIR__.'/stubs/info/navigation.stub';
    }

    /**
     * @return string
     */
    protected function getRouteStub(): string
    {
        return __DIR__.'/stubs/info/route.stub';
    }

    /**
     * @return string
     */
    protected function getApiRouteStub(): string
    {
        return __DIR__.'/stubs/info/apiroute.stub';
    }

    /**
     * @return string
     */
    protected function getRouteModelBindingStub(): string
    {
        return __DIR__.'/stubs/info/routemodelbinding.stub';
    }

    /**
     * @return string
     */
    protected function getModelFactoryStub(): string
    {
        return __DIR__.'/stubs/info/modelfactory.stub';
    }

    /**
     * @return string
     */
    protected function getTestHelperStub(): string
    {
        return __DIR__.'/stubs/info/testhelper.stub';
    }

    /**
     * @return string
     */
    protected function getPermissionStub(): string
    {
        return __DIR__.'/stubs/info/permissions.stub';
    }

    /**
     * @param $directory
     */
    protected function makeDirectory($directory): void
    {
        $filesystem = new Filesystem();
        if (! $filesystem->isDirectory($directory)) {
            $filesystem->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * @return string
     */
    protected function getTargetTestHelperFile(): string
    {
        $basePath = (! is_null($this->option('path')) ? $this->option('path').'/../tests/helpers' : resource_path().'/../tests/helpers');
        $this->makeDirectory($basePath);

        return $basePath.'/test_helper.php';
    }

    /**
     * @return string
     */
    protected function getTargetModelFactoryFile(): string
    {
        $basePath = (! is_null($this->option('path')) ? $this->option('path').'/../database/factories' : resource_path().'/../database/factories');
        $this->makeDirectory($basePath);

        return $basePath.'/ModelFactory.php';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $navigation = file_get_contents($this->getNavigationStub());
        $navigation = $this->replaceTemplateVars($navigation);

        $route = file_get_contents($this->getRouteStub());
        $route = $this->replaceTemplateVars($route);

        $apiRoute = file_get_contents($this->getApiRouteStub());
        $apiRoute = $this->replaceTemplateVars($apiRoute);

        $routeModelBinding = file_get_contents($this->getRouteModelBindingStub());
        $routeModelBinding = $this->replaceTemplateVars($routeModelBinding);

        $testHelper = file_get_contents($this->getTestHelperStub());
        $testHelper = $this->replaceTemplateVars($testHelper);

        $permission = file_get_contents($this->getPermissionStub());
        $permission = $this->replaceTemplateVars($permission);

        $this->info('Add this to an items array in your app/config/motor-backend-navigation.php');
        echo $navigation."\n";

        $this->info('Add this to the backend route groups in your routes/web.php');
        echo $route."\n";

        $this->info('Add this to the api route groups in your routes/api.php');
        echo $apiRoute."\n";

        $this->info('Add this to the boot method in your app/Providers/RouteServiceProvider.php (or your own service provider)');
        echo $routeModelBinding."\n";

        $this->info('Add this to your app/config/motor-backend-permissions.php file');
        echo $permission."\n";

        $testHelperFile = $this->getTargetTestHelperFile();

        if (! file_exists($testHelperFile)) {
            file_put_contents($testHelperFile, "<?php\r\n\r\n".$testHelper);
            $this->info('Generated tests/helpers/test_helper.php');
        } else {
            if (file_exists($testHelperFile) && strpos(file_get_contents($testHelperFile), $testHelper) === false) {
                $existingTestHelperFile = file_get_contents($testHelperFile);

                file_put_contents($testHelperFile, $existingTestHelperFile."\r\n".$testHelper);
                $this->info('Added new test helper to tests/helpers/test_helper.php');
            } else {
                $this->info('Add this to your tests/helpers/test_helper.php (if it doesn\'t exist yet)');
                echo $testHelper."\n";
            }
        }
    }
}
