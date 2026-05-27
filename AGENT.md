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

## Tenant scoping (ZRMDEV-165)

Per-request tenant isolation for V2 API traffic. Five components in this package compose the system; downstream packages adopt them by trait or middleware.

### Resolver contract

A single per-request resolver controls every layer. Bound under `ClientScope::RESOLVER_KEY` (`'client.scope.resolver'`) as a `Closure(): ?array<int>`. Three return shapes:

| Returns | Meaning |
|---|---|
| `null` | SuperAdmin / unscoped — no filter applied anywhere. |
| `[]` | Authenticated user with no client pivot — every query returns zero rows, every authz check denies. |
| `[1, 2, …]` | Allowed client ids — `whereIn` applied; in-list = allowed. |
| *(unbound)* | V1 / console / job / public path — every layer no-ops. |

The resolver is bound by `ScopeRequestsToClient` middleware on V2 routes only. Test code uses the `actingAsClientScopedUser($user)` Pest helper to bind it explicitly.

### Components

| Component | Purpose |
|---|---|
| `Scopes\ClientScope` | Eloquent global scope. Reads the resolver and adds `whereIn($table.client_id, $ids)`. Also exposes `ClientScope::deniesForModel(?Model, string)` as the static counterpart for the trait check. |
| `Traits\BelongsToClient` | Boots `ClientScope`, declares the `client()` belongsTo via `config('motor-admin.models.client')`, and has a `creating` hook that auto-fills `client_id` when the resolver returns exactly one id. Used by tenanted models in downstream packages. |
| `Search\ClientScopedSearch` | Wraps `Model::search($query)` with the resolver-driven Scout filter. V2 search call sites use `Model::searchScopedToClient($query)` (provided by the trait) or `ClientScopedSearch::for($modelClass, $query)` directly. |
| `Traits\AuthorizesClientAccess` | Policy-side `denyForeignClient(?Model)`. Returns true when the resolver is bound, not SuperAdmin, and the model's `client_id` is not in the allowed list — including when the model itself is `null` (parent-traversal chains denying naturally). |
| `Http\Middleware\ScopeRequestsToClient` | Binds the resolver from `Auth::user()->clients()` on `handle()`; clears it on `terminate()` to prevent leakage across worker processes. |
| `Http\Requests\ValidatesAgainstUserClients` | Trait for V2 PostRequests. Composes `Rule::in(allowed_client_ids)` onto the existing `client_id` rule list. SuperAdmin gets every seeded client; everyone else gets exactly their pivot. |

### Opt-in / opt-out

- **Opt-in for a model:** `use Motor\Core\Traits\BelongsToClient;`. The trait expects a `client_id` column. For tenanted models without that column (e.g. `Score`, `SeoRedirect`, `CustomContentField{,Data,Conditional}`), do NOT use the trait — enforce tenancy via parent-traversal in the policy instead (`denyForeignClient($model->parent)` reads cleanly because the trait method is null-tolerant).
- **Opt-in for a policy:** `use Motor\Core\Traits\AuthorizesClientAccess;` and call `if ($this->denyForeignClient($model)) return false;` at the top of every per-instance ability (`view`, `update`, `delete`, `restore`, `forceDelete`, plus any custom abilities). `viewAny` and `create` are not gated here — list endpoints rely on the global scope; create is gated by the `ValidatesAgainstUserClients` trait on the V2 PostRequest.
- **Opt-out per-row:** the trait does not include a `withoutClientAutoFill()` helper. Seeders and factories don't bind a resolver anyway, so the auto-fill never fires for them.

### Queue / job caveat

The middleware's `terminate()` clears the binding so it does not leak into sync queue dispatches running mid-request, Octane-style request reuse, or other long-lived processes. Jobs that need the same scoping must bind the resolver themselves before dispatching scoped queries — pass the user (or the resolver array) into the job constructor and bind in `handle()`. This is intentional; a job inheriting an arbitrary tenant's resolver would be a security hole.

### Rollout reindex

Phase 2 added `client_id` to several `toSearchableArray()` outputs. Existing Meilisearch documents lack the field and will be filtered out for any non-SuperAdmin caller until reindexed. See `docs/runbooks/zrmdev-165-meilisearch-reindex.md` for the per-index `scout:flush` + `scout:import` sequence.

## Testing

This package has minimal direct tests. Its functionality is tested indirectly through the packages that extend it (primarily motor-admin).

## Code Style

- Follow existing patterns when extending base classes
- All filter renderers must implement the renderer interface
- Traits should be composable and not depend on each other
