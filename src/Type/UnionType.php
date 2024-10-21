<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Path\Entry\UnionLeafEntry;
use TypeLang\Mapper\Runtime\ContextInterface;

class UnionType implements TypeInterface
{
    /**
     * @param non-empty-list<TypeInterface> $types
     */
    public function __construct(
        private readonly array $types,
    ) {}

    /**
     * Finds a child supported type from their {@see $types} list by value.
     */
    protected function findType(mixed $value, ContextInterface $context): ?TypeInterface
    {
        foreach ($this->types as $index => $type) {
            $entrance = $context->enter(new UnionLeafEntry($index));

            if ($type->match($value, $entrance)) {
                return $type;
            }
        }

        return null;
    }

    public function match(mixed $value, ContextInterface $context): bool
    {
        return $this->findType($value, $context) !== null;
    }

    /**
     * @throws InvalidValueException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, ContextInterface $context): mixed
    {
        $type = $this->findType($value, $context);

        if ($type !== null) {
            return $type->cast($value, $context);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
