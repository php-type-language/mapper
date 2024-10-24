<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<ArrayType>
 */
class ArrayTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $statement, TypeRepository $types): ArrayType
    {
        $this->expectNoShapeFields($statement);

        $arguments = $statement->arguments->items ?? [];

        return match (\count($arguments)) {
            0 => new ArrayType(),
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
    private function buildByKeyValue(NamedTypeNode $statement, TypeRepository $types): ArrayType
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
            key: $types->getByStatement($key->value),
            value: $types->getByStatement($value->value),
        );
    }

    /**
     * @throws TypeNotFoundException
     * @throws TemplateArgumentHintsNotSupportedException
     */
    private function buildByValue(NamedTypeNode $statement, TypeRepository $types): ArrayType
    {
        $arguments = $statement->arguments->items ?? [];

        assert(\array_key_exists(0, $arguments));

        /** @var TemplateArgumentNode $value */
        $value = $arguments[0];

        $this->expectNoTemplateArgumentHint($statement, $value);

        return new ArrayType(
            value: $types->getByStatement($value->value),
        );
    }
}
