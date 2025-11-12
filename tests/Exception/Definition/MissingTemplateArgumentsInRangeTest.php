<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsInRangeException;

#[Group('exception')]
#[CoversClass(MissingTemplateArgumentsInRangeException::class)]
final class MissingTemplateArgumentsInRangeTest extends DefinitionExceptionTestCase
{
    #[TestDox('supported int<T[, U]> (from 1 to 2 arguments), passed int (0 arguments)')]
    public function testRangeUnderflow(): void
    {
        $this->expectException(MissingTemplateArgumentsInRangeException::class);
        $this->expectExceptionMessage('Type "int" expects at least from 1 to 2 template argument(s), but 0 were passed');

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int'),
        );
    }

    #[TestDox('[UB] supported int<T[, U]> (from 1 to 2 arguments), passed int<T> (1 argument)')]
    public function testRangeMinOverflow(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int<T>'),
        );
    }

    #[TestDox('[UB] supported int<T[, U]> (from 1 to 2 arguments), passed int<T, U, V> (3 arguments)')]
    public function testRangeMaxOverflow(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int<T, U, V>'),
        );
    }

    #[TestDox('[UB] supported int<T, U> (from 2 to 2 arguments), passed int<T, U> (2 arguments)')]
    public function testRangeEqual(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: 2,
            maxArgumentsCount: 2,
            type: self::parse('int<T, U>'),
        );
    }

    #[TestDox('[UB] supported int<T, U> (from 2 to 1 arguments), passed int<T, U> (2 arguments)')]
    public function testRangeInverse(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: 2,
            maxArgumentsCount: 1,
            type: self::parse('int<T, U>'),
        );
    }
}
