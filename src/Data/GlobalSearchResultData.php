<?php

namespace Motor\Core\Data;

use JsonSerializable;

class GlobalSearchResultData implements JsonSerializable
{
    /**
     * @param  GlobalSearchHitData[]  $data
     */
    public function __construct(
        public readonly array $data,
        public readonly GlobalSearchMetaData $meta,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'meta' => $this->meta,
        ];
    }
}
