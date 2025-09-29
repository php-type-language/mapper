<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use TypeLang\Mapper\Runtime\Repository\Reference\ReferencesReaderInterface;

final class InterfaceCompatibilityTest extends ReferenceTestCase
{
    #[DoesNotPerformAssertions]
    public function testMethodCompatibility(): void
    {
        new class implements ReferencesReaderInterface {
            public function getUseStatements(\ReflectionClass $class): array {}
        };
    }
}
