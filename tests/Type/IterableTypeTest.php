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
        $generator = (static function (): \Generator {
            yield 5 => 1;
        })();

        self::assertSame([5 => 1], self::createMapper()->normalize($generator, 'iterable<int>'));
    }

    public function testRejectsElementAtItsKey(): void
    {
        self::assertDenormalizationFails(
            'iterable<string, int>',
            ['a' => 'x'],
            '$.a: Value must be of type int, but "x" given',
        );
    }

    public function testRejectsTraversalThatThrows(): void
    {
        $generator = static function (): \Generator {
            yield 1;

            throw new \RuntimeException('Broken');
        };

        $mapper = self::createMapper();

        self::assertFalse($mapper->isDenormalizable($generator(), 'iterable<int>'));
        self::assertFalse($mapper->isNormalizable($generator(), 'iterable<int>'));

        self::assertDenormalizationFails('iterable<int>', $generator(), '$: Value object is rejected');
        self::assertNormalizationFails('iterable<int>', $generator(), '$: Value object(Generator) is rejected');
    }

    public function testRejectsTraversalWithKeyThatIsNotArrayKey(): void
    {
        $generator = static function (mixed $key): \Generator {
            yield $key => 1;
        };

        $mapper = self::createMapper();

        self::assertFalse($mapper->isDenormalizable($generator([1]), 'iterable<int>'));
        self::assertFalse($mapper->isDenormalizable($generator(1.5), 'iterable<int>'));
        self::assertFalse($mapper->isNormalizable($generator(1.5), 'iterable<int>'));

        self::assertDenormalizationFails('iterable<int>', $generator(1.5), '$: Value object is rejected');
        self::assertNormalizationFails('iterable<int>', $generator([1]), '$: Value object(Generator) is rejected');
    }

    public function testRejectsValueThatIsNotIterable(): void
    {
        self::assertDenormalizationFails(
            'iterable<int>',
            'x',
            '$: Value must be of type iterable<int>, but "x" given',
        );
    }
}
