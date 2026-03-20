# Motor Core Commands

## Utility Commands

| Command | Description |
|---------|-------------|
| `motor:core:generate-docs` | Generate OpenAPI/Swagger API documentation |
| `motor:core:set-package-dev {status}` | Toggle the `MOTOR_PACKAGE_DEVELOPMENT` environment variable for local package development |

`generate-docs` accepts an optional `{documentation?}` argument to generate a specific documentation set, or `--all` to regenerate all sets.

## Scaffolding (motor:make:*)

Module scaffolding commands for generating Motor CMS package components.

| Command | Description |
|---------|-------------|
| `motor:make:module {name}` | Generate a complete Motor backend module (model, migration, controller, service, resource, requests, tests) |
| `motor:make:controller {name}` | Generate a Motor API controller |
| `motor:make:model {name}` | Generate a Motor model |
| `motor:make:migration {name}` | Generate a Motor migration |
| `motor:make:service {name}` | Generate a Motor service class |
| `motor:make:resource {name}` | Generate a Motor API resource |
| `motor:make:request {name}` | Generate a Motor form request |
| `motor:make:factory` | Generate a Motor model factory |
| `motor:make:seeder` | Generate a Motor seeder |
| `motor:make:policy` | Generate a Motor policy |
| `motor:make:test {name} {type}` | Generate a Motor test |
| `motor:make:info {name}` | Display config information for a given module |

All scaffolding commands support `--path` and `--namespace` for generating into custom package directories.
