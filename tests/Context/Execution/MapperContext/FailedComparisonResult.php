<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution\MapperContext;

final class FailedComparisonResult extends ComparisonResult
{
    public function __construct(mixed $value, \Throwable $result)
    {
        parent::__construct($value, $result);
    }
}
