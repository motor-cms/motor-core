# AGENT.md -- motor-core

Instructions for AI agents working on this package.

## Purpose

Foundation package providing base classes, traits, filters, services, and renderers that all other Motor CMS packages extend. This package contains **no models of its own** (only Data Transfer Objects) -- it provides the scaffolding that domain packages build upon.

## Key Base Classes

| Class | Purpose | Extended By |
|-------|---------|-------------|
| `Filter\Filter` | Query filtering orchestration | All package filters |
| `Http\Controllers\Api\V2\ApiController` | V2 CRUD with authorization + error handling | All V2 API controllers |
| `Http\Resources\V2\BaseResource` | Wraps in `data` key, adds `meta.api_version: 'v2'` | All V2 resources |
| `Http\Resources\V2\BaseCollection` | Paginated collections with V2 meta | All V2 collections |
| `Http\Traits\V2\HandlesApiErrors` | Standardized error responses (401, 403, 404, 422, 500) | V2 ApiController |

### V2 Error Response Format

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable message",
    "details": {}
  },
  "meta": { "api_version": "v2" }
}
```

### V2ErrorHandler Middleware

`Http\Middleware\V2\V2ErrorHandler` catches exceptions and formats them as V2 error envelopes. Handles: `ValidationException`, `AuthorizationException`, `AuthenticationException`, `ModelNotFoundException`, `NotFoundHttpException`. Applied automatically to all V2 routes.

## Filter System

The filter system is the most complex part of this package. It lives in `src/Filter/`:

```
Filter/
├── Base.php             # Abstract base filter
├── Filter.php           # Main filter class (orchestrates renderers)
└── Renderers/
    ├── SelectRenderer.php    # Dropdown filter options
    ├── WhereRenderer.php     # Direct where clauses
    ├── SearchRenderer.php    # Full-text search
    ├── SortRenderer.php      # Column sorting
    ├── PerPageRenderer.php   # Pagination size
    └── RelationRenderer.php  # Relationship-based filtering
```

### How Filters Work

1. Each model has a `Filter` class that extends `Filter\Filter`
2. The filter class registers renderers via `addRenderer()`
3. Controllers call `$filter->filter()` which applies all renderers to the query
4. The `Filterable` trait on models provides the `filteredBy()` scope

### Modifying Filters

- Add new filter options in the model's filter class
- Add new renderer types in `src/Filter/Renderers/`
- The `Filterable` trait (`src/Traits/Filterable.php`) provides the bridge between models and filters

## Traits

| Trait | File | Purpose |
|-------|------|---------|
| `Filterable` | `src/Traits/Filterable.php` | Adds `filteredBy()` scope and `searchableOptions` to models |
| `Searchable` | `src/Traits/Searchable.php` | Laravel Scout search integration |
| `CheckForeignKeys` | `src/Traits/CheckForeignKeys.php` | Validates foreign key constraints before deletion |

## Global Search

`Services\GlobalSearchService` provides cross-module Meilisearch integration:
- **Route:** `GET /api/v2/global-search?q=...&limit=25&page=1` (Sanctum-protected)
- Searches across multiple CMS modules simultaneously
- Supports module-specific prefix syntax: `user: admin` searches only users
- Configuration via `global-search.modules` array in each package's config

DTOs:
- `Data\GlobalSearchHitData` -- individual search result (module, index, id, title, excerpt, meta, score)
- `Data\GlobalSearchMetaData` -- search metadata (total, page, per_page, module counts)
- `Data\GlobalSearchResultData` -- complete result set

## Scaffolding Commands

Located in `src/Console/Commands/`. Generate Motor-convention files:

| Command | Generates |
|---------|-----------|
| `motor:make:module {name}` | Complete module (model, migration, controller, service, resource, requests, tests) |
| `motor:make:controller {name}` | API controller |
| `motor:make:model {name}` | Eloquent model with factory |
| `motor:make:service {name}` | Service class |
| `motor:make:resource {name}` | API resource |
| `motor:make:request {name}` | Form request |
| `motor:make:test {name}` | Pest test file |

## HTTP Layer

### Middleware

Located in `src/Http/Middleware/` -- shared middleware used across all packages.

### V2 Base Controller

`src/Http/Controllers/Api/V2/BaseApiController` provides standardized CRUD operations for V2 API endpoints.

## Console Commands

Located in `src/Console/` -- includes scaffolding generators and utility commands.

## Helpers

`src/Helpers/` -- shared helper functions available across all packages.

## Architecture Notes

- This package is the **foundation layer**. It has no dependencies on other Motor packages.
- All other packages depend on motor-core.
- Changes here affect the entire system -- test thoroughly.
- No Eloquent models in this package -- only DTOs in `src/Data/`.
- The `Support/` directory contains additional support classes.

## Testing

This package has minimal direct tests. Its functionality is tested indirectly through the packages that extend it (primarily motor-admin).

## Code Style

- Follow existing patterns when extending base classes
- All filter renderers must implement the renderer interface
- Traits should be composable and not depend on each other
