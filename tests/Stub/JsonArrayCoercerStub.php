<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Coercer\ArrayCoercer;
use TypeLang\Mapper\Coercer\CoercerInterface;

/**
 * @template-implements CoercerInterface<array<array-key, mixed>>
 */
final class JsonArrayCoercerStub implements CoercerInterface
{
    public function coerce(mixed $value): ?array
    {
        if (\is_string($value)) {
            $value = \json_decode($value, true);
        }

        return (new ArrayCoercer())->coerce($value);
    }
}
