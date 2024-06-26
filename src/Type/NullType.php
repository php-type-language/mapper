<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;

final class NullType implements TypeInterface
{
    /**
     * @throws InvalidValueException
     */
    public function cast(mixed $value, RegistryInterface $types, LocalContext $context): mixed
    {
        if (!$context->isStrictTypesEnabled()) {
            return null;
        }

        if ($value !== null) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: 'null',
                actualValue: $value,
            );
        }

        return null;
    }
}
