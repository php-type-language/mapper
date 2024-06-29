<?php

declare(strict_types=1);

namespace Serafim\Mapper\Tests\Unit\Type;

use Serafim\Mapper\Context;
use Serafim\Mapper\Type\MixedType;
use Serafim\Mapper\Type\TypeInterface;

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
