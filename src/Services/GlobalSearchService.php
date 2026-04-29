<?php

namespace Motor\Core\Services;

use Illuminate\Support\Str;
use Meilisearch\Client;
use Meilisearch\Contracts\SearchQuery;
use Motor\Core\Data\GlobalSearchHitData;
use Motor\Core\Data\GlobalSearchMetaData;
use Motor\Core\Data\GlobalSearchResultData;

class GlobalSearchService
{
    protected Client $client;

    protected array $modules;

    protected string $scoutPrefix;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->modules = config('global-search.modules', []);
        $this->scoutPrefix = config('scout.prefix', '');
    }

    public function search(string $query, int $limit = 25, int $page = 1, ?string $moduleFilter = null): GlobalSearchResultData
    {
        $parsed = $this->parseQuery($query);
        $searchTerm = $parsed['term'];
        $targetModule = $parsed['module'];

        // Explicit ?module= param takes precedence over prefix syntax
        if ($moduleFilter !== null) {
            $targetModule = null;
            $activeModules = $this->resolveActiveModulesByPackage($moduleFilter);
        } else {
            $activeModules = $this->resolveActiveModules($targetModule);
        }

        if (empty($activeModules)) {
            return $this->emptyResponse($query, $targetModule, $searchTerm, $limit, $page);
        }

        $offset = ($page - 1) * $limit;

        $searchQueries = $this->buildSearchQueries($activeModules, $searchTerm, $offset, $limit);

        $results = $this->client->multiSearch($searchQueries);

        return $this->formatResults($results, $activeModules, $query, $targetModule, $searchTerm, $limit, $page, $offset);
    }

    protected function parseQuery(string $query): array
    {
        if (preg_match('/^(\w+):\s*(.+)$/', $query, $matches)) {
            $prefix = $matches[1];
            $term = $matches[2];

            $moduleKey = $this->resolveModuleKey($prefix);

            if ($moduleKey !== null) {
                return ['module' => $moduleKey, 'term' => $term];
            }
        }

        return ['module' => null, 'term' => $query];
    }

    protected function resolveModuleKey(string $prefix): ?string
    {
        $prefix = strtolower($prefix);

        if (isset($this->modules[$prefix])) {
            return $prefix;
        }

        $singular = Str::singular($prefix);
        if (isset($this->modules[$singular])) {
            return $singular;
        }

        $plural = Str::plural($prefix);
        if (isset($this->modules[$plural])) {
            return $plural;
        }

        return null;
    }

    protected function resolveActiveModules(?string $targetModule): array
    {
        if ($targetModule !== null) {
            return isset($this->modules[$targetModule])
                ? [$targetModule => $this->modules[$targetModule]]
                : [];
        }

        return $this->modules;
    }

    /**
     * Filter modules by their package name (e.g. "motor-admin", "motor-media").
     */
    protected function resolveActiveModulesByPackage(string $packageName): array
    {
        return array_filter($this->modules, fn (array $config) => $config['module'] === $packageName);
    }

    protected function buildSearchQueries(array $activeModules, string $searchTerm, int $offset, int $limit): array
    {
        $queries = [];

        foreach ($activeModules as $moduleConfig) {
            $searchQuery = new SearchQuery;
            $searchQuery->setIndexUid($this->scoutPrefix.$moduleConfig['index']);
            $searchQuery->setQuery($searchTerm);
            $searchQuery->setShowRankingScore(true);
            $searchQuery->setLimit($offset + $limit);

            if (! empty($moduleConfig['default_filter'])) {
                $searchQuery->setFilter([$moduleConfig['default_filter']]);
            }

            $queries[] = $searchQuery;
        }

        return $queries;
    }

    protected function formatResults(
        array $results,
        array $activeModules,
        string $originalQuery,
        ?string $targetModule,
        string $searchTerm,
        int $limit,
        int $page,
        int $offset,
    ): GlobalSearchResultData {
        $allHits = [];
        $moduleCounts = [];
        $totalEstimated = 0;
        $moduleKeys = array_keys($activeModules);

        foreach ($results['results'] as $i => $indexResult) {
            $moduleKey = $moduleKeys[$i];
            $moduleConfig = $activeModules[$moduleKey];
            $estimatedTotal = $indexResult['estimatedTotalHits'] ?? 0;
            $packageName = $moduleConfig['module'];
            $moduleCounts[$packageName] = ($moduleCounts[$packageName] ?? 0) + $estimatedTotal;
            $totalEstimated += $estimatedTotal;

            foreach ($indexResult['hits'] as $hit) {
                $allHits[] = new GlobalSearchHitData(
                    module: $packageName,
                    index: $moduleConfig['entity'],
                    id: $hit['id'] ?? null,
                    title: $this->extractField($hit, $moduleConfig['title_field']),
                    excerpt: $this->extractField($hit, $moduleConfig['excerpt_field']),
                    meta: $this->extractMeta($hit, $moduleConfig['meta_fields'] ?? []),
                    score: (float) ($hit['_rankingScore'] ?? 0),
                );
            }
        }

        usort($allHits, fn (GlobalSearchHitData $a, GlobalSearchHitData $b) => $b->score <=> $a->score);

        $paginatedHits = array_slice($allHits, $offset, $limit);

        $lastPage = $totalEstimated > 0 ? (int) ceil($totalEstimated / $limit) : 1;

        return new GlobalSearchResultData(
            data: array_values($paginatedHits),
            meta: new GlobalSearchMetaData(
                api_version: 'v2',
                query: $originalQuery,
                parsed_module: $targetModule,
                parsed_term: $searchTerm,
                total: $totalEstimated,
                page: $page,
                per_page: $limit,
                last_page: $lastPage,
                modules: $moduleCounts,
            ),
        );
    }

    protected function extractField(array $hit, string $field): mixed
    {
        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            $value = $hit;
            foreach ($parts as $part) {
                if (is_array($value) && array_key_exists($part, $value)) {
                    $value = $value[$part];
                } else {
                    return null;
                }
            }

            return $value;
        }

        return $hit[$field] ?? null;
    }

    protected function extractMeta(array $hit, array $metaFields): array
    {
        $meta = [];
        foreach ($metaFields as $field) {
            $meta[$field] = $this->extractField($hit, $field);
        }

        return $meta;
    }

    protected function emptyResponse(string $query, ?string $targetModule, string $searchTerm, int $limit, int $page): GlobalSearchResultData
    {
        return new GlobalSearchResultData(
            data: [],
            meta: new GlobalSearchMetaData(
                api_version: 'v2',
                query: $query,
                parsed_module: $targetModule,
                parsed_term: $searchTerm,
                total: 0,
                page: $page,
                per_page: $limit,
                last_page: 1,
                modules: [],
            ),
        );
    }
}
