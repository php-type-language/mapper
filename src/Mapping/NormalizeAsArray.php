<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class NormalizeAsArray
{
    public function __construct(
        /**
         * Enables normalization of an object value as an associative
         * {@see array} if {@see $enabled} is set to {@see true} or use
         * anonymous {@see object} in case of parameter is set to {@see false}.
         */
        public readonly bool $enabled = true,
    ) {}
}
