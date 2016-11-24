<?php

namespace Motor\Core\Support;

class Composer extends \Illuminate\Support\Composer
{

    /**
     * Regenerate the Composer autoloader files.
     *
     * @param  string $extra
     *
     * @return void
     */
    public function dumpAutoloads($extra = '')
    {
        $process = $this->getProcess();

        $prefix = (env('MOTOR_PACKAGE_DEVELOPMENT') ? $prefix = 'COMPOSER=composer-dev.json ' : '');

        $process->setCommandLine($prefix.trim($this->findComposer() . ' dump-autoload ' . $extra));

        $process->run();
    }
}
