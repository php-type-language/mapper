<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\UnionLeafEntry;

/**
 * @template T of mixed = mixed
 * @template-implements TypeInterface<T>
 */
class UnionType implements TypeInterface
{
    public function __construct(
        /**
         * @var non-empty-list<TypeInterface<T>>
         */
        private readonly array $types,
    ) {}

    /**
     * Finds a child supported type from their {@see $types} list by value.
     */
    protected function findType(mixed $value, Context $context): ?TypeInterface
    {
        foreach ($this->types as $index => $type) {
            $entrance = $context->enter($value, new UnionLeafEntry($index));

            if ($type->match($value, $entrance)) {
                return $type;
            }
        }

        return null;
    }

    public function match(mixed $value, Context $context): bool
    {
        return $this->findType($value, $context) !== null;
    }

    public function cast(mixed $value, Context $context): mixed
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
