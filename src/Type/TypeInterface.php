<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\ContextInterface;

interface TypeInterface
{
    /**
     * Checks that the value matches the selected type
     */
    public function match(mixed $value, ContextInterface $context): bool;

    /**
     * @throws RuntimeExceptionInterface in case of known mapping issue
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, ContextInterface $context): mixed;
}
