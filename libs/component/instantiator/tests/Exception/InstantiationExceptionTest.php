<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests\Exception;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Instantiator\Exception\ClassNotInstantiableException;
use TypeLang\Mapper\Instantiator\Exception\InstantiationException;
use TypeLang\Mapper\Instantiator\Exception\InstantiatorExceptionInterface;
use TypeLang\Mapper\Instantiator\Tests\TestCase;

final class InstantiationExceptionTest extends TestCase
{
    /**
     * @var non-empty-string
     */
    private const CLASS_NAME = 'Some\\"Any"';

    /**
     * @return iterable<non-empty-string, array{
     *     \Closure(string): InstantiationException,
     *     non-empty-string,
     * }>
     */
    public static function exceptionDataProvider(): iterable
    {
        yield 'class is abstract' => [
            static fn(string $c) => ClassNotInstantiableException::becauseClassIsAbstract($c),
            'Cannot instantiate abstract class "Some\\\\"Any\\""',
        ];
        yield 'class is interface' => [
            static fn(string $c) => ClassNotInstantiableException::becauseClassIsInterface($c),
            'Cannot instantiate interface "Some\\\\"Any\\""',
        ];
        yield 'class is trait' => [
            static fn(string $c) => ClassNotInstantiableException::becauseClassIsTrait($c),
            'Cannot instantiate trait "Some\\\\"Any\\""',
        ];
        yield 'class is enum' => [
            static fn(string $c) => ClassNotInstantiableException::becauseClassIsEnum($c),
            'Cannot instantiate enum "Some\\\\"Any\\""',
        ];
        yield 'class requires constructor' => [
            static fn(string $c) => ClassNotInstantiableException::becauseClassRequiresConstructor($c),
            'Cannot instantiate class "Some\\\\"Any\\"" without the constructor',
        ];
    }

    /**
     * @param \Closure(string): InstantiationException $create
     */
    #[DataProvider('exceptionDataProvider')]
    public function testExceptionCapturesClassAndDescribesFailure(\Closure $create, string $message): void
    {
        $error = $create(self::CLASS_NAME);

        self::assertInstanceOf(InstantiatorExceptionInterface::class, $error);
        self::assertSame(self::CLASS_NAME, $error->class);
        self::assertSame($message, $error->getMessage());
    }

    public function testExceptionKeepsPreviousThrowable(): void
    {
        $previous = new \ReflectionException('reason');

        self::assertSame(
            $previous,
            ClassNotInstantiableException::becauseClassRequiresConstructor('Some', $previous)->getPrevious(),
        );
    }
}
