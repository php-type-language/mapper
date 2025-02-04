<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\Type\ListType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ListType>
 */
class ListTypeBuilder extends NamedTypeBuilder
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_VALUE_TYPE = 'mixed';

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param non-empty-string $valueType
     */
    public function __construct(
        array|string $names,
        protected readonly string $valueType = self::DEFAULT_INNER_VALUE_TYPE,
    ) {
        parent::__construct($names);
    }

    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ListType {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => $this->buildWithNoValue($types, $parser),
            1 => $this->buildWithValue($statement, $types),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 1,
                type: $statement,
            ),
        };
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithNoValue(TypeRepositoryInterface $types, TypeParserInterface $parser): ListType
    {
        return new ListType(
            value: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition(
                    definition: $this->valueType,
                ),
            ),
        );
    }

    /**
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithValue(
        NamedTypeNode $statement,
        TypeRepositoryInterface $types,
    ): ListType {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ListType(
            value: $types->getTypeByStatement(
                statement: $value->value,
            ),
        );
    }
}
