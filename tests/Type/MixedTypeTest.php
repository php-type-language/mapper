<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Context\DirectionInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Tests\Type\Stub\AnyTypeStub;
use TypeLang\Mapper\Tests\Type\Stub\IntBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\StringBackedEnumStub;
use TypeLang\Mapper\Tests\Type\Stub\UnitEnumStub;
use TypeLang\Mapper\Type\Builder\SimpleTypeBuilder;
use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\TypeInterface;

#[Group('type')]
#[CoversClass(MixedType::class)]
final class MixedTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::withPlatform(new class extends StandardPlatform {
            public function getTypes(DirectionInterface $direction): iterable
            {
                yield new SimpleTypeBuilder('resource', AnyTypeStub::class);

                yield from parent::getTypes($direction);
            }
        });
    }

    protected static function createType(): TypeInterface
    {
        return new MixedType();
    }

    protected static function matchValues(bool $normalize): iterable
    {
        foreach (self::defaultMatchDataProviderSamples() as $value => $_) {
            yield $value => true;
        }
    }

    protected static function castValues(bool $normalize): iterable
    {
        foreach (self::defaultCastDataProviderSamples() as $value => $_) {
            yield $value => $normalize === true
                ? match (true) {
                    \is_object($value) => match (true) {
                        // Unit enum coverts to enum's name
                        $value === UnitEnumStub::ExampleCase => $value->name,
                        // Int backed enum coverts to enum's int value
                        $value === IntBackedEnumStub::ExampleCase => $value->value,
                        // String backed enum coverts to enum's string value
                        $value === StringBackedEnumStub::ExampleCase => $value->value,
                        // Empty object converts to empty array
                        $value == (object) [] => [],
                        // Object with 1 property converts to hash-map
                        $value == (object) ['key' => 'val'] => ['key' => 'val'],
                        // Object without named properties converts to list<string>
                        $value == (object) ['val'] => ['val'],
                        default => $value,
                    },
                    default => $value,
                }
            : match (true) {
                $value === UnitEnumStub::ExampleCase => new \ValueError('Passed value "ExampleCase" is invalid'),
                $value === IntBackedEnumStub::ExampleCase => new \ValueError('Passed value 3735928559 is invalid'),
                $value === StringBackedEnumStub::ExampleCase => new \ValueError('Passed value "case" is invalid'),
                default => $value,
            };
        }
    }
}
