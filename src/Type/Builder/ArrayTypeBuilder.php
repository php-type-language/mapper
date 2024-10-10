<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\ArrayKeyType;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, ArrayType>
 */
final class ArrayTypeBuilder extends Builder
{
    /**
     * @var non-empty-lowercase-string
     */
    protected readonly string $lower;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name = ArrayType::DEFAULT_TYPE_NAME)
    {
        $this->lower = \strtolower($name);
    }

    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === $this->lower;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): ArrayType
    {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => $this->buildAsIs($statement),
            1 => $this->buildByValue($statement, $types),
            2 => $this->buildByKeyValue($statement, $types),
            default => throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                passedArgumentsCount: \count($arguments),
                minSupportedArgumentsCount: 0,
                maxSupportedArgumentsCount: 2,
                type: $statement,
            ),
        };
    }

    /**
     * @throws TemplateArgumentHintsNotSupportedException
     * @throws TypeNotFoundException
     */
    private function buildByKeyValue(NamedTypeNode $statement, RepositoryInterface $types): ArrayType
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
            name: $statement->name->toString(),
            key: $types->getByStatement($key->value),
            value: $types->getByStatement($value->value),
        );
    }

    /**
     * @throws TypeNotFoundException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildByValue(NamedTypeNode $statement, RepositoryInterface $types): ArrayType
    {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ArrayType(
            name: $statement->name->toString(),
            key: new ArrayKeyType(),
            value: $types->getByStatement($value->value),
        );
    }

    private function buildAsIs(NamedTypeNode $statement): ArrayType
    {
        return new ArrayType($statement->name->toString());
    }
}
