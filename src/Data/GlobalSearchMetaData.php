<?php

namespace Motor\Core\Data;

use JsonSerializable;

class GlobalSearchMetaData implements JsonSerializable
{
    public function __construct(
        public readonly string $api_version,
        public readonly string $query,
        public readonly ?string $parsed_module,
        public readonly string $parsed_term,
        public readonly int $total,
        public readonly int $page,
        public readonly int $per_page,
        public readonly int $last_page,
        /** @var array<string, int> */
        public readonly array $modules,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'api_version' => $this->api_version,
            'query' => $this->query,
            'parsed_module' => $this->parsed_module,
            'parsed_term' => $this->parsed_term,
            'total' => $this->total,
            'page' => $this->page,
            'per_page' => $this->per_page,
            'last_page' => $this->last_page,
            'modules' => $this->modules,
        ];
    }
}
