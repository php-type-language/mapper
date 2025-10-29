<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

use TypeLang\Mapper\Exception\Value\JsonLikeValuePrinter;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Tests\Type
 *
 * @template-implements \IteratorAggregate<array-key, mixed>
 */
final class DataSamples implements \IteratorAggregate
{
    /**
     * @return \Traversable<mixed, bool>
     */
    public function getMatchesIterator(): \Traversable
    {
        foreach ($this as $value) {
            yield $value => false;
        }
    }

    /**
     * @return \Traversable<mixed, \ValueError>
     */
    public function getCastsIterator(): \Traversable
    {
        $printer = new JsonLikeValuePrinter();

        foreach ($this as $value) {
            yield $value => new \ValueError(\sprintf(
                'Passed value %s is invalid',
                $printer->print($value),
            ));
        }
    }

    /**
     * @return iterable<array-key, mixed>
     */
    public function getIterator(): \Traversable
    {
        // Integer values
        yield \PHP_INT_MAX + 1;
        yield \PHP_INT_MAX;
        yield 42;
        yield 1;
        yield 0;
        yield -1;
        yield -42;
        yield \PHP_INT_MIN;
        yield \PHP_INT_MIN - 1;

        // Numeric integer-like string values
        yield '9223372036854775808';
        yield '9223372036854775807';
        yield '42';
        yield '1';
        yield '0';
        yield '-1';
        yield '-42';
        yield '-9223372036854775808';
        yield '-9223372036854775809';

        // Float values
        yield 9223372036854775808.0;
        yield 9223372036854775807.0;
        yield 42.5;
        yield 42.0;
        yield 1.0;
        yield 0.0;
        yield -1.0;
        yield -42.0;
        yield -42.5;
        yield -9223372036854775808.0;
        yield -9223372036854775809.0;

        yield INF;
        yield -INF;
        yield NAN;

        // Numeric float-like string values
        yield '9223372036854775808.0';
        yield '9223372036854775807.0';
        yield '42.5';
        yield '42.0';
        yield '1.0';
        yield '0.0';
        yield '-1.0';
        yield '-42.0';
        yield '-42.5';
        yield '-9223372036854775808.0';
        yield '-9223372036854775809.0';

        // Null
        yield null;

        // Boolean
        yield true;
        yield false;

        // Boolean-like strings
        yield 'true';
        yield 'false';

        // Strings
        yield 'non empty';
        yield '';

        // Array values
        yield [];
        yield [0 => 23];
        yield ['key' => 42];

        // Object values
        yield (object) [];
        yield (object) ['key' => 'val'];
        yield (object) ['val'];

        // Resource
        yield \fopen('php://memory', 'rb');
        \fclose($stream = \fopen('php://memory', 'rb'));
        yield $stream; // closed resource

        // Enum values
        yield UnitEnumStub::ExampleCase;
        // This behavior can be confusing to the user, since the "public"
        // type (i.e., the one displayed to the user) for an enum is an int,
        // but the actual type is an enum's object.
        //
        // Thus, the error displays "expected an int, but received an int,"
        // which is very bad.
        yield IntBackedEnumStub::ExampleCase;
        yield StringBackedEnumStub::ExampleCase;
    }
}
