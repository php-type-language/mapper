<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference\Reader;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use TypeLang\Mapper\Runtime\Repository\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Tests\Runtime\Repository\Reference\ReferenceTestCase;

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
