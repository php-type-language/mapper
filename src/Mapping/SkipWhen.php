<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use JetBrains\PhpStorm\Language;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SkipWhen
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        #[Language('JavaScript')]
        public readonly string $expr,
    ) {}
}
