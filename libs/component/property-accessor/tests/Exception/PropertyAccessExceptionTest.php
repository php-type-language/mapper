<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\PropertyAccessor\Exception\MethodNotCallableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyAccessorExceptionInterface;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotFoundException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotReadableException;
use TypeLang\Mapper\PropertyAccessor\Exception\PropertyNotWritableException;
use TypeLang\Mapper\PropertyAccessor\Tests\Stub\VisibilityStub;
use TypeLang\Mapper\PropertyAccessor\Tests\TestCase;

final class PropertyAccessExceptionTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{
     *     \Closure(class-string, string): PropertyAccessException,
     *     non-empty-string,
     * }>
     */
    public static function exceptionDataProvider(): iterable
    {
        yield 'property is static' => [
            static fn(string $c, string $p) => PropertyNotFoundException::becausePropertyIsStatic($c, $p),
            'Property "%s::$prop" is static',
        ];
        yield 'property is write-only' => [
            static fn(string $c, string $p) => PropertyNotReadableException::becausePropertyIsWriteOnly($c, $p),
            'Property "%s::$prop" is write-only',
        ];
        yield 'property is read-only' => [
            static fn(string $c, string $p) => PropertyNotWritableException::becausePropertyIsReadOnly($c, $p),
            'Property "%s::$prop" is read-only',
        ];
        yield 'property does not exist' => [
            static fn(string $c, string $p) => PropertyNotFoundException::becausePropertyDoesNotExist($c, $p),
            'Property "%s::$prop" does not exist',
        ];
        yield 'virtual property is not read' => [
            static fn(string $c, string $p) => PropertyNotReadableException::becausePropertyIsVirtual($c, $p),
            'Property "%s::$prop" has no backing store to read',
        ];
        yield 'virtual property is not written' => [
            static fn(string $c, string $p) => PropertyNotWritableException::becausePropertyIsVirtual($c, $p),
            'Property "%s::$prop" has no backing store to write',
        ];
        yield 'property has no get hook' => [
            static fn(string $c, string $p) => PropertyNotReadableException::becausePropertyHasNoGetHook($c, $p),
            'Property "%s::$prop" has no get hook',
        ];
        yield 'property has no set hook' => [
            static fn(string $c, string $p) => PropertyNotWritableException::becausePropertyHasNoSetHook($c, $p),
            'Property "%s::$prop" has no set hook',
        ];
        yield 'method does not exist' => [
            static fn(string $c, string $p) => MethodNotCallableException::becauseMethodDoesNotExist($c, $p, 'get'),
            'Method "%1$s::get()" of property "%1$s::$prop" does not exist',
        ];
    }

    /**
     * @param \Closure(class-string, string): PropertyAccessException $create
     */
    #[DataProvider('exceptionDataProvider')]
    public function testExceptionCapturesPropertyAndDescribesFailure(\Closure $create, string $message): void
    {
        $error = $create(VisibilityStub::class, 'prop');

        self::assertInstanceOf(PropertyAccessorExceptionInterface::class, $error);
        self::assertSame(VisibilityStub::class, $error->class);
        self::assertSame('prop', $error->property);
        self::assertSame(\sprintf($message, VisibilityStub::class), $error->getMessage());
    }
}
