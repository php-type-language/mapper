<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Tests\TestCase;

final class IterableTypeTest extends TestCase
{
    public function testDenormalizesArrayAndTraversableIntoArray(): void
    {
        self::assertDenormalizes('iterable', ['a' => 1], ['a' => 1]);
        self::assertDenormalizes('iterable<int>', ['1', 2], [1, 2]);
        self::assertDenormalizes('iterable<string, int>', new \ArrayIterator(['a' => '1']), ['a' => 1]);
    }

    public function testNormalizesTraversableKeepingKeys(): void
    {
        self::assertNormalizes('iterable<string, int>', new \ArrayIterator(['a' => 1]), ['a' => 1]);
    }

    public function testNormalizesGeneratorOnce(): void
    {
        $repository = self::createRepository();
        $generator = (static function (): \Generator {
            yield 5 => 1;
        })();

        $type = self::getType('iterable<int>', $repository);

        self::assertSame([5 => 1], $type->normalize($generator, self::getContext($repository)));
    }

    public function testRejectsElementAtItsKey(): void
    {
        self::assertDenormalizationFails(
            'iterable<string, int>',
            ['a' => 'x'],
            '$.a: Value must be of type "int", but "x" given',
        );
    }

    public function testRejectsValueThatIsNotIterable(): void
    {
        self::assertDenormalizationFails(
            'iterable<int>',
            'x',
            '$: Value must be of type "iterable<int>", but "x" given',
        );
    }
}
