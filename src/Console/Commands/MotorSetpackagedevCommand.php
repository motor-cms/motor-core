<?php

namespace Motor\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MotorSetpackagedevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor:setpackagedev {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set motor package development environment variable. Currently only used when making migrations as they dump the composer autoloader, which, during packag development, is different';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Load .env
        $env = file(base_path('.env'));

        foreach ($env as $key => $line) {
            if (strpos($line, 'MOTOR_PACKAGE_DEVELOPMENT') !== false) {
                $env[$key] = 'MOTOR_PACKAGE_DEVELOPMENT='.$this->argument('status');
            }
        }

        file_put_contents(base_path('.env'), implode("", $env));

        $this->info('Set MOTOR_PACKAGE_DEVELOPMENT environment variable to '.$this->argument('status'));
    }
}
