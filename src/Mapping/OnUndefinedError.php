<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OnUndefinedError
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $message,
    ) {}
}
