<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use L5Swagger\Exceptions\L5SwaggerException;
use Motor\Core\L5Swagger\GeneratorFactory;

class GenerateDocsCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor-swagger:generate {documentation?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate docs';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws L5SwaggerException
     */
    public function handle(GeneratorFactory $generatorFactory)
    {
        $all = $this->option('all');

        if ($all) {
            $documentations = array_keys(config('l5-swagger.documentations', []));

            foreach ($documentations as $documentation) {
                $this->generateDocumentation($generatorFactory, $documentation);
            }

            return;
        }

        $documentation = $this->argument('documentation');

        if (! $documentation) {
            $documentation = config('l5-swagger.default');
        }

        $this->generateDocumentation($generatorFactory, $documentation);
    }

    /**
     * @throws L5SwaggerException
     */
    private function generateDocumentation(GeneratorFactory $generatorFactory, string $documentation)
    {
        $this->info('Regenerating docs '.$documentation);

        $generator = $generatorFactory->make($documentation);
        $generator->generateDocs();
    }
}
