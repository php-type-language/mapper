<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

final class SourceInfo
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        public readonly string $file,
        /**
         * @var int<1, max>
         */
        public readonly int $line,
    ) {}
}
