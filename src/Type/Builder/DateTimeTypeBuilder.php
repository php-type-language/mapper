<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Literal\StringLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
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

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        $this->expectNoShapeFields($statement);
        $this->expectTemplateArgumentsLessOrEqualThan($statement, 1, 0);

        /** @var class-string<\DateTimeInterface> $class */
        $class = $statement->name->toString();

        if ($statement->arguments === null) {
            return $this->create($statement, $class);
        }

        /** @var TemplateArgumentNode $formatArgument */
        $formatArgument = $statement->arguments->first();

        $this->expectNoTemplateArgumentHint($statement, $formatArgument);

        if (!$formatArgument->value instanceof StringLiteralNode) {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
                argument: $formatArgument,
                expected: new NamedTypeNode('string'),
                type: $statement,
            );
        }

        return $this->create(
            stmt: $statement,
            class: $class,
            format: $formatArgument->value->value,
        );
    }

    /**
     * @param class-string<\DateTimeInterface> $class
     *
     * @return TypeInterface<TResult>
     */
    abstract protected function create(NamedTypeNode $stmt, string $class, ?string $format = null): TypeInterface;
}
