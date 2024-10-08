<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

class UnionType implements TypeInterface
{
    /**
     * @param non-empty-list<TypeInterface> $types
     */
    public function __construct(
        private readonly array $types,
    ) {}

    /**
     * @return UnionTypeNode<TypeStatement>|TypeStatement
     */
    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        $statements = [];

        foreach ($this->types as $type) {
            $statements[] = $type->getTypeStatement($context);
        }

        if (\count($statements) === 1) {
            return \reset($statements);
        }

        return new UnionTypeNode(...$statements);
    }

    /**
     * Finds a child supported type from their {@see $types} list by value.
     */
    protected function findType(mixed $value, LocalContext $context): ?TypeInterface
    {
        foreach ($this->types as $type) {
            if ($type->supportsCasting($value, $context)) {
                return $type;
            }
        }

        return null;
    }

    public function supportsCasting(mixed $value, LocalContext $context): bool
    {
        return $this->findType($value, $context) !== null;
    }

    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): mixed
    {
        $type = $this->findType($value, $context);

        if ($type !== null) {
            return $type->cast($value, $context);
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
