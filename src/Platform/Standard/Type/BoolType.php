<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class BoolType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return \is_bool($value);
    }

    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            return $this->convertToBool($value);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    protected function convertToBool(mixed $value): bool
    {
        //
        // Each value should be checked EXPLICITLY, instead
        // of converting to a bool like `(bool) $value`.
        //
        // This will avoid implicit behavior, such as when an empty
        // SimpleXMLElement is cast to false, instead of being
        // converted to true like any other object:
        //
        // ```
        // (bool) new \SimpleXMLElement('<xml />'); // -> false (WTF?)
        // ```
        //
        return $value !== ''
            && $value !== '0'
            && $value !== []
            && $value !== null
            && $value !== 0
            && $value !== 0.0
            && $value !== false;
    }
}
