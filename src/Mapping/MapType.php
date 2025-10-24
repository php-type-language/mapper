<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class MapType
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $type,
        public readonly ?bool $strict = null,
    ) {}
}
