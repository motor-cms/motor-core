<?php

namespace Motor\Core\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    /**
     * Add api_version to all V2 collection responses.
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'api_version' => 'v2',
            ],
        ];
    }
}
