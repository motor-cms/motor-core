# Motor Core

Foundation package for the Motor CMS framework. Provides base classes, traits, filters, services, and renderers that all other Motor packages extend.

## Installation

```bash
composer require motor-cms/motor-core
```

## What This Package Provides

### Base Classes

- **Filter system** -- Query filtering with pluggable renderers (select, where, search, sort, pagination, relation)
- **HTTP layer** -- Base controllers, resources, requests, and middleware
- **Service support** -- Service layer infrastructure
- **Console commands** -- Scaffolding generators

### Traits

| Trait | Purpose |
|-------|---------|
| `Filterable` | Adds query filtering scope and searchable options to models |
| `Searchable` | Laravel Scout search integration |
| `CheckForeignKeys` | Validates foreign key constraints before deletion |

### Filter Renderers

| Renderer | Purpose |
|----------|---------|
| `SelectRenderer` | Dropdown filter options |
| `WhereRenderer` | Direct where clause filtering |
| `SearchRenderer` | Full-text search |
| `SortRenderer` | Column sorting |
| `PerPageRenderer` | Pagination size |
| `RelationRenderer` | Relationship-based filtering |

### Global Search

Cross-model search service with typed DTOs for search results.

## Package Structure

```
src/
├── Console/          # Scaffolding generators
├── Data/             # Data Transfer Objects
├── Database/         # Database support classes
├── Filter/           # Filter system (Base, Filter, Renderers/)
├── Helpers/          # Shared helper functions
├── Http/             # Controllers, Middleware, Requests, Resources, Traits
├── L5Swagger/        # OpenAPI documentation support
├── Providers/        # Service providers
├── Services/         # Service layer (GlobalSearchService, AWS)
├── Support/          # Additional support classes
└── Traits/           # Filterable, Searchable, CheckForeignKeys
```

## Dependencies

This package has no dependencies on other Motor packages. It is the foundation layer.

## Credits

- [Reza Esmaili](https://github.com/dfox288)
