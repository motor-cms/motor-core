<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class MotorMakeModuleCommand
 */
class MotorMakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:module {name} {locale=en} {--path=} {--namespace=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a motor backend module with stubs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $classSingular = Str::singular(Str::studly($this->argument('name')));
        $classPlural = Str::plural(Str::studly($this->argument('name')));
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        $extraoptions = ['--path' => 'app', '--namespace' => null];

        if (! is_null($this->option('path'))) {
            $extraoptions['--path'] = $this->option('path');
        }
        if (! is_null($this->option('namespace'))) {
            // //$extraoptions['--namespace'] = $this->option('namespace') . (str_ends_with($this->option('namespace'), '\\') ? '' : '\\') ;
            // $extraoptions['--namespace'] = $this->option('namespace');
            $extraoptions['--namespace'] = (str_ends_with($this->option('namespace'), '\\') ? substr($this->option('namespace'), 0, -1) : $this->option('namespace'));
        }

        // FIXME: evil hack to tell laravel that the models are inside a Models directory even if the directory does not exist
        if (! is_dir(app_path('Models'))) {
            $filesystem = new Filesystem;
            $filesystem->makeDirectory(app_path('Models'), 0755, true);
        }

        // Create model
        $this->call('motor:make:model', array_merge(['name' => $classSingular], $extraoptions));

        // Create migration
        // Strip namespace from migration command
        $migrationOptions = $extraoptions;

        $migrationOptions['--path'] = $migrationOptions['--path'].'/../database/migrations';
        unset($migrationOptions['--namespace']);
        $this->call('motor:make:migration', array_merge(['name' => "create_{$table}_table", '--create' => $table], $migrationOptions));

        // Create POST request
        $this->call('motor:make:request', array_merge(['name' => 'Api/'.$classSingular.'PostRequest'], $extraoptions));

        // Create PATCH request
        $this->call('motor:make:request', array_merge(['name' => 'Api/'.$classSingular.'PatchRequest'], $extraoptions));

        // Create controller
        $this->call('motor:make:controller', array_merge(['name' => 'Api/'.$classPlural.'Controller', '--type' => 'api'], $extraoptions));

        // Create service
        $this->call('motor:make:service', array_merge(['name' => $classSingular.'Service'], $extraoptions));

        // Create resource
        $this->call('motor:make:resource', array_merge(['name' => $classSingular.'Resource'], $extraoptions));

        // Create resource
        $this->call('motor:make:resource', array_merge(['name' => $classSingular.'Collection'], $extraoptions));

        // Create policy
        $this->call('motor:make:policy', array_merge(['--model' => $classSingular, 'name' => $classSingular.'Policy'], $extraoptions));

        // Create factory
        $this->call('motor:make:factory', array_merge(['--model' => $classSingular, 'name' => $classSingular], $extraoptions));

        // Create seeder
        $this->call('motor:make:seeder', array_merge(['--model' => $classSingular, 'name' => $classPlural], $extraoptions));

        // Display config information
        $this->call('motor:make:info', array_merge(['name' => $classPlural], $extraoptions));
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the module'],
        ];
    }
}
