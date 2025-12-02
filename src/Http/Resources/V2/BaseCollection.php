<?php

namespace Motor\Core\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    /**
     * Customize pagination format for V2.
     * Note: api_version is only added here (not in with()) to avoid duplication.
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'api_version' => 'v2',
                'pagination' => [
                    'current_page' => $paginated['current_page'],
                    'per_page' => $paginated['per_page'],
                    'total' => $paginated['total'],
                    'total_pages' => $paginated['last_page'],
                    'has_more' => $paginated['current_page'] < $paginated['last_page'],
                ],
            ],
            'links' => [
                'first' => $paginated['first_page_url'],
                'prev' => $paginated['prev_page_url'],
                'next' => $paginated['next_page_url'],
                'last' => $paginated['last_page_url'],
            ],
        ];
    }
}
