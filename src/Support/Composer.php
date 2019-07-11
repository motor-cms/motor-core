<?php

namespace Motor\Core\Support;

/**
 * Class Composer
 * @package Motor\Core\Support
 */
class Composer extends \Illuminate\Support\Composer
{

    /**
     * Regenerate the Composer autoloader files.
     *
     * @param string $extra
     * @return void
     */
    public function dumpAutoloads($extra = ''): void
    {
        $prefix  = (env('MOTOR_PACKAGE_DEVELOPMENT') ? $prefix = 'COMPOSER=composer-dev.json ' : '');
        $process = $this->getProcess([$prefix . trim($this->findComposer() . ' dump-autoload ' . $extra)]);
        $process->run();
    }
}
