<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeCoercerInterface<bool>
 */
class BoolTypeCoercer implements TypeCoercerInterface
{
    public function coerce(mixed $value, Context $context): bool
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
        return $value !== false
            && $value !== '0'
            && $value !== ''
            && $value !== []
            && $value !== null
            && $value !== 0
            && $value !== 0.0;
    }
}
