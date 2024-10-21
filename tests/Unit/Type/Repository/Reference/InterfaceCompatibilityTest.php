<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type\Repository\Reference;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Runtime\Repository\Reference\ReferencesReaderInterface;

#[Group('unit'), Group('type-lang/mapper')]
final class InterfaceCompatibilityTest extends ReferenceTestCase
{
    public function testMethodCompatibility(): void
    {
        self::expectNotToPerformAssertions();

        new class implements ReferencesReaderInterface {
            public function getUseStatements(\ReflectionClass $class): array {}
        };
    }
}
