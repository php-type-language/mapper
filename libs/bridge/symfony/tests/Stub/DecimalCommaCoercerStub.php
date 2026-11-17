<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperCoercer;
use TypeLang\Mapper\Coercer\CoercerInterface;
use TypeLang\Mapper\Coercer\FloatCoercer;

/**
 * @template-implements CoercerInterface<float>
 */
#[AsMapperCoercer('float', mappers: ['api'])]
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
