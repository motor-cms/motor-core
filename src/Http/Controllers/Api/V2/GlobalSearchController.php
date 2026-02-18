<?php

namespace Motor\Core\Http\Controllers\Api\V2;

use Motor\Core\Data\GlobalSearchResultData;
use Motor\Core\Http\Requests\Api\GlobalSearchGetRequest;
use Motor\Core\Services\GlobalSearchService;

/**
 * Global Search
 *
 * Search across all CMS modules (users, pages, files, etc.) via Meilisearch.
 *
 * @tags Global Search
 */
class GlobalSearchController extends ApiController
{
    /**
     * Search across all modules
     *
     * Performs a unified search across all indexed CMS modules. Results are ranked by
     * Meilisearch relevance score and returned as a flat list. Use module prefix syntax
     * (e.g. `user: admin`) to filter results to a single module.
     *
     * @response array{data: list<array{module: string, index: string, id: int|string|null, title: string|null, excerpt: string|null, meta: array<string, mixed>, score: float}>, meta: array{api_version: string, query: string, parsed_module: string|null, parsed_term: string, total: int, page: int, per_page: int, last_page: int, modules: array<string, int>}}
     */
    public function __invoke(GlobalSearchGetRequest $request): GlobalSearchResultData
    {
        $service = new GlobalSearchService;

        return $service->search(
            query: $request->validated('q'),
            limit: (int) $request->validated('limit', 25),
            page: (int) $request->validated('page', 1),
        );
    }
}
