<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\RuntimeException;

/**
 * @template-covariant TResult of mixed = mixed
 */
interface TypeInterface
{
    /**
     * Checks that the value matches the selected type
     */
    public function match(mixed $value, RuntimeContext $context): bool;

    /**
     * @return TResult
     * @throws RuntimeException in case of known mapping issue
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, RuntimeContext $context): mixed;
}
