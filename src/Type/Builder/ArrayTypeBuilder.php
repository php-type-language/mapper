<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ArrayType>
 */
class ArrayTypeBuilder extends NamedTypeBuilder
{
    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_KEY_TYPE = 'array-key';

    /**
     * @var non-empty-lowercase-string
     */
    public const DEFAULT_INNER_VALUE_TYPE = 'mixed';

    /**
     * @param non-empty-array<non-empty-string>|non-empty-string $names
     * @param non-empty-string $keyType
     * @param non-empty-string $valueType
     */
    public function __construct(
        array|string $names,
        protected readonly string $keyType = self::DEFAULT_INNER_KEY_TYPE,
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
    ): ArrayType {
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => $this->buildWithNoKeyValue($types, $parser),
            1 => $this->buildWithValue($statement, $types, $parser),
            2 => $this->buildWithKeyValue($statement, $types),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }

    /**
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithNoKeyValue(TypeRepositoryInterface $types, TypeParserInterface $parser): ArrayType
    {
        return new ArrayType(
            key: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition(
                    definition: $this->keyType,
                ),
            ),
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
    private function buildWithKeyValue(NamedTypeNode $statement, TypeRepositoryInterface $types): ArrayType
    {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));
        assert(\array_key_exists(1, $arguments));

        /** @var TemplateArgumentNode $key */
        $key = $arguments[0];
        $this->expectNoTemplateArgumentHint($statement, $key);

        /** @var TemplateArgumentNode $value */
        $value = $arguments[1];
        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ArrayType(
            key: $types->getTypeByStatement($key->value),
            value: $types->getTypeByStatement($value->value),
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
        TypeParserInterface $parser,
    ): ArrayType {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ArrayType(
            key: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition(
                    definition: $this->keyType,
                ),
            ),
            value: $types->getTypeByStatement($value->value),
        );
    }
}
