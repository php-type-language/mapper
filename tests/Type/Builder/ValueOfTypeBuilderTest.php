<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Builder;

use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\ConstantsStub;
use TypeLang\Mapper\Tests\Stub\StatusStub;
use TypeLang\Mapper\Tests\Stub\SuitStub;
use TypeLang\Mapper\Tests\TestCase;

final class ValueOfTypeBuilderTest extends TestCase
{
    public function testValueOfBackedEnumIsKeptAsScalar(): void
    {
        $type = \sprintf('value-of<%s>', StatusStub::class);

        self::assertDenormalizes($type, 'banned', 'banned');
        self::assertNormalizes($type, 'active', 'active');
    }

    public function testValueOutOfCasesIsRejected(): void
    {
        $type = \sprintf('value-of<%s>', StatusStub::class);

        self::assertDenormalizationFails(
            $type,
            'unknown',
            '$: Value must be of type \'active\'|\'banned\', but "unknown" given',
        );
    }

    public function testValueOfOtherTypesIsNotSupported(): void
    {
        foreach ([SuitStub::class, ConstantsStub::class . '::LIST', 'int', 'array{a: int}'] as $argument) {
            $error = self::captureBuildError(\sprintf('value-of<%s>', $argument));

            self::assertInstanceOf(TypeDefinitionException::class, $error);
            self::assertStringEndsWith('is not supported', $error->getMessage());
        }
    }
}
