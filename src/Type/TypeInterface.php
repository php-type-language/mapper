<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-covariant TResult of mixed = mixed
 */
interface TypeInterface
{
    /**
     * Checks that the value matches the selected type
     */
    public function match(mixed $value, Context $context): bool;

    /**
     * @return TResult
     * @throws RuntimeException in case of known mapping issue
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, Context $context): mixed;
}
