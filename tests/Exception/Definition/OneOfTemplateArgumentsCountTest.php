<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\OneOfTemplateArgumentsCountException;

#[Group('exception')]
#[CoversClass(OneOfTemplateArgumentsCountException::class)]
final class OneOfTemplateArgumentsCountTest extends DefinitionExceptionTestCase
{
    #[TestDox('expected int<T> or int<T, U, V> (1 or 3 arguments), passed int<T, U> (2 arguments)')]
    public function testInvalidArgumentsCount(): void
    {
        $this->expectException(OneOfTemplateArgumentsCountException::class);
        $this->expectExceptionMessage('Type "int<min, max>" only accepts [1, 3] template argument(s), but 2 were passed');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [1, 3],
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[UB] expected int<T> or int<T, U, V> (1 or 3 arguments), passed int<T> (1 argument)')]
    public function testArgumentsCountInRange(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [1, 3],
            type: self::parse('int<T>'),
        );
    }

    #[TestDox('[UB] expected int (0 arguments), passed int<T, U> (2 arguments)')]
    public function testNoVariantsCount(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [],
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('expected int<T> (1 argument), passed int<T, U> (2 arguments)')]
    public function testOneVariantNotInRangeGte(): void
    {
        $this->expectException(OneOfTemplateArgumentsCountException::class);
        $this->expectExceptionMessage('Type "int<min, max>" only accepts [1] template argument(s), but 2 were passed');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [1],
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('expected int<T, U> (2 arguments), passed int<T> (1 argument)')]
    public function testOneVariantNotInRangeLte(): void
    {
        $this->expectException(OneOfTemplateArgumentsCountException::class);
        $this->expectExceptionMessage('Type "int<T>" only accepts [2] template argument(s), but 1 were passed');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [2],
            type: self::parse('int<T>'),
        );
    }

    #[TestDox('[UB] expected int<T> (1 argument), passed int<T> (1 argument)')]
    public function testOneVariantInRange(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw OneOfTemplateArgumentsCountException::becauseArgumentsCountDoesNotMatch(
            variants: [1],
            type: self::parse('int<T>'),
        );
    }
}
