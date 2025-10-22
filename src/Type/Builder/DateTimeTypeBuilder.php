<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TDateTime of \DateTime|\DateTimeImmutable = \DateTimeImmutable
 * @template TResult of mixed = mixed
 * @template-extends Builder<NamedTypeNode, TypeInterface<TResult>>
 */
abstract class DateTimeTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && \is_a($statement->name->toLowerString(), \DateTimeInterface::class, true);
    }

    /**
     * @return class-string<TDateTime>
     * @return class-string<TDateTime>
     */
    private function getDateTimeClass(string $name): string
    {
        if ($name === \DateTimeInterface::class || \interface_exists($name)) {
            /** @phpstan-ignore-next-line : If an interface is passed, then return the base class */
            return \DateTimeImmutable::class;
        }

        /** @var class-string<TDateTime> */
        return $name;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsLessOrEqualThan($statement, 1, 0);

        if ($statement->arguments === null) {
            return $this->create(
                class: $this->getDateTimeClass(
                    name: $statement->name->toString(),
                ),
            );
        }

        /** @var TemplateArgumentNode $formatArgument */
        $formatArgument = $statement->arguments->first();

        $this->expectNoTemplateArgumentHint($statement, $formatArgument);

        if (!$formatArgument->value instanceof StringLiteralNode) {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentIsInvalid(
                expected: new NamedTypeNode('string'),
                argument: $formatArgument,
                type: $statement,
            );
        }

        return $this->create(
            class: $this->getDateTimeClass(
                name: $statement->name->toString(),
            ),
            format: $formatArgument->value->value,
        );
    }

    /**
     * @param class-string<TDateTime> $class
     *
     * @return TypeInterface<TResult>
     */
    abstract protected function create(string $class, ?string $format = null): TypeInterface;
}
