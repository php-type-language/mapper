<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
class ListTypeBuilder extends NamedTypeBuilder
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_VALUE_TYPE = 'mixed';

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param non-empty-string $defaultValueType
     */
    public function __construct(
        array|string $names,
        protected readonly string $defaultValueType = self::DEFAULT_INNER_VALUE_TYPE,
    ) {
        parent::__construct($names);
    }

    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentHintNotSupportedException
     * @throws TooManyTemplateArgumentsInRangeException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof NamedTypeNode);

        $this->expectNoShapeFields($stmt);

        $arguments = $stmt->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => $this->buildWithNoValue($context),
            1 => $this->buildWithValue($stmt, $context),
            default => throw TooManyTemplateArgumentsInRangeException::becauseArgumentsCountRequired(
                minArgumentsCount: 0,
                maxArgumentsCount: 1,
                type: $stmt,
            ),
        };
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithNoValue(BuildingContext $context): TypeInterface
    {
        return new ListType(
            value: $context->getTypeByDefinition(
                definition: $this->defaultValueType,
            ),
        );
    }

    /**
     * @throws TemplateArgumentHintNotSupportedException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithValue(NamedTypeNode $statement, BuildingContext $context): TypeInterface
    {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ListType(
            value: $context->getTypeByStatement(
                statement: $value->value,
            ),
        );
    }
}
