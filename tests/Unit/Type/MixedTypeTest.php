<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type;

use TypeLang\Mapper\Context;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

final class MixedTypeTest extends TypeTestCase
{
    protected function getType(): TypeInterface
    {
        return new MixedType();
    }

    protected function getNormalizationExpectation(mixed $value, ValueType $type, Context $ctx): mixed
    {
        return $value;
    }
}
