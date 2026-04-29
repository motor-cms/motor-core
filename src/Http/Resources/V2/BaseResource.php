<?php

namespace Motor\Core\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    /**
     * Ensure V2 resources are always wrapped in 'data'.
     */
    public static $wrap = 'data';

    /**
     * Add api_version to all V2 resource responses.
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
