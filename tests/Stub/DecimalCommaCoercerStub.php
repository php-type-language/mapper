<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Coercer\CoercerInterface;
use TypeLang\Mapper\Coercer\FloatCoercer;

/**
 * @template-implements CoercerInterface<float>
 */
final class DecimalCommaCoercerStub implements CoercerInterface
{
    public function coerce(mixed $value): ?float
    {
        if (\is_string($value)) {
            $value = \str_replace(',', '.', $value);
        }

        return (new FloatCoercer())->coerce($value);
    }
}
