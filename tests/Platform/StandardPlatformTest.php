<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Platform;

use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Tests\Platform\Stub\ExampleClassStub;
use TypeLang\Mapper\Tests\Platform\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Platform\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Platform\Stub\UnitEnumStub;

#[Group('platform')]
final class StandardPlatformTest extends PlatformTestCase
{
    protected const SUPPORTED_TYPES = [
        'int',
        'integer',
        'string',
        'bool',
        'boolean',
        'float',
        'double',
        'real',
        'true',
        'false',
        'null',
        'array',
        'iterable',
        'mixed',
        'object',
        'list',
        'traversable',
        \Traversable::class,
        ExampleClassStub::class,
        UnitEnumStub::class,
        StringBackedEnumStub::class,
        IntBackedEnumStub::class,
        // special
        'array-key',
        'numeric-string',
        'non-empty-string',
        // composite
        '?int',
        'int|false',
    ];

    #[\Override]
    public static function typesDataProvider(): iterable
    {
        foreach (parent::typesDataProvider() as $title => [$type]) {
            $supports = \in_array($type, self::SUPPORTED_TYPES, true);

            yield $title => [$type, $supports];
        }
    }

    protected function createTypePlatform(): PlatformInterface
    {
        return new StandardPlatform();
    }
}
