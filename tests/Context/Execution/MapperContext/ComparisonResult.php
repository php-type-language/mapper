<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution\MapperContext;

class ComparisonResult
{
    public function __construct(
        public readonly mixed $input,
        public readonly mixed $result,
    ) {}
}
