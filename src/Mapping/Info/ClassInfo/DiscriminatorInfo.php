<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info\ClassInfo;

use TypeLang\Mapper\Mapping\Info\ClassInfo\DiscriminatorInfo\DiscriminatorMapInfo;

final class DiscriminatorInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $field,
        public readonly DiscriminatorMapInfo $map,
        /**
         * @var non-empty-string|null
         */
        public ?string $default = null,
    ) {}
}
