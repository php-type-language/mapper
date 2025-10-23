<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Runtime\Context;

/**
 * @template-covariant TResult of mixed = mixed
 * @template TValue of mixed = mixed
 */
interface TypeCoercerInterface
{
    /**
     * @param TValue $value
     *
     * @return TResult
     */
    public function coerce(mixed $value, Context $context): mixed;
}
