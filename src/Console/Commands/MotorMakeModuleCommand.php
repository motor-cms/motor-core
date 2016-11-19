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
    protected $signature = 'motor:make:module {name} {locale=en} {--namespace=}';

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
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));
        
        // Create model
        $this->call('motor:make:model', [ 'name' => $classSingular ]);

        // Create migration
        $this->call('motor:make:migration', [ 'name' => "create_{$table}_table", '--create' => $table ]);

        // Create grid
        $this->call('motor:make:grid', [ 'name' => $classSingular . 'Grid' ]);

        // Create request
        $this->call('motor:make:request', [ 'name' => 'Backend/' . $classSingular . 'Request' ]);

        // Create controller
        $this->call('motor:make:controller', [ 'name' => 'Backend/' . $classPlural . 'Controller' ]);

        // Create form
        $this->call('make:form', [ 'name' => 'Forms/Backend/' . $classSingular . 'Form' ]);

        // Create i18n file
        $this->call('motor:make:i18n', [ 'name' => $classPlural, 'locale' => $this->argument('locale') ]);

        // Create view files
        $this->call('motor:make:view', [ 'name' => $classPlural, 'type' => 'create' ]);
        $this->call('motor:make:view', [ 'name' => $classPlural, 'type' => 'edit' ]);
        $this->call('motor:make:view', [ 'name' => $classPlural, 'type' => 'index' ]);
        $this->call('motor:make:view', [ 'name' => $classPlural, 'type' => 'form' ]);

        // Display config information
        $this->call('motor:make:info', [ 'name' => $classPlural ]);
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
