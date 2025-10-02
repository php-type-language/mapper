<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info;

final class TypeInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $declaration,
    ) {}
}
