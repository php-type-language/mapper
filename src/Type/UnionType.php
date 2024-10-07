<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

class UnionType implements LogicalTypeInterface
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
     * Checks a child type against a value.
     */
    protected function matchType(TypeInterface $type, mixed $value, LocalContext $context): bool
    {
        return $type instanceof LogicalTypeInterface
            && $type->supportsCasting($value, $context);
    }

    /**
     * Finds a child supported type from their {@see $types} list by value.
     */
    protected function findType(mixed $value, LocalContext $context): ?LogicalTypeInterface
    {
        foreach ($this->types as $type) {
            /** @var LogicalTypeInterface $type */
            if ($this->matchType($type, $value, $context)) {
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
    public function cast(mixed $value, RepositoryInterface $types, LocalContext $context): mixed
    {
        $type = $this->findType($value, $context);

        if ($type !== null) {
            return $type->cast($value, $types, $context);
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            context: $context,
            expectedType: $this->getTypeStatement($context),
            actualValue: $value,
        );
    }
}
