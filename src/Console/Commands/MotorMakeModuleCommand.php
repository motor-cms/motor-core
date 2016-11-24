<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class MotorMakeModuleCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:make:module {name} {locale=en} {{--path=} {--namespace=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a motor backend module with stubs';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $classSingular = Str::singular(Str::studly($this->argument('name')));
        $classPlural   = Str::plural(Str::studly($this->argument('name')));
        $table         = Str::plural(Str::snake(class_basename($this->argument('name'))));

        $extraoptions = [];
        if ( ! is_null($this->option('path'))) {
            $extraoptions['--path'] = $this->option('path');
        }
        if ( ! is_null($this->option('namespace'))) {
            $extraoptions['--namespace'] = $this->option('namespace');
        }

        // Create model
        $this->call('motor:make:model', array_merge([ 'name' => $classSingular ], $extraoptions));

        // Create migration
        $this->call('motor:make:migration', array_merge([ 'name' => "create_{$table}_table", '--create' => $table ], $extraoptions));

        // Create grid
        $this->call('motor:make:grid', array_merge([ 'name' => $classSingular . 'Grid' ], $extraoptions));

        // Create request
        $this->call('motor:make:request', array_merge([ 'name' => 'Backend/' . $classSingular . 'Request' ], $extraoptions));

        // Create controller
        $this->call('motor:make:controller', array_merge([ 'name' => 'Backend/' . $classPlural . 'Controller' ], $extraoptions));

        // Create controller
        $this->call('motor:make:controller', array_merge([ 'name' => 'Api/' . $classPlural . 'Controller' , '--type' => 'api' ], $extraoptions));

        // Create service
        $this->call('motor:make:service', array_merge([ 'name' => $classSingular . 'Service' ], $extraoptions));

        // Create transformer
        $this->call('motor:make:transformer', array_merge([ 'name' => $classSingular . 'Transformer' ], $extraoptions));

        // Create form
        $this->call('motor:make:form', array_merge([ 'name' => 'Forms/Backend/' . $classSingular . 'Form' ], $extraoptions));

        // Create test for backend controller
        $this->call('motor:make:test', array_merge([ 'name' => $classSingular , 'type' => 'backend'], $extraoptions));

        // Create test for api controller
        $this->call('motor:make:test', array_merge([ 'name' => $classSingular, 'type' => 'api' ], $extraoptions));

        // Create i18n file
        $this->call('motor:make:i18n', array_merge([ 'name' => $classPlural, 'locale' => $this->argument('locale') ], $extraoptions));

        // Create view files
        $this->call('motor:make:view', array_merge([ 'name' => $classPlural, 'type' => 'create' ], $extraoptions));
        $this->call('motor:make:view', array_merge([ 'name' => $classPlural, 'type' => 'edit' ], $extraoptions));
        $this->call('motor:make:view', array_merge([ 'name' => $classPlural, 'type' => 'index' ], $extraoptions));
        $this->call('motor:make:view', array_merge([ 'name' => $classPlural, 'type' => 'form' ], $extraoptions));

        // Display config information
        $this->call('motor:make:info', array_merge([ 'name' => $classPlural ], $extraoptions));
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [ 'name', InputArgument::REQUIRED, 'The name of the module' ],
        ];
    }

}
