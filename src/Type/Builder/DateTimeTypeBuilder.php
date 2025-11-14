<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
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
    public function isSupported(TypeStatement $stmt): bool
    {
        return $stmt instanceof NamedTypeNode
            && \is_a($stmt->name->toLowerString(), \DateTimeInterface::class, true);
    }

    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        $this->expectNoShapeFields($stmt);
        $this->expectTemplateArgumentsLessOrEqualThan($stmt, 1);

        /** @var class-string<\DateTimeInterface> $class */
        $class = $stmt->name->toString();

        if ($stmt->arguments === null) {
            return $this->create($stmt, $class);
        }

        /** @var TemplateArgumentNode $formatArgument */
        $formatArgument = $stmt->arguments->first();

        $this->expectNoTemplateArgumentHint($stmt, $formatArgument);

        if (!$formatArgument->value instanceof StringLiteralNode) {
            throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
                argument: $formatArgument,
                expected: new NamedTypeNode('string'),
                type: $stmt,
            );
        }

        return $this->create(
            stmt: $stmt,
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
