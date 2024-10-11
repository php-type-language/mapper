<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\MappingException;
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
        $strict = $context->withStrictTypes(true);

        foreach ($this->types as $type) {
            if ($type->match($value, $strict)) {
                return $type;
            }
        }

        $nonStrict = $context->withStrictTypes(false);

        foreach ($this->types as $type) {
            if ($type->match($value, $nonStrict)) {
                return $type;
            }
        }

        return null;
    }

    public function match(mixed $value, LocalContext $context): bool
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
            try {
                return $type->cast($value, $context);
            } catch (MappingException $e) {
                throw InvalidValueException::becauseInvalidValueGiven(
                    value: $value,
                    expected: $this->getTypeStatement($context),
                    context: $context,
                    previous: $e,
                );
            }
        }

        throw InvalidValueException::becauseInvalidValueGiven(
            value: $value,
            expected: $this->getTypeStatement($context),
            context: $context,
        );
    }
}
