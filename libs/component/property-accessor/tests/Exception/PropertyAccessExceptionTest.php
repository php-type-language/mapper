<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\PropertyAccessor\Exception\AccessNotSupportedException;
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

    public function testMethodExceptionCapturesFieldAndMethod(): void
    {
        $error = MethodNotCallableException::becauseMethodDoesNotExist(VisibilityStub::class, 'prop', 'get');

        self::assertInstanceOf(PropertyAccessorExceptionInterface::class, $error);
        self::assertSame(VisibilityStub::class, $error->class);
        self::assertSame('prop', $error->field);
        self::assertSame('get', $error->method);
        self::assertSame(
            \sprintf('Method "%s::get()" of field "prop" does not exist', VisibilityStub::class),
            $error->getMessage(),
        );
    }

    public function testAccessExceptionCapturesFieldAndNamesAccess(): void
    {
        $access = new SlotAccessMetadata('prop');
        $error = AccessNotSupportedException::becauseNoStrategyReadsField(VisibilityStub::class, 'prop', $access);

        self::assertInstanceOf(PropertyAccessorExceptionInterface::class, $error);
        self::assertSame(VisibilityStub::class, $error->class);
        self::assertSame('prop', $error->field);
        self::assertSame(
            \sprintf(
                'No access strategy reads field "prop" of "%s" through "%s"',
                VisibilityStub::class,
                SlotAccessMetadata::class,
            ),
            $error->getMessage(),
        );
    }
}
