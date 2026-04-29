<?php

namespace Motor\Core\Data;

use JsonSerializable;

class GlobalSearchHitData implements JsonSerializable
{
    public function __construct(
        public readonly string $module,
        public readonly string $index,
        public readonly int|string|null $id,
        public readonly mixed $title,
        public readonly mixed $excerpt,
        /** @var array<string, mixed> */
        public readonly array $meta,
        public readonly float $score,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'module' => $this->module,
            'index' => $this->index,
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'meta' => $this->meta,
            'score' => $this->score,
        ];
    }
}
