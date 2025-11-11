<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TKey of mixed = mixed
 * @template TValue of mixed = mixed
 * @template-extends NamedTypeBuilder<TypeInterface<iterable<TKey, TValue>>>
 */
abstract class MapTypeBuilder extends NamedTypeBuilder
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

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($statement instanceof NamedTypeNode);

        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
        return match (\count($arguments)) {
            0 => $this->buildWithNoKeyValue($types, $parser),
            1 => $this->buildWithValue($statement, $types, $parser),
            2 => $this->buildWithKeyValue($statement, $types),
            default => throw TooManyTemplateArgumentsInRangeException::becauseTooManyThanRangeTemplateArguments(
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }

    /**
     * @return TypeInterface<iterable<TKey, TValue>>
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithNoKeyValue(TypeRepositoryInterface $types, TypeParserInterface $parser): TypeInterface
    {
        /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
        return $this->create(
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
     * @return TypeInterface<iterable<TKey, TValue>>
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithKeyValue(NamedTypeNode $statement, TypeRepositoryInterface $types): TypeInterface
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

        /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
        return $this->create(
            key: $types->getTypeByStatement($key->value),
            value: $types->getTypeByStatement($value->value),
        );
    }

    /**
     * @return TypeInterface<iterable<TKey, TValue>>
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    private function buildWithValue(
        NamedTypeNode $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        /** @phpstan-ignore-next-line : It's too difficult for PHPStan to calculate the specified type */
        return $this->create(
            key: $types->getTypeByStatement(
                statement: $parser->getStatementByDefinition($this->keyType),
            ),
            value: $types->getTypeByStatement($value->value),
        );
    }

    /**
     * @param TypeInterface<TKey> $key
     * @param TypeInterface<TValue> $value
     *
     * @return TypeInterface<iterable<TKey, TValue>>
     */
    abstract protected function create(TypeInterface $key, TypeInterface $value): TypeInterface;
}
