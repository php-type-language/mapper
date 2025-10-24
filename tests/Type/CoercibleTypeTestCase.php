<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

abstract class CoercibleTypeTestCase extends TypeTestCase
{
    abstract protected static function createType(?TypeCoercerInterface $coercer = null): TypeInterface;

    /**
     * @api
     */
    public function testUsesTypeCoercion(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('on-coerce');

        $coercer = $this->createMock(TypeCoercerInterface::class);
        $coercer->method('coerce')
            ->willThrowException(new \BadMethodCallException('on-coerce'));

        $value = \fopen('php://memory', 'rb');

        $type = static::createType($coercer);
        $type->cast($value, $this->createNormalizationContext($value, false));
    }
}
