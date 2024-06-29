<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapProperty
{
    /**
     * @param non-empty-string|null $type
     * @param non-empty-string|null $name
     */
    public function __construct(
        public ?string $type = null,
        public ?string $name = null,
    ) {}
}
