<?php

namespace Motor\Core\L5Swagger;

use Illuminate\Support\Arr;
use OpenApi\Generator as OpenApiGenerator;

class Generator extends \L5Swagger\Generator
{
    protected function setProcessors(OpenApiGenerator $generator): void
    {
        $processorClasses = Arr::get($this->scanOptions, self::SCAN_OPTION_PROCESSORS, []);
        $processors = [];

        foreach ($processorClasses as $customProcessor) {
            $processors[] = $customProcessor;
        }

        if (! empty($processors)) {
            $generator->setProcessors($processors);
        }
    }
}
